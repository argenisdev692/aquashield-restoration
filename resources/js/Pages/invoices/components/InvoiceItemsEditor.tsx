import * as React from 'react';
import { Plus, Trash2 } from 'lucide-react';
import { formatCurrency } from '@/common/helpers/formatDate';

export interface EditorItem {
    _key: string;
    service_name: string;
    description: string;
    quantity: number;
    rate: number;
    sort_order: number;
}

interface InvoiceItemsEditorProps {
    items: EditorItem[];
    onChange: (items: EditorItem[]) => void;
    taxAmount: number;
    onTaxChange: (value: number) => void;
}

function newItem(sortOrder: number): EditorItem {
    return {
        _key: crypto.randomUUID(),
        service_name: '',
        description: '',
        quantity: 1,
        rate: 0,
        sort_order: sortOrder,
    };
}

export function InvoiceItemsEditor({ items, onChange, taxAmount, onTaxChange }: InvoiceItemsEditorProps): React.JSX.Element {
    const subtotal = items.reduce((acc, item) => acc + item.quantity * item.rate, 0);
    const balanceDue = subtotal + taxAmount;

    const handleAdd = () => {
        onChange([...items, newItem(items.length)]);
    };

    const handleRemove = (key: string) => {
        onChange(items.filter((i) => i._key !== key).map((i, idx) => ({ ...i, sort_order: idx })));
    };

    const handleChange = (key: string, field: keyof Omit<EditorItem, '_key'>, value: string | number) => {
        onChange(
            items.map((i) =>
                i._key === key ? { ...i, [field]: typeof value === 'string' && (field === 'quantity' || field === 'rate' || field === 'sort_order') ? parseFloat(value) || 0 : value } : i,
            ),
        );
    };

    const inputStyle: React.CSSProperties = {
        width: '100%',
        height: 34,
        padding: '0 10px',
        borderRadius: 'var(--radius-sm)',
        border: '1px solid var(--input-border)',
        background: 'var(--input-bg)',
        color: 'var(--input-text)',
        fontSize: 12,
        fontFamily: 'var(--font-sans)',
        outline: 'none',
        transition: 'border-color 0.2s',
    };

    const thStyle: React.CSSProperties = {
        padding: '8px 10px',
        textAlign: 'left',
        fontSize: 10,
        fontWeight: 700,
        fontFamily: 'var(--font-sans)',
        textTransform: 'uppercase',
        letterSpacing: '0.08em',
        color: 'var(--text-muted)',
        borderBottom: '1px solid var(--border-subtle)',
        whiteSpace: 'nowrap',
    };

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 0 }}>
            <div style={{ overflowX: 'auto' }}>
                <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                    <thead>
                        <tr>
                            <th style={{ ...thStyle, width: '22%' }}>Service</th>
                            <th style={{ ...thStyle, width: '30%' }}>Description</th>
                            <th style={{ ...thStyle, width: '10%' }}>Qty</th>
                            <th style={{ ...thStyle, width: '14%' }}>Rate</th>
                            <th style={{ ...thStyle, width: '14%' }}>Amount</th>
                            <th style={{ ...thStyle, width: '10%' }}></th>
                        </tr>
                    </thead>
                    <tbody>
                        {items.length === 0 && (
                            <tr>
                                <td
                                    colSpan={6}
                                    style={{ padding: '20px 10px', textAlign: 'center', color: 'var(--text-disabled)', fontSize: 12, fontFamily: 'var(--font-sans)' }}
                                >
                                    No line items. Click "Add Item" to begin.
                                </td>
                            </tr>
                        )}
                        {items.map((item, idx) => (
                            <tr key={item._key} style={{ background: idx % 2 === 1 ? 'color-mix(in srgb, var(--border-subtle) 50%, transparent)' : undefined }}>
                                <td style={{ padding: '6px 8px' }}>
                                    <input
                                        type="text"
                                        value={item.service_name}
                                        onChange={(e) => handleChange(item._key, 'service_name', e.target.value)}
                                        placeholder="Service name"
                                        style={inputStyle}
                                        aria-label="Service name"
                                    />
                                </td>
                                <td style={{ padding: '6px 8px' }}>
                                    <input
                                        type="text"
                                        value={item.description}
                                        onChange={(e) => handleChange(item._key, 'description', e.target.value)}
                                        placeholder="Description"
                                        style={inputStyle}
                                        aria-label="Description"
                                    />
                                </td>
                                <td style={{ padding: '6px 8px' }}>
                                    <input
                                        type="number"
                                        min={1}
                                        value={item.quantity}
                                        onChange={(e) => handleChange(item._key, 'quantity', e.target.value)}
                                        style={{ ...inputStyle, width: '100%' }}
                                        aria-label="Quantity"
                                    />
                                </td>
                                <td style={{ padding: '6px 8px' }}>
                                    <input
                                        type="number"
                                        min={0}
                                        step="0.01"
                                        value={item.rate}
                                        onChange={(e) => handleChange(item._key, 'rate', e.target.value)}
                                        style={{ ...inputStyle, width: '100%', fontFamily: 'var(--font-mono)' }}
                                        aria-label="Rate"
                                    />
                                </td>
                                <td style={{ padding: '6px 8px' }}>
                                    <span
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            height: 34,
                                            paddingLeft: 10,
                                            fontSize: 12,
                                            fontFamily: 'var(--font-mono)',
                                            fontWeight: 600,
                                            color: 'var(--accent-success)',
                                        }}
                                    >
                                        {formatCurrency(item.quantity * item.rate)}
                                    </span>
                                </td>
                                <td style={{ padding: '6px 8px', textAlign: 'center' }}>
                                    <button
                                        type="button"
                                        onClick={() => handleRemove(item._key)}
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            width: 28,
                                            height: 28,
                                            borderRadius: 6,
                                            border: '1px solid color-mix(in srgb, var(--accent-error) 30%, transparent)',
                                            background: 'color-mix(in srgb, var(--accent-error) 8%, transparent)',
                                            color: 'var(--accent-error)',
                                            cursor: 'pointer',
                                            margin: '0 auto',
                                        }}
                                        aria-label="Remove item"
                                        title="Remove item"
                                    >
                                        <Trash2 size={12} />
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    padding: '12px 8px',
                    borderTop: '1px solid var(--border-subtle)',
                    flexWrap: 'wrap',
                    gap: 12,
                }}
            >
                <button
                    type="button"
                    onClick={handleAdd}
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 6,
                        padding: '6px 14px',
                        borderRadius: 'var(--radius-md)',
                        border: '1px solid color-mix(in srgb, var(--accent-primary) 35%, transparent)',
                        background: 'color-mix(in srgb, var(--accent-primary) 10%, transparent)',
                        color: 'var(--accent-primary)',
                        fontSize: 12,
                        fontWeight: 600,
                        fontFamily: 'var(--font-sans)',
                        cursor: 'pointer',
                    }}
                >
                    <Plus size={13} />
                    Add Item
                </button>

                <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'flex-end', gap: 6, minWidth: 200 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', width: '100%', fontSize: 13, fontFamily: 'var(--font-sans)' }}>
                        <span style={{ color: 'var(--text-muted)' }}>Subtotal</span>
                        <span style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-mono)', fontWeight: 600 }}>
                            {formatCurrency(subtotal)}
                        </span>
                    </div>

                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', width: '100%', fontSize: 13, fontFamily: 'var(--font-sans)', gap: 8 }}>
                        <span style={{ color: 'var(--text-muted)', flexShrink: 0 }}>Tax</span>
                        <input
                            type="number"
                            min={0}
                            step="0.01"
                            value={taxAmount}
                            onChange={(e) => onTaxChange(parseFloat(e.target.value) || 0)}
                            style={{
                                width: 110,
                                height: 28,
                                padding: '0 8px',
                                borderRadius: 'var(--radius-sm)',
                                border: '1px solid var(--input-border)',
                                background: 'var(--input-bg)',
                                color: 'var(--input-text)',
                                fontSize: 12,
                                fontFamily: 'var(--font-mono)',
                                outline: 'none',
                                textAlign: 'right',
                            }}
                            aria-label="Tax amount"
                        />
                    </div>

                    <div
                        style={{
                            display: 'flex',
                            justifyContent: 'space-between',
                            width: '100%',
                            fontSize: 14,
                            fontFamily: 'var(--font-sans)',
                            paddingTop: 8,
                            borderTop: '1px solid var(--border-default)',
                        }}
                    >
                        <span style={{ color: 'var(--text-primary)', fontWeight: 700 }}>Balance Due</span>
                        <span style={{ color: 'var(--accent-success)', fontFamily: 'var(--font-mono)', fontWeight: 800, fontSize: 16 }}>
                            {formatCurrency(balanceDue)}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    );
}
