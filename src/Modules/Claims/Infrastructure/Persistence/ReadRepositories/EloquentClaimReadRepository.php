<?php

declare(strict_types=1);

namespace Src\Modules\Claims\Infrastructure\Persistence\ReadRepositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Modules\Claims\Application\DTOs\ClaimFilterData;
use Src\Modules\Claims\Application\Queries\Contracts\ClaimReadRepository;
use Src\Modules\Claims\Application\Queries\ReadModels\ClaimListReadModel;
use Src\Modules\Claims\Application\Queries\ReadModels\ClaimReadModel;
use Src\Modules\Claims\Infrastructure\Persistence\Eloquent\Models\ClaimEloquentModel;

final class EloquentClaimReadRepository implements ClaimReadRepository
{
    public function paginate(ClaimFilterData $filters): LengthAwarePaginator
    {
        $query = ClaimEloquentModel::query()
            ->with([
                'property.customers',
                'claimStatus',
                'typeDamage',
                'referredByUser',
            ])
            ->when($filters->status === 'deleted', fn ($q) => $q->onlyTrashed())
            ->when(
                $filters->status !== null && $filters->status !== 'deleted',
                fn ($q) => $q->whereNull('deleted_at'),
            )
            ->search($filters->search)
            ->inDateRange($filters->dateFrom, $filters->dateTo)
            ->when($filters->claimStatusId, fn ($q, $id) => $q->where('claim_status', $id))
            ->when($filters->typeDamageId, fn ($q, $id) => $q->where('type_damage_id', $id))
            ->latest('created_at');

        return $query->paginate(
            perPage: $filters->perPage,
            page: $filters->page,
        )->through(fn (ClaimEloquentModel $model): ClaimListReadModel => $this->toListReadModel($model));
    }

    public function findByUuid(string $uuid): ?ClaimReadModel
    {
        $model = ClaimEloquentModel::withTrashed()
            ->with([
                'property.customers',
                'claimStatus',
                'typeDamage',
                'referredByUser',
                'causesOfLoss',
                'serviceRequests',
                'insuranceCompanyAssignment.insuranceCompany',
                'publicCompanyAssignment.publicCompany',
                'insuranceAdjusterAssignment.insuranceAdjuster',
                'publicAdjusterAssignment.publicAdjuster',
                'claimAlliance.allianceCompany',
            ])
            ->where('uuid', $uuid)
            ->first();

        return $model !== null ? $this->toReadModel($model) : null;
    }

    private function mapCustomers(ClaimEloquentModel $model): array
    {
        return $model->property?->customers
            ?->map(static fn ($customer): array => [
                'id'         => $customer->id,
                'uuid'       => $customer->uuid,
                'full_name'  => trim(($customer->name ?? '') . ' ' . ($customer->last_name ?? '')),
                'email'      => $customer->email ?? null,
                'cell_phone' => $customer->cell_phone ?? null,
                'home_phone' => $customer->home_phone ?? null,
            ])
            ->values()
            ->all() ?? [];
    }

    private function toListReadModel(ClaimEloquentModel $model): ClaimListReadModel
    {
        return new ClaimListReadModel(
            uuid: $model->uuid,
            claimNumber: $model->claim_number,
            claimInternalId: $model->claim_internal_id,
            policyNumber: $model->policy_number,
            dateOfLoss: $model->date_of_loss,
            propertyId: (int) $model->property_id,
            propertyAddress: $model->property?->property_address,
            customers: $this->mapCustomers($model),
            typeDamageId: (int) $model->type_damage_id,
            typeDamageName: $model->typeDamage?->type_damage_name ?? null,
            claimStatusId: (int) $model->claim_status,
            claimStatusName: $model->claimStatus?->claim_status_name ?? null,
            claimStatusColor: $model->claimStatus?->background_color ?? null,
            userIdRefBy: (int) $model->user_id_ref_by,
            referredByName: $model->referredByUser?->name ?? null,
            status: $model->deleted_at !== null ? 'Suspended' : 'Active',
            createdAt: $model->created_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }

    private function toReadModel(ClaimEloquentModel $model): ClaimReadModel
    {
        $causesOfLoss = $model->causesOfLoss
            ->map(fn ($col): array => [
                'id'   => $col->id,
                'name' => $col->cause_of_loss_name ?? ($col->name ?? ''),
            ])
            ->values()
            ->all();

        $serviceRequests = $model->serviceRequests
            ->map(fn ($sr): array => [
                'id'   => $sr->id,
                'name' => $sr->service_request_name ?? ($sr->name ?? ''),
            ])
            ->values()
            ->all();

        $insuranceCompanyAssignment = null;
        if ($model->insuranceCompanyAssignment !== null) {
            $ica = $model->insuranceCompanyAssignment;
            $insuranceCompanyAssignment = [
                'id'                     => $ica->id,
                'insurance_company_id'   => $ica->insurance_company_id,
                'insurance_company_name' => $ica->insuranceCompany?->insurance_company_name ?? null,
                'assignment_date'        => $ica->assignment_date,
            ];
        }

        $publicCompanyAssignment = null;
        if ($model->publicCompanyAssignment !== null) {
            $pca = $model->publicCompanyAssignment;
            $publicCompanyAssignment = [
                'id'                  => $pca->id,
                'public_company_id'   => $pca->public_company_id,
                'public_company_name' => $pca->publicCompany?->public_company_name ?? null,
                'assignment_date'     => $pca->assignment_date,
            ];
        }

        $insuranceAdjusterAssignment = null;
        if ($model->insuranceAdjusterAssignment !== null) {
            $iaa = $model->insuranceAdjusterAssignment;
            $insuranceAdjusterAssignment = [
                'id'                      => $iaa->id,
                'insurance_adjuster_id'   => $iaa->insurance_adjuster_id,
                'insurance_adjuster_name' => $iaa->insuranceAdjuster?->name ?? null,
                'assignment_date'         => $iaa->assignment_date,
            ];
        }

        $publicAdjusterAssignment = null;
        if ($model->publicAdjusterAssignment !== null) {
            $paa = $model->publicAdjusterAssignment;
            $publicAdjusterAssignment = [
                'id'                   => $paa->id,
                'public_adjuster_id'   => $paa->public_adjuster_id,
                'public_adjuster_name' => $paa->publicAdjuster?->name ?? null,
                'assignment_date'      => $paa->assignment_date,
            ];
        }

        $claimAlliance = null;
        if ($model->claimAlliance !== null) {
            $ca = $model->claimAlliance;
            $claimAlliance = [
                'id'                    => $ca->id,
                'alliance_company_id'   => $ca->alliance_company_id,
                'alliance_company_name' => $ca->allianceCompany?->alliance_company_name ?? null,
                'assignment_date'       => $ca->assignment_date,
            ];
        }

        return new ClaimReadModel(
            id: (int) $model->id,
            uuid: $model->uuid,
            claimNumber: $model->claim_number,
            claimInternalId: $model->claim_internal_id,
            policyNumber: $model->policy_number,
            dateOfLoss: $model->date_of_loss,
            descriptionOfLoss: $model->description_of_loss,
            numberOfFloors: $model->number_of_floors !== null ? (int) $model->number_of_floors : null,
            claimDate: $model->claim_date,
            workDate: $model->work_date,
            damageDescription: $model->damage_description,
            scopeOfWork: $model->scope_of_work,
            customerReviewed: $model->customer_reviewed,
            propertyId: (int) $model->property_id,
            propertyAddress: $model->property?->property_address,
            customers: $this->mapCustomers($model),
            typeDamageId: (int) $model->type_damage_id,
            typeDamageName: $model->typeDamage?->type_damage_name ?? null,
            claimStatusId: (int) $model->claim_status,
            claimStatusName: $model->claimStatus?->claim_status_name ?? null,
            claimStatusColor: $model->claimStatus?->background_color ?? null,
            userIdRefBy: (int) $model->user_id_ref_by,
            referredByName: $model->referredByUser?->name ?? null,
            signaturePathId: (int) $model->signature_path_id,
            causesOfLoss: $causesOfLoss,
            serviceRequests: $serviceRequests,
            insuranceCompanyAssignment: $insuranceCompanyAssignment,
            publicCompanyAssignment: $publicCompanyAssignment,
            insuranceAdjusterAssignment: $insuranceAdjusterAssignment,
            publicAdjusterAssignment: $publicAdjusterAssignment,
            claimAlliance: $claimAlliance,
            status: $model->deleted_at !== null ? 'Suspended' : 'Active',
            createdAt: $model->created_at?->toIso8601String() ?? '',
            updatedAt: $model->updated_at?->toIso8601String() ?? '',
            deletedAt: $model->deleted_at?->toIso8601String(),
        );
    }
}
