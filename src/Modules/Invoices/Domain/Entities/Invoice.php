<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Domain\Entities;

use Src\Modules\Invoices\Domain\ValueObjects\InvoiceId;
use Src\Modules\Invoices\Domain\ValueObjects\InvoiceStatus;

class Invoice
{
    public function __construct(
        public readonly InvoiceId $id,
        public int $userId,
        public ?int $claimId,
        public string $invoiceNumber,
        public string $invoiceDate,
        public string $billToName,
        public ?string $billToAddress,
        public ?string $billToPhone,
        public ?string $billToEmail,
        public float $subtotal,
        public float $taxAmount,
        public float $balanceDue,
        public ?string $claimNumber,
        public ?string $policyNumber,
        public ?string $insuranceCompany,
        public ?string $dateOfLoss,
        public ?string $dateReceived,
        public ?string $dateInspected,
        public ?string $dateEntered,
        public ?string $priceListCode,
        public ?string $typeOfLoss,
        public ?string $notes,
        public InvoiceStatus $status,
        public ?string $pdfUrl,
        public array $items = [],
    ) {}

    public function withStatus(InvoiceStatus $status): self
    {
        return clone($this, ['status' => $status]);
    }

    public function withBalanceDue(float $subtotal, float $taxAmount): self
    {
        return clone($this, [
            'subtotal'   => $subtotal,
            'taxAmount'  => $taxAmount,
            'balanceDue' => $subtotal + $taxAmount,
        ]);
    }

    public function withItems(array $items): self
    {
        return clone($this, ['items' => $items]);
    }

    public function withPdfUrl(string $url): self
    {
        return clone($this, ['pdfUrl' => $url]);
    }
}
