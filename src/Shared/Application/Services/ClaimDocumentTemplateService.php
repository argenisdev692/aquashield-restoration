<?php

declare(strict_types=1);

namespace Src\Shared\Application\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use RuntimeException;
use Shared\Domain\Ports\StoragePort;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;
use Src\Modules\DocumentTemplateAdjusters\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAdjusterEloquentModel;
use Src\Modules\DocumentTemplateAlliances\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateAllianceEloquentModel;
use Src\Modules\DocumentTemplates\Infrastructure\Persistence\Eloquent\Models\DocumentTemplateEloquentModel;

/**
 * Shared application service for generating Word documents from claim data
 * using PhpWord TemplateProcessor against templates stored in R2.
 *
 * Template variable conventions (use ${variable} in .docx files):
 *
 * Common (all templates):
 *   ${claim_number}           — Claim # (or internal ID if not set)
 *   ${claim_internal_id}      — Claim internal identifier
 *   ${policy_number}          — Insurance policy number
 *   ${date_of_loss}           — Date of loss
 *   ${damage_description}     — Damage description
 *   ${scope_of_work}          — Scope of work
 *   ${generated_date}         — Date the document was generated
 *
 * Property:
 *   ${property_address}       — Property street address
 *   ${property_city}          — City
 *   ${property_state}         — State
 *   ${property_postal_code}   — Postal/ZIP code
 *   ${property_full_address}  — Full formatted address (address, city, state zip)
 *
 * Referred-by user (insured):
 *   ${insured_name}           — Full name of the referred-by user
 *   ${insured_email}          — Email of the referred-by user
 *
 * Adjuster template extras:
 *   ${adjuster_name}          — Name of the insurance adjuster assigned
 *   ${adjuster_email}         — Email of the insurance adjuster
 *   ${insurance_company_name} — Name of the assigned insurance company
 *   ${assignment_date}        — Insurance company assignment date
 *
 * Agreement template extras:
 *   ${public_adjuster_name}   — Public adjuster full name
 *   ${public_adjuster_email}  — Public adjuster email
 *   ${public_company_name}    — Public company name
 *   ${agreement_date}         — Date of agreement generation (today)
 *
 * Alliance template extras (optional):
 *   ${alliance_company_name}  — Alliance company name
 *   ${alliance_company_email} — Alliance company email
 *   ${alliance_assignment_date} — Alliance assignment date
 */
final class ClaimDocumentTemplateService
{
    public function __construct(
        private readonly StoragePort $storage,
    ) {}

    /**
     * Generate an adjuster document for a claim.
     *
     * Looks up the DocumentTemplateAdjuster record for the public adjuster
     * assigned to the claim, downloads the .docx from R2, fills all variables,
     * and returns the path of the generated temp file.
     *
     * @throws RuntimeException if the claim or template is not found
     */
    #[\NoDiscard('Generated adjuster document path must be captured for storage or response.')]
    public function generateAdjusterDocument(string $claimUuid): string
    {
        $claim = $this->loadClaim($claimUuid, [
            'property',
            'referredByUser',
            'publicAdjusterAssignment',
            'insuranceCompanyAssignment.insuranceCompany',
            'insuranceAdjusterAssignment.insuranceAdjuster',
        ]);

        $templateModel = DocumentTemplateAdjusterEloquentModel::query()
            ->when(
                $claim->publicAdjusterAssignment?->public_adjuster_id,
                fn ($q, $id) => $q->where('public_adjuster_id', $id),
            )
            ->latest()
            ->first();

        if ($templateModel === null) {
            throw new RuntimeException(
                "No adjuster document template found for claim [{$claimUuid}]."
            );
        }

        $variables = array_merge(
            $this->commonVariables($claim),
            $this->propertyVariables($claim),
            $this->insuredVariables($claim),
            [
                'adjuster_name'          => $claim->insuranceAdjusterAssignment?->insuranceAdjuster?->name ?? '',
                'adjuster_email'         => $claim->insuranceAdjusterAssignment?->insuranceAdjuster?->email ?? '',
                'insurance_company_name' => $claim->insuranceCompanyAssignment?->insuranceCompany?->insurance_company_name ?? '',
                'assignment_date'        => $claim->insuranceCompanyAssignment?->assignment_date ?? '',
            ],
        );

        return $this->processTemplate(
            templatePath: $templateModel->template_path_adjuster,
            variables: $variables,
            outputPrefix: "adjuster-{$claimUuid}",
        );
    }

    /**
     * Generate an agreement document for a claim.
     *
     * Looks up the latest DocumentTemplate of type 'agreement', downloads
     * the .docx from R2, fills all variables, and returns the temp file path.
     *
     * @throws RuntimeException if the claim or template is not found
     */
    #[\NoDiscard('Generated agreement document path must be captured for storage or response.')]
    public function generateAgreementDocument(string $claimUuid): string
    {
        $claim = $this->loadClaim($claimUuid, [
            'property',
            'referredByUser',
            'publicCompanyAssignment.publicCompany',
            'publicAdjusterAssignment.publicAdjuster',
        ]);

        $templateModel = DocumentTemplateEloquentModel::query()
            ->where('template_type', 'agreement')
            ->latest()
            ->first();

        if ($templateModel === null) {
            throw new RuntimeException(
                "No agreement document template found for claim [{$claimUuid}]."
            );
        }

        $variables = array_merge(
            $this->commonVariables($claim),
            $this->propertyVariables($claim),
            $this->insuredVariables($claim),
            [
                'public_adjuster_name'  => $claim->publicAdjusterAssignment?->publicAdjuster?->name ?? '',
                'public_adjuster_email' => $claim->publicAdjusterAssignment?->publicAdjuster?->email ?? '',
                'public_company_name'   => $claim->publicCompanyAssignment?->publicCompany?->public_company_name ?? '',
                'agreement_date'        => now()->format('F j, Y'),
            ],
        );

        return $this->processTemplate(
            templatePath: $templateModel->template_path,
            variables: $variables,
            outputPrefix: "agreement-{$claimUuid}",
        );
    }

    /**
     * Generate an alliance document for a claim (optional).
     *
     * Looks up the latest DocumentTemplateAlliance record, downloads the .docx
     * from R2, fills all variables, and returns the temp file path.
     *
     * Returns null if no alliance template exists (non-fatal).
     */
    #[\NoDiscard('Generated alliance document path must be captured; null means no template exists.')]
    public function generateAllianceDocument(string $claimUuid): ?string
    {
        $claim = $this->loadClaim($claimUuid, [
            'property',
            'referredByUser',
            'claimAlliance.allianceCompany',
        ]);

        $templateModel = DocumentTemplateAllianceEloquentModel::query()
            ->latest()
            ->first();

        if ($templateModel === null) {
            return null;
        }

        $variables = array_merge(
            $this->commonVariables($claim),
            $this->propertyVariables($claim),
            $this->insuredVariables($claim),
            [
                'alliance_company_name'   => $claim->claimAlliance?->allianceCompany?->alliance_company_name ?? '',
                'alliance_company_email'  => $claim->claimAlliance?->allianceCompany?->email ?? '',
                'alliance_assignment_date'=> $claim->claimAlliance?->assignment_date ?? '',
            ],
        );

        return $this->processTemplate(
            templatePath: $templateModel->template_path_alliance,
            variables: $variables,
            outputPrefix: "alliance-{$claimUuid}",
        );
    }

    private function loadClaim(string $uuid, array $relations): ClaimEloquentModel
    {
        $claim = ClaimEloquentModel::with($relations)
            ->where('uuid', $uuid)
            ->first();

        if ($claim === null) {
            throw new RuntimeException("Claim [{$uuid}] not found.");
        }

        return $claim;
    }

    /**
     * Downloads the template .docx from R2 to a local temp file, runs
     * TemplateProcessor to fill variables, and saves the output to another
     * temp file. Returns the output temp file path.
     *
     * @param array<string, string> $variables
     */
    private function processTemplate(string $templatePath, array $variables, string $outputPrefix): string
    {
        $localTemplate = $this->downloadToTemp($templatePath, $outputPrefix . '-template');

        try {
            $processor = $localTemplate
                |> static fn (string $templateFile): TemplateProcessor => new TemplateProcessor($templateFile);

            foreach ($variables as $key => $value) {
                $processor->setValue($key, htmlspecialchars((string) $value, ENT_COMPAT));
            }

            $outputPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $outputPrefix . '-' . now()->format('Ymd-His') . '.docx';
            $processor->saveAs($outputPath);

            return $outputPath;
        } finally {
            if (file_exists($localTemplate)) {
                @unlink($localTemplate);
            }
        }
    }

    private function downloadToTemp(string $storagePath, string $prefix): string
    {
        $contents = $this->storage->download($storagePath);
        $tempFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $prefix . '-' . uniqid('', true) . '.docx';
        file_put_contents($tempFile, $contents);

        return $tempFile;
    }

    /** @return array<string, string> */
    private function commonVariables(ClaimEloquentModel $claim): array
    {
        return [
            'claim_number'       => $claim->claim_number ?? $claim->claim_internal_id,
            'claim_internal_id'  => $claim->claim_internal_id,
            'policy_number'      => $claim->policy_number,
            'date_of_loss'       => $claim->date_of_loss ?? '',
            'damage_description' => $claim->damage_description ?? '',
            'scope_of_work'      => $claim->scope_of_work ?? '',
            'generated_date'     => now()->format('F j, Y'),
        ];
    }

    /** @return array<string, string> */
    private function propertyVariables(ClaimEloquentModel $claim): array
    {
        $property = $claim->property;

        $parts = [$property?->property_address, $property?->property_city, $property?->property_state, $property?->property_postal_code];
        $fullAddress = implode(', ', array_filter($parts, static fn (?string $v): bool => $v !== null && $v !== ''));

        return [
            'property_address'      => $property?->property_address ?? '',
            'property_city'         => $property?->property_city ?? '',
            'property_state'        => $property?->property_state ?? '',
            'property_postal_code'  => $property?->property_postal_code ?? '',
            'property_full_address' => $fullAddress,
        ];
    }

    /** @return array<string, string> */
    private function insuredVariables(ClaimEloquentModel $claim): array
    {
        return [
            'insured_name'  => $claim->referredByUser?->name ?? '',
            'insured_email' => $claim->referredByUser?->email ?? '',
        ];
    }
}
