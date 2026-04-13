import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import {
    ArrowLeft, Pencil, FileText, User, Building2, ClipboardList,
    ExternalLink, DollarSign,
} from 'lucide-react';
import { motion } from 'framer-motion';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useInvoice } from '@/modules/invoices/hooks/useInvoice';
import { InvoiceStatusBadge } from '@/modules/invoices/components/InvoiceStatusBadge';
import { formatDateShort, formatCurrency } from '@/common/helpers/formatDate';

interface Props extends PageProps { uuid: string }

function SectionCard({ title, icon, children }: { title: string; icon: React.ReactNode; children: React.ReactNode }): React.JSX.Element {
    return (
        <div style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', borderRadius: 'var(--radius-lg)', overflow: 'hidden' }}>
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, padding: '12px 16px', borderBottom: '1px solid var(--border-subtle)', background: 'var(--bg-surface)' }}>
                <span style={{ color: 'var(--accent-primary)' }}>{icon}</span>
                <span style={{ fontSize: 11, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', textTransform: 'uppercase', letterSpacing: '0.08em' }}>{title}</span>
            </div>
            <div style={{ padding: 16 }}>{children}</div>
        </div>
    );
}

function Field({ label, value }: { label: string; value: React.ReactNode }): React.JSX.Element {
    return (
        <div style={{ display: 'flex', gap: 8, marginBottom: 10, fontSize: 13, fontFamily: 'var(--font-sans)' }}>
            <span style={{ color: 'var(--text-muted)', minWidth: 150, flexShrink: 0 }}>{label}</span>
            <span style={{ color: 'var(--text-primary)', fontWeight: 500 }}>{value ?? <span style={{ color: 'var(--text-disabled)' }}>—</span>}</span>
        </div>
    );
}

export default function InvoiceShowPage(): React.JSX.Element {
    const { uuid } = usePage<Props>().props;
    const { data: invoice, isPending, isError } = useInvoice(uuid);

    return (
        <>
            <Head title={invoice ? `Invoice ${invoice.invoice_number}` : 'Invoice'} />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    {/* Header */}
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                            <Link href="/invoices" style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: 36, height: 36, borderRadius: 'var(--radius-md)', border: '1px solid var(--border-default)', background: 'var(--bg-card)', color: 'var(--text-muted)', textDecoration: 'none' }} aria-label="Back to invoices">
                                <ArrowLeft size={16} />
                            </Link>
                            <div>
                                <h1 style={{ margin: 0, fontSize: 24, fontWeight: 800, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                                    {invoice ? invoice.invoice_number : 'Invoice'}
                                </h1>
                                {invoice && (
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginTop: 4 }}>
                                        <InvoiceStatusBadge status={invoice.status} />
                                        <span style={{ fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                            {formatDateShort(invoice.invoice_date)}
                                        </span>
                                    </div>
                                )}
                            </div>
                        </div>
                        {invoice && (
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                <button
                                    onClick={() => window.open(`/invoices/data/admin/${uuid}/invoice-pdf`, '_blank')}
                                    style={{ display: 'flex', alignItems: 'center', gap: 6, padding: '8px 16px', borderRadius: 'var(--radius-md)', border: '1px solid color-mix(in srgb, var(--accent-error) 40%, transparent)', background: 'color-mix(in srgb, var(--accent-error) 10%, transparent)', color: 'var(--accent-error)', fontSize: 13, fontWeight: 600, fontFamily: 'var(--font-sans)', cursor: 'pointer' }}
                                    aria-label="Download PDF"
                                >
                                    <ExternalLink size={14} /> Download PDF
                                </button>
                                <PermissionGuard permissions={['UPDATE_INVOICE']}>
                                    <Link href={`/invoices/${uuid}/edit`} style={{ display: 'flex', alignItems: 'center', gap: 6, padding: '8px 16px', borderRadius: 'var(--radius-md)', border: '1px solid color-mix(in srgb, var(--accent-primary) 40%, transparent)', background: 'color-mix(in srgb, var(--accent-primary) 10%, transparent)', color: 'var(--accent-primary)', fontSize: 13, fontWeight: 600, fontFamily: 'var(--font-sans)', textDecoration: 'none' }}>
                                        <Pencil size={14} /> Edit
                                    </Link>
                                </PermissionGuard>
                            </div>
                        )}
                    </div>

                    {isPending && (
                        <div style={{ display: 'flex', justifyContent: 'center', padding: 48 }}>
                            <div style={{ width: 32, height: 32, borderRadius: '50%', border: '3px solid var(--accent-primary)', borderTopColor: 'transparent', animation: 'spin 0.8s linear infinite' }} />
                        </div>
                    )}

                    {isError && (
                        <div style={{ padding: 24, textAlign: 'center', color: 'var(--accent-error)', fontFamily: 'var(--font-sans)', fontSize: 14 }}>
                            Failed to load invoice.
                        </div>
                    )}

                    {invoice && (
                        <motion.div
                            initial={{ opacity: 0, y: 8 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.25 }}
                            style={{ display: 'flex', flexDirection: 'column', gap: 16 }}
                        >
                            {/* Balance highlight */}
                            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3, 1fr)', gap: 12 }}>
                                {[
                                    { label: 'Subtotal', value: invoice.subtotal, accent: 'var(--accent-info)' },
                                    { label: 'Tax', value: invoice.tax_amount, accent: 'var(--accent-warning)' },
                                    { label: 'Balance Due', value: invoice.balance_due, accent: 'var(--accent-success)' },
                                ].map((stat) => (
                                    <div key={stat.label} style={{ background: `color-mix(in srgb, ${stat.accent} 8%, var(--bg-card))`, border: `1px solid color-mix(in srgb, ${stat.accent} 25%, transparent)`, borderRadius: 'var(--radius-lg)', padding: '16px 20px' }}>
                                        <p style={{ margin: 0, fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: stat.accent, fontFamily: 'var(--font-sans)' }}>{stat.label}</p>
                                        <p style={{ margin: '6px 0 0', fontSize: 22, fontWeight: 800, color: stat.accent, fontFamily: 'var(--font-mono)' }}>{formatCurrency(stat.value)}</p>
                                    </div>
                                ))}
                            </div>

                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                                <SectionCard title="Invoice Details" icon={<FileText size={14} />}>
                                    <Field label="Invoice #" value={<span style={{ fontFamily: 'var(--font-mono)', fontWeight: 700 }}>{invoice.invoice_number}</span>} />
                                    <Field label="Date" value={formatDateShort(invoice.invoice_date)} />
                                    <Field label="Status" value={<InvoiceStatusBadge status={invoice.status} />} />
                                    <Field label="Price List Code" value={invoice.price_list_code} />
                                    <Field label="Type of Loss" value={invoice.type_of_loss} />
                                    {invoice.notes && <Field label="Notes" value={<span style={{ whiteSpace: 'pre-wrap' }}>{invoice.notes}</span>} />}
                                </SectionCard>

                                <SectionCard title="Bill To" icon={<User size={14} />}>
                                    <Field label="Name" value={invoice.bill_to_name} />
                                    <Field label="Address" value={invoice.bill_to_address} />
                                    <Field label="Email" value={invoice.bill_to_email} />
                                    <Field label="Phone" value={invoice.bill_to_phone} />
                                </SectionCard>
                            </div>

                            {(invoice.claim_number || invoice.policy_number || invoice.insurance_company) && (
                                <SectionCard title="Claim Information" icon={<Building2 size={14} />}>
                                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: '0 24px' }}>
                                        <Field label="Claim #" value={invoice.claim_number} />
                                        <Field label="Policy #" value={invoice.policy_number} />
                                        <Field label="Insurance Co." value={invoice.insurance_company} />
                                        <Field label="Date of Loss" value={formatDateShort(invoice.date_of_loss)} />
                                        <Field label="Date Received" value={formatDateShort(invoice.date_received)} />
                                        <Field label="Date Inspected" value={formatDateShort(invoice.date_inspected)} />
                                    </div>
                                </SectionCard>
                            )}

                            {/* Line Items */}
                            <SectionCard title={`Line Items (${invoice.items.length})`} icon={<ClipboardList size={14} />}>
                                {invoice.items.length === 0 ? (
                                    <p style={{ margin: 0, fontSize: 13, color: 'var(--text-disabled)', fontFamily: 'var(--font-sans)' }}>No line items.</p>
                                ) : (
                                    <div style={{ overflowX: 'auto' }}>
                                        <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                                            <thead>
                                                <tr>
                                                    {['#', 'Service', 'Description', 'Qty', 'Rate', 'Amount'].map((h) => (
                                                        <th key={h} style={{ padding: '8px 12px', textAlign: h === 'Amount' || h === 'Rate' || h === 'Qty' ? 'right' : 'left', fontSize: 10, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.08em', color: 'var(--text-muted)', borderBottom: '1px solid var(--border-subtle)', fontFamily: 'var(--font-sans)' }}>{h}</th>
                                                    ))}
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {invoice.items.map((item, idx) => (
                                                    <tr key={item.uuid} style={{ background: idx % 2 === 1 ? 'color-mix(in srgb, var(--border-subtle) 40%, transparent)' : undefined }}>
                                                        <td style={{ padding: '10px 12px', fontSize: 11, color: 'var(--text-disabled)', fontFamily: 'var(--font-sans)' }}>{idx + 1}</td>
                                                        <td style={{ padding: '10px 12px', fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>{item.service_name}</td>
                                                        <td style={{ padding: '10px 12px', fontSize: 12, color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)', maxWidth: 260 }}>{item.description}</td>
                                                        <td style={{ padding: '10px 12px', fontSize: 13, color: 'var(--text-primary)', textAlign: 'right', fontFamily: 'var(--font-mono)' }}>{item.quantity}</td>
                                                        <td style={{ padding: '10px 12px', fontSize: 13, color: 'var(--text-secondary)', textAlign: 'right', fontFamily: 'var(--font-mono)' }}>{formatCurrency(item.rate)}</td>
                                                        <td style={{ padding: '10px 12px', fontSize: 13, fontWeight: 700, color: 'var(--accent-success)', textAlign: 'right', fontFamily: 'var(--font-mono)' }}>{formatCurrency(item.amount)}</td>
                                                    </tr>
                                                ))}
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colSpan={4} />
                                                    <td style={{ padding: '12px', fontSize: 12, color: 'var(--text-muted)', borderTop: '1px solid var(--border-default)', fontFamily: 'var(--font-sans)', fontWeight: 700, textAlign: 'right' }}>Subtotal</td>
                                                    <td style={{ padding: '12px', fontSize: 13, color: 'var(--text-primary)', borderTop: '1px solid var(--border-default)', fontFamily: 'var(--font-mono)', fontWeight: 700, textAlign: 'right' }}>{formatCurrency(invoice.subtotal)}</td>
                                                </tr>
                                                <tr>
                                                    <td colSpan={4} />
                                                    <td style={{ padding: '4px 12px', fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)', textAlign: 'right' }}>Tax</td>
                                                    <td style={{ padding: '4px 12px', fontSize: 13, color: 'var(--accent-warning)', fontFamily: 'var(--font-mono)', textAlign: 'right' }}>{formatCurrency(invoice.tax_amount)}</td>
                                                </tr>
                                                <tr>
                                                    <td colSpan={4} />
                                                    <td style={{ padding: '12px', fontSize: 13, fontWeight: 800, color: 'var(--text-primary)', borderTop: '2px solid var(--border-default)', fontFamily: 'var(--font-sans)', textAlign: 'right' }}>Balance Due</td>
                                                    <td style={{ padding: '12px', fontSize: 16, fontWeight: 800, color: 'var(--accent-success)', borderTop: '2px solid var(--border-default)', fontFamily: 'var(--font-mono)', textAlign: 'right' }}>{formatCurrency(invoice.balance_due)}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                )}
                            </SectionCard>

                            <div style={{ display: 'flex', gap: 16, fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)', paddingTop: 4 }}>
                                <span style={{ display: 'flex', alignItems: 'center', gap: 4 }}><DollarSign size={10} /> Created: {formatDateShort(invoice.created_at)}</span>
                                {invoice.deleted_at && <span style={{ color: 'var(--accent-error)' }}>Deleted: {formatDateShort(invoice.deleted_at)}</span>}
                            </div>
                        </motion.div>
                    )}
                </div>
            </AppLayout>
        </>
    );
}
