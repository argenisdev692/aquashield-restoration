<?php

declare(strict_types=1);

namespace Src\Modules\Invoices\Domain\ValueObjects;

enum InvoiceStatus: string
{
    case Draft     = 'draft';
    case Sent      = 'sent';
    case Paid      = 'paid';
    case Cancelled = 'cancelled';
    case PrintPdf  = 'print_pdf';

    public function label(): string
    {
        return match($this) {
            self::Draft     => 'Draft',
            self::Sent      => 'Sent',
            self::Paid      => 'Paid',
            self::Cancelled => 'Cancelled',
            self::PrintPdf  => 'Print PDF',
        };
    }

    public function isActive(): bool
    {
        return $this !== self::Cancelled;
    }
}
