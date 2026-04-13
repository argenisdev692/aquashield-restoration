import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { ArrowLeft } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useInvoice } from '@/modules/invoices/hooks/useInvoice';
import { useUpdateInvoice } from '@/modules/invoices/hooks/useInvoiceMutations';
import { InvoiceItemsEditor } from './components/InvoiceItemsEditor';
import type { EditorItem } from './components/InvoiceItemsEditor';
import type { InvoiceStatus, InvoiceItemPayload } from '@/modules/invoices/types';
import type { AuthPageProps } from '@/types/auth';

interface Props extends PageProps { uuid: string }

const inputSx: React.CSSProperties = {
    width: '100%', height: 40, padding: '0 12px', borderRadius: 'var(--radius-md)',
    border: '1px solid var(--input-border)', background: 'var(--input-bg)',
    color: 'var(--input-text)', fontSize: 14, fontFamily: 'var(--font-sans)', outline: 'none',
};

const labelSx: React.CSSProperties = {
    display: 'block', marginBottom: 6, fontSize: 11, fontWeight: 700,
    textTransform: 'uppercase', letterSpacing: '0.08em', color: 'var(--text-secondary)',
    fontFamily: 'var(--font-sans)',
};

function SectionTitle({ children }: { children: React.ReactNode }): React.JSX.Element {
    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 16, paddingBottom: 8, borderBottom: '1px solid var(--border-subtle)' }}>
            <span style={{ fontSize: 12, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.1em', color: 'var(--accent-primary)', fontFamily: 'var(--font-sans)' }}>{children}</span>
        </div>
    );
}

export default function InvoiceEditPage(): React.JSX.Element {
    const { uuid, auth } = usePage<Props & AuthPageProps>().props;
    const { data: invoice, isPending: isLoading } = useInvoice(uuid);
    const updateInvoice = useUpdateInvoice();

    const [form, setForm] = React.useState({
        invoice_number: '',
        invoice_date: '',
        status: 'draft' as InvoiceStatus,
        bill_to_name: '',
        bill_to_address: '',
        bill_to_email: '',
        bill_to_phone: '',
        claim_id: '',
        claim_number: '',
        policy_number: '',
        insurance_company: '',
        date_of_loss: '',
        date_received: '',
        date_inspected: '',
        date_entered: '',
        price_list_code: '',
        type_of_loss: '',
        notes: '',
    });
    const [items, setItems] = React.useState<EditorItem[]>([]);
    const [taxAmount, setTaxAmount] = React.useState<number>(0);
    const [initialized, setInitialized] = React.useState(false);

    React.useEffect(() => {
        if (invoice && !initialized) {
            setForm({
                invoice_number: invoice.invoice_number,
                invoice_date: invoice.invoice_date,
                status: invoice.status,
                bill_to_name: invoice.bill_to_name,
                bill_to_address: invoice.bill_to_address ?? '',
                bill_to_email: invoice.bill_to_email ?? '',
                bill_to_phone: invoice.bill_to_phone ?? '',
                claim_id: invoice.claim_id ? String(invoice.claim_id) : '',
                claim_number: invoice.claim_number ?? '',
                policy_number: invoice.policy_number ?? '',
                insurance_company: invoice.insurance_company ?? '',
                date_of_loss: invoice.date_of_loss ?? '',
                date_received: invoice.date_received ?? '',
                date_inspected: invoice.date_inspected ?? '',
                date_entered: invoice.date_entered ?? '',
                price_list_code: invoice.price_list_code ?? '',
                type_of_loss: invoice.type_of_loss ?? '',
                notes: invoice.notes ?? '',
            });
            setTaxAmount(invoice.tax_amount);
            setItems(
                invoice.items.map((item) => ({
                    _key: item.uuid,
                    service_name: item.service_name,
                    description: item.description,
                    quantity: item.quantity,
                    rate: item.rate,
                    sort_order: item.sort_order,
                })),
            );
            setInitialized(true);
        }
    }, [invoice, initialized]);

    const handleChange = (field: keyof typeof form, value: string) => setForm((p) => ({ ...p, [field]: value }));

    const subtotal = items.reduce((acc, item) => acc + item.quantity * item.rate, 0);
    const balanceDue = subtotal + taxAmount;

    const handleSubmit = (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        const itemsPayload: InvoiceItemPayload[] = items.map((item, idx) => ({
            service_name: item.service_name,
            description: item.description,
            quantity: item.quantity,
            rate: item.rate,
            amount: item.quantity * item.rate,
            sort_order: idx,
        }));

        updateInvoice.mutate({
            uuid,
            data: {
                user_id: auth.user!.id,
                invoice_number: form.invoice_number,
                invoice_date: form.invoice_date,
                status: form.status,
                bill_to_name: form.bill_to_name,
                bill_to_address: form.bill_to_address || null,
                bill_to_email: form.bill_to_email || null,
                bill_to_phone: form.bill_to_phone || null,
                claim_id: form.claim_id ? parseInt(form.claim_id) : null,
                claim_number: form.claim_number || null,
                policy_number: form.policy_number || null,
                insurance_company: form.insurance_company || null,
                date_of_loss: form.date_of_loss || null,
                date_received: form.date_received || null,
                date_inspected: form.date_inspected || null,
                date_entered: form.date_entered || null,
                price_list_code: form.price_list_code || null,
                type_of_loss: form.type_of_loss || null,
                notes: form.notes || null,
                subtotal,
                tax_amount: taxAmount,
                balance_due: balanceDue,
                items: itemsPayload,
            },
        });
    };

    return (
        <>
            <Head title={invoice ? `Edit ${invoice.invoice_number}` : 'Edit Invoice'} />
            <AppLayout>
                <div className="flex flex-col gap-6" style={{ maxWidth: 900, margin: '0 auto' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                        <Link
                            href={`/invoices/${uuid}`}
                            style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: 36, height: 36, borderRadius: 'var(--radius-md)', border: '1px solid var(--border-default)', background: 'var(--bg-card)', color: 'var(--text-muted)', textDecoration: 'none' }}
                            aria-label="Back"
                        >
                            <ArrowLeft size={16} />
                        </Link>
                        <h1 style={{ margin: 0, fontSize: 24, fontWeight: 800, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                            Edit {invoice?.invoice_number ?? 'Invoice'}
                        </h1>
                    </div>

                    {isLoading && (
                        <div style={{ display: 'flex', justifyContent: 'center', padding: 48 }}>
                            <div style={{ width: 32, height: 32, borderRadius: '50%', border: '3px solid var(--accent-primary)', borderTopColor: 'transparent', animation: 'spin 0.8s linear infinite' }} />
                        </div>
                    )}

                    {!isLoading && (
                        <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>
                            {/* Invoice Info */}
                            <div style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', borderRadius: 'var(--radius-lg)', padding: 24 }}>
                                <SectionTitle>Invoice Info</SectionTitle>
                                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 16 }}>
                                    <div>
                                        <label htmlFor="invoice_number" style={labelSx}>Invoice # <span style={{ color: 'var(--accent-error)' }}>*</span></label>
                                        <input id="invoice_number" type="text" required value={form.invoice_number} onChange={(e) => handleChange('invoice_number', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="invoice_date" style={labelSx}>Invoice Date <span style={{ color: 'var(--accent-error)' }}>*</span></label>
                                        <input id="invoice_date" type="date" required value={form.invoice_date} onChange={(e) => handleChange('invoice_date', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="status" style={labelSx}>Status</label>
                                        <select id="status" value={form.status} onChange={(e) => handleChange('status', e.target.value)} style={{ ...inputSx, cursor: 'pointer' }}>
                                            <option value="draft">Draft</option>
                                            <option value="sent">Sent</option>
                                            <option value="paid">Paid</option>
                                            <option value="cancelled">Cancelled</option>
                                            <option value="print_pdf">Print PDF</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {/* Bill To */}
                            <div style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', borderRadius: 'var(--radius-lg)', padding: 24 }}>
                                <SectionTitle>Bill To</SectionTitle>
                                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                                    <div>
                                        <label htmlFor="bill_to_name" style={labelSx}>Name <span style={{ color: 'var(--accent-error)' }}>*</span></label>
                                        <input id="bill_to_name" type="text" required value={form.bill_to_name} onChange={(e) => handleChange('bill_to_name', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="bill_to_email" style={labelSx}>Email</label>
                                        <input id="bill_to_email" type="email" value={form.bill_to_email} onChange={(e) => handleChange('bill_to_email', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="bill_to_phone" style={labelSx}>Phone</label>
                                        <input id="bill_to_phone" type="text" value={form.bill_to_phone} onChange={(e) => handleChange('bill_to_phone', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="bill_to_address" style={labelSx}>Address</label>
                                        <input id="bill_to_address" type="text" value={form.bill_to_address} onChange={(e) => handleChange('bill_to_address', e.target.value)} style={inputSx} />
                                    </div>
                                </div>
                            </div>

                            {/* Claim Info */}
                            <div style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', borderRadius: 'var(--radius-lg)', padding: 24 }}>
                                <SectionTitle>Claim Information (optional)</SectionTitle>
                                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr 1fr', gap: 16 }}>
                                    <div>
                                        <label htmlFor="claim_id" style={labelSx}>Claim ID (internal)</label>
                                        <input id="claim_id" type="number" value={form.claim_id} onChange={(e) => handleChange('claim_id', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="claim_number" style={labelSx}>Claim #</label>
                                        <input id="claim_number" type="text" value={form.claim_number} onChange={(e) => handleChange('claim_number', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="policy_number" style={labelSx}>Policy #</label>
                                        <input id="policy_number" type="text" value={form.policy_number} onChange={(e) => handleChange('policy_number', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="insurance_company" style={labelSx}>Insurance Company</label>
                                        <input id="insurance_company" type="text" value={form.insurance_company} onChange={(e) => handleChange('insurance_company', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="date_of_loss" style={labelSx}>Date of Loss</label>
                                        <input id="date_of_loss" type="date" value={form.date_of_loss} onChange={(e) => handleChange('date_of_loss', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="type_of_loss" style={labelSx}>Type of Loss</label>
                                        <input id="type_of_loss" type="text" value={form.type_of_loss} onChange={(e) => handleChange('type_of_loss', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="date_received" style={labelSx}>Date Received</label>
                                        <input id="date_received" type="date" value={form.date_received} onChange={(e) => handleChange('date_received', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="date_inspected" style={labelSx}>Date Inspected</label>
                                        <input id="date_inspected" type="date" value={form.date_inspected} onChange={(e) => handleChange('date_inspected', e.target.value)} style={inputSx} />
                                    </div>
                                    <div>
                                        <label htmlFor="price_list_code" style={labelSx}>Price List Code</label>
                                        <input id="price_list_code" type="text" value={form.price_list_code} onChange={(e) => handleChange('price_list_code', e.target.value)} style={inputSx} />
                                    </div>
                                </div>
                                <div style={{ marginTop: 16 }}>
                                    <label htmlFor="notes" style={labelSx}>Notes</label>
                                    <textarea id="notes" value={form.notes} onChange={(e) => handleChange('notes', e.target.value)} rows={3} style={{ ...inputSx, height: 'auto', padding: 12, resize: 'vertical' }} />
                                </div>
                            </div>

                            {/* Line Items */}
                            <div style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', borderRadius: 'var(--radius-lg)', overflow: 'hidden' }}>
                                <div style={{ padding: '14px 20px', borderBottom: '1px solid var(--border-subtle)', background: 'var(--bg-surface)' }}>
                                    <span style={{ fontSize: 11, fontWeight: 700, textTransform: 'uppercase', letterSpacing: '0.1em', color: 'var(--accent-primary)', fontFamily: 'var(--font-sans)' }}>Line Items</span>
                                </div>
                                <InvoiceItemsEditor items={items} onChange={setItems} taxAmount={taxAmount} onTaxChange={setTaxAmount} />
                            </div>

                            <div style={{ display: 'flex', justifyContent: 'flex-end', gap: 12 }}>
                                <Link href={`/invoices/${uuid}`} style={{ display: 'inline-flex', alignItems: 'center', padding: '10px 20px', borderRadius: 'var(--radius-md)', border: '1px solid var(--border-default)', background: 'var(--bg-card)', color: 'var(--text-primary)', fontSize: 13, fontWeight: 600, fontFamily: 'var(--font-sans)', textDecoration: 'none' }}>
                                    Cancel
                                </Link>
                                <button type="submit" disabled={updateInvoice.isPending} style={{ display: 'inline-flex', alignItems: 'center', gap: 8, padding: '10px 24px', borderRadius: 'var(--radius-md)', background: 'var(--accent-primary)', color: '#fff', fontSize: 13, fontWeight: 700, fontFamily: 'var(--font-sans)', cursor: updateInvoice.isPending ? 'not-allowed' : 'pointer', opacity: updateInvoice.isPending ? 0.7 : 1, border: 'none' }}>
                                    {updateInvoice.isPending ? 'Saving…' : 'Save Changes'}
                                </button>
                            </div>
                        </form>
                    )}
                </div>
            </AppLayout>
        </>
    );
}
