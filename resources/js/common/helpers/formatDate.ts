export function formatDateShort(value: string | null | undefined): string {
    if (!value) return '—';
    const d = new Date(value);
    if (isNaN(d.getTime())) return value;
    return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

export function formatCurrency(value: number | null | undefined, currency = 'USD'): string {
    if (value == null) return '—';
    return new Intl.NumberFormat('en-US', { style: 'currency', currency }).format(value);
}
