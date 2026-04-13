import * as React from 'react';
import { Link } from '@inertiajs/react';
import axios from 'axios';
import { useQuery } from '@tanstack/react-query';
import { keepPreviousData } from '@tanstack/react-query';
import { motion, AnimatePresence } from 'framer-motion';
import { Receipt, ExternalLink, Plus, DollarSign, FileText, Clock, CheckCircle2 } from 'lucide-react';
import { InvoiceStatusBadge } from '@/modules/invoices/components/InvoiceStatusBadge';
import { formatDateShort, formatCurrency } from '@/common/helpers/formatDate';
import type { InvoiceListItem, PaginatedInvoiceResponse } from '@/modules/invoices/types';

interface InvoiceClaimTimelineProps {
    claimId: number;
}

function useClaimInvoices(claimId: number) {
    return useQuery<PaginatedInvoiceResponse, Error>({
        queryKey: ['invoices', 'claim', claimId],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedInvoiceResponse>('/invoices/data/admin', {
                params: { claim_id: claimId, per_page: 50 },
            });
            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
        enabled: !!claimId,
    });
}

interface InvoiceTimelineCardProps {
    invoice: InvoiceListItem;
    index: number;
    isLast: boolean;
}

const STATUS_ACCENT: Record<string, string> = {
    paid:      'var(--accent-success)',
    sent:      'var(--accent-info)',
    draft:     'var(--accent-warning)',
    cancelled: 'var(--accent-error)',
    print_pdf: 'var(--accent-secondary)',
};

function InvoiceTimelineCard({ invoice, index, isLast }: InvoiceTimelineCardProps): React.JSX.Element {
    const accent = STATUS_ACCENT[invoice.status] ?? 'var(--accent-primary)';
    const isPaid = invoice.status === 'paid';

    return (
        <motion.div
            initial={{ opacity: 0, x: -16 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.25, delay: index * 0.06, ease: [0.25, 0.46, 0.45, 0.94] }}
            style={{ display: 'flex', gap: 0 }}
        >
            {/* Timeline spine */}
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', marginRight: 18, flexShrink: 0 }}>
                <div
                    style={{
                        width: 40,
                        height: 40,
                        borderRadius: '50%',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        background: `color-mix(in srgb, ${accent} 15%, var(--bg-card))`,
                        border: `2px solid color-mix(in srgb, ${accent} 40%, transparent)`,
                        color: accent,
                        boxShadow: `0 0 0 4px color-mix(in srgb, ${accent} 8%, transparent)`,
                        flexShrink: 0,
                    }}
                >
                    {isPaid ? <CheckCircle2 size={18} /> : <Receipt size={18} />}
                </div>
                {!isLast && (
                    <div
                        style={{
                            width: 2,
                            flex: 1,
                            minHeight: 24,
                            background: `linear-gradient(to bottom, color-mix(in srgb, ${accent} 35%, transparent), var(--border-subtle))`,
                            borderRadius: 1,
                            marginTop: 5,
                        }}
                    />
                )}
            </div>

            {/* Card */}
            <div
                style={{
                    flex: 1,
                    marginBottom: isLast ? 0 : 22,
                    background: 'var(--bg-card)',
                    border: `1px solid color-mix(in srgb, ${accent} 22%, var(--border-default))`,
                    borderRadius: 'var(--radius-lg)',
                    overflow: 'hidden',
                    boxShadow: `0 2px 10px color-mix(in srgb, ${accent} 8%, transparent)`,
                    opacity: invoice.deleted_at ? 0.65 : 1,
                }}
            >
                {/* Card header */}
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        padding: '12px 16px',
                        borderBottom: '1px solid var(--border-subtle)',
                        background: `color-mix(in srgb, ${accent} 5%, var(--bg-surface))`,
                        flexWrap: 'wrap',
                        gap: 8,
                    }}
                >
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap' }}>
                        <span style={{ fontSize: 13, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-mono)' }}>
                            {invoice.invoice_number}
                        </span>
                        <InvoiceStatusBadge status={invoice.status} />
                        {invoice.deleted_at && (
                            <span style={{ fontSize: 10, fontWeight: 700, padding: '2px 8px', borderRadius: 999, background: 'color-mix(in srgb, var(--accent-error) 12%, transparent)', color: 'var(--accent-error)', border: '1px solid color-mix(in srgb, var(--accent-error) 25%, transparent)' }}>
                                DELETED
                            </span>
                        )}
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                        <Link
                            href={`/invoices/${invoice.uuid}`}
                            style={{ display: 'flex', alignItems: 'center', gap: 5, padding: '5px 12px', borderRadius: 'var(--radius-sm)', border: `1px solid color-mix(in srgb, ${accent} 30%, transparent)`, background: `color-mix(in srgb, ${accent} 10%, transparent)`, color: accent, fontSize: 11, fontWeight: 700, fontFamily: 'var(--font-sans)', textDecoration: 'none' }}
                            aria-label={`View invoice ${invoice.invoice_number}`}
                        >
                            <ExternalLink size={11} /> View
                        </Link>
                    </div>
                </div>

                {/* Card body */}
                <div style={{ padding: '12px 16px', display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: '8px 16px' }}>
                    <div>
                        <p style={{ margin: 0, fontSize: 10, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>Bill To</p>
                        <p style={{ margin: '3px 0 0', fontSize: 12, fontWeight: 600, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>{invoice.bill_to_name}</p>
                    </div>
                    <div>
                        <p style={{ margin: 0, fontSize: 10, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>Invoice Date</p>
                        <p style={{ margin: '3px 0 0', fontSize: 12, color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)', display: 'flex', alignItems: 'center', gap: 4 }}>
                            <Clock size={10} style={{ color: 'var(--text-disabled)' }} />
                            {formatDateShort(invoice.invoice_date)}
                        </p>
                    </div>
                    <div>
                        <p style={{ margin: 0, fontSize: 10, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>Balance Due</p>
                        <p style={{ margin: '3px 0 0', fontSize: 14, fontWeight: 800, color: accent, fontFamily: 'var(--font-mono)' }}>
                            {formatCurrency(invoice.balance_due)}
                        </p>
                    </div>
                    {invoice.insurance_company && (
                        <div style={{ gridColumn: '1 / -1' }}>
                            <p style={{ margin: 0, fontSize: 10, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>Insurance Co.</p>
                            <p style={{ margin: '3px 0 0', fontSize: 12, color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}>{invoice.insurance_company}</p>
                        </div>
                    )}
                </div>

                {/* Footer */}
                <div style={{ padding: '8px 16px', borderTop: '1px solid var(--border-subtle)', display: 'flex', alignItems: 'center', gap: 16, flexWrap: 'wrap' }}>
                    <span style={{ fontSize: 11, color: 'var(--text-disabled)', fontFamily: 'var(--font-sans)', display: 'flex', alignItems: 'center', gap: 4 }}>
                        <FileText size={10} />
                        {invoice.items_count} {invoice.items_count === 1 ? 'item' : 'items'}
                    </span>
                    <span style={{ fontSize: 11, color: 'var(--text-disabled)', fontFamily: 'var(--font-sans)' }}>
                        Subtotal: {formatCurrency(invoice.subtotal)}
                    </span>
                    {invoice.tax_amount > 0 && (
                        <span style={{ fontSize: 11, color: 'var(--accent-warning)', fontFamily: 'var(--font-sans)' }}>
                            Tax: {formatCurrency(invoice.tax_amount)}
                        </span>
                    )}
                    <span style={{ marginLeft: 'auto', fontSize: 10, color: 'var(--text-disabled)', fontFamily: 'var(--font-sans)' }}>
                        {formatDateShort(invoice.created_at)}
                    </span>
                </div>
            </div>
        </motion.div>
    );
}

export function InvoiceClaimTimeline({ claimId }: InvoiceClaimTimelineProps): React.JSX.Element {
    const { data, isPending, isError } = useClaimInvoices(claimId);
    const invoices = data?.data ?? [];
    const total = data?.meta?.total ?? 0;

    const paidCount = invoices.filter((i) => i.status === 'paid' && !i.deleted_at).length;
    const totalBalance = invoices.filter((i) => !i.deleted_at).reduce((acc, i) => acc + i.balance_due, 0);

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>
            {/* Summary bar */}
            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    padding: '14px 18px',
                    background: 'var(--bg-card)',
                    border: '1px solid var(--border-default)',
                    borderRadius: 'var(--radius-lg)',
                    flexWrap: 'wrap',
                    gap: 12,
                }}
            >
                <div>
                    <p style={{ margin: 0, fontSize: 14, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                        Invoice Timeline
                    </p>
                    <p style={{ margin: '2px 0 0', fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                        {total} {total === 1 ? 'invoice' : 'invoices'} linked to this claim.
                    </p>
                </div>
                <div style={{ display: 'flex', alignItems: 'center', gap: 20, flexShrink: 0 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, fontFamily: 'var(--font-sans)', color: 'var(--accent-success)', fontWeight: 600 }}>
                        <CheckCircle2 size={13} />
                        {paidCount} Paid
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, fontFamily: 'var(--font-sans)', color: 'var(--accent-primary)', fontWeight: 700 }}>
                        <DollarSign size={13} />
                        {formatCurrency(totalBalance)} total
                    </div>
                    <Link
                        href={`/invoices/create`}
                        style={{ display: 'flex', alignItems: 'center', gap: 6, padding: '6px 14px', borderRadius: 'var(--radius-md)', background: 'var(--accent-primary)', color: '#fff', fontSize: 12, fontWeight: 700, fontFamily: 'var(--font-sans)', textDecoration: 'none' }}
                        aria-label="Create invoice for claim"
                    >
                        <Plus size={12} /> New Invoice
                    </Link>
                </div>
            </div>

            {isPending && (
                <div style={{ display: 'flex', justifyContent: 'center', padding: 32 }}>
                    <div style={{ width: 28, height: 28, borderRadius: '50%', border: '3px solid var(--accent-primary)', borderTopColor: 'transparent', animation: 'spin 0.8s linear infinite' }} />
                </div>
            )}

            {isError && (
                <div style={{ padding: 20, textAlign: 'center', color: 'var(--accent-error)', fontSize: 13, fontFamily: 'var(--font-sans)' }}>
                    Failed to load invoices.
                </div>
            )}

            {!isPending && !isError && invoices.length === 0 && (
                <div
                    style={{
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        gap: 10,
                        padding: '32px 16px',
                        background: 'var(--bg-card)',
                        border: '1px dashed var(--border-default)',
                        borderRadius: 'var(--radius-lg)',
                    }}
                >
                    <Receipt size={28} style={{ color: 'var(--text-disabled)' }} />
                    <p style={{ margin: 0, fontSize: 13, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                        No invoices linked to this claim yet.
                    </p>
                </div>
            )}

            {!isPending && invoices.length > 0 && (
                <AnimatePresence mode="popLayout">
                    <div style={{ paddingLeft: 4 }}>
                        {invoices.map((invoice, idx) => (
                            <InvoiceTimelineCard
                                key={invoice.uuid}
                                invoice={invoice}
                                index={idx}
                                isLast={idx === invoices.length - 1}
                            />
                        ))}
                    </div>
                </AnimatePresence>
            )}
        </div>
    );
}
