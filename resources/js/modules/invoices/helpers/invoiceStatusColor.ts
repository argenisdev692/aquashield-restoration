import type { InvoiceStatus } from '../types';

interface StatusConfig {
    label: string;
    accent: string;
}

export function invoiceStatusConfig(status: InvoiceStatus): StatusConfig {
    const map: Record<InvoiceStatus, StatusConfig> = {
        draft:     { label: 'Draft',     accent: 'var(--accent-warning)' },
        sent:      { label: 'Sent',      accent: 'var(--accent-info)' },
        paid:      { label: 'Paid',      accent: 'var(--accent-success)' },
        cancelled: { label: 'Cancelled', accent: 'var(--accent-error)' },
        print_pdf: { label: 'Print PDF', accent: 'var(--accent-secondary)' },
    };
    return map[status] ?? { label: status, accent: 'var(--text-muted)' };
}
