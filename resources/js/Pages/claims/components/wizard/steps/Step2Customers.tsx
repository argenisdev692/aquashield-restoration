import * as React from 'react';
import { Search, User, X, Loader2, Crown, Users, UserPlus } from 'lucide-react';
import { useClaimWizardStore } from '@/modules/claims/stores/claimWizardStore';
import { useCustomers } from '@/modules/customers/hooks/useCustomers';
import type { CustomerSlot, CustomerSlotRole } from '@/modules/claims/types';
import { CreateCustomerModal } from '../modals/CreateCustomerModal';

const ROLE_META: Record<CustomerSlotRole, { label: string; icon: React.ReactNode; color: string }> = {
    owner:    { label: 'Owner',         icon: <Crown size={14} />,    color: 'var(--accent-primary)' },
    co_owner: { label: 'Co-Owner',      icon: <Users size={14} />,    color: 'var(--accent-secondary)' },
    extra:    { label: 'Extra Contact', icon: <UserPlus size={14} />, color: 'var(--text-muted)' },
};

interface SlotRowProps {
    slot: CustomerSlot;
    onSearch: (role: CustomerSlotRole, q: string) => void;
    onSelect: (role: CustomerSlotRole, id: number, uuid: string, label: string) => void;
    onClear: (role: CustomerSlotRole) => void;
    onAddNew: (role: CustomerSlotRole) => void;
    searchQuery: string;
    results: { uuid: string; id: number; full_name: string; email: string | null }[];
    isPending: boolean;
    activeRole: CustomerSlotRole | null;
}

function SlotRow({
    slot, onSearch, onSelect, onClear, onAddNew,
    searchQuery, results, isPending, activeRole,
}: SlotRowProps): React.JSX.Element {
    const meta = ROLE_META[slot.role];
    const isActive = activeRole === slot.role;

    return (
        <div
            style={{
                background: 'var(--bg-card)',
                border: `1px solid ${slot.customer_id ? 'var(--border-strong)' : 'var(--border-default)'}`,
                borderRadius: 'var(--radius-lg)',
                padding: 16,
                position: 'relative',
                transition: 'border-color 0.2s ease',
            }}
        >
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 10 }}>
                <span style={{ color: meta.color }}>{meta.icon}</span>
                <span
                    style={{
                        fontSize: 12,
                        fontWeight: 700,
                        color: meta.color,
                        textTransform: 'uppercase',
                        letterSpacing: '0.1em',
                        fontFamily: 'var(--font-sans)',
                    }}
                >
                    {meta.label}
                </span>
                {slot.role === 'extra' && (
                    <span style={{ fontSize: 10, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                        (optional)
                    </span>
                )}
            </div>

            {slot.customer_id !== null ? (
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 8 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div
                            style={{
                                width: 36, height: 36, borderRadius: '50%',
                                background: `color-mix(in srgb, ${meta.color} 20%, var(--bg-elevated))`,
                                display: 'flex', alignItems: 'center', justifyContent: 'center',
                                color: meta.color, flexShrink: 0,
                            }}
                        >
                            <User size={16} />
                        </div>
                        <div>
                            <p style={{ margin: 0, fontSize: 14, fontWeight: 600, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                                {slot.customer_label}
                            </p>
                            <p style={{ margin: 0, fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                ID: {slot.customer_id}
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={() => onClear(slot.role)}
                        aria-label={`Remove ${meta.label}`}
                        style={{
                            width: 28, height: 28, borderRadius: 'var(--radius-sm)',
                            border: '1px solid var(--border-default)', background: 'transparent',
                            color: 'var(--accent-error)', cursor: 'pointer',
                            display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0,
                        }}
                    >
                        <X size={14} />
                    </button>
                </div>
            ) : (
                <div style={{ position: 'relative' }}>
                    {/* Search input */}
                    <div style={{ position: 'relative' }}>
                        <span
                            style={{
                                position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)',
                                color: 'var(--text-muted)', pointerEvents: 'none',
                            }}
                        >
                            {isPending && isActive ? <Loader2 size={14} className="animate-spin" /> : <Search size={14} />}
                        </span>
                        <input
                            type="text"
                            value={isActive ? searchQuery : ''}
                            onChange={(e) => onSearch(slot.role, e.target.value)}
                            placeholder={`Search ${meta.label.toLowerCase()}...`}
                            style={{
                                width: '100%', height: 38,
                                paddingLeft: 34, paddingRight: 12,
                                background: 'var(--input-bg)',
                                border: '1px solid var(--input-border)',
                                borderRadius: 'var(--input-radius)',
                                color: 'var(--text-primary)',
                                fontSize: 13, fontFamily: 'var(--font-sans)',
                                outline: 'none', boxSizing: 'border-box',
                            }}
                        />
                    </div>

                    {/* Search results dropdown */}
                    {isActive && searchQuery.length >= 2 && (
                        <div
                            style={{
                                position: 'absolute', top: 'calc(100% + 4px)', left: 0, right: 0,
                                background: 'var(--bg-elevated)', border: '1px solid var(--border-default)',
                                borderRadius: 'var(--radius-md)', zIndex: 60,
                                maxHeight: 180, overflowY: 'auto',
                                boxShadow: '0 8px 24px rgba(0,0,0,0.3)',
                            }}
                        >
                            {results.length === 0 && !isPending ? (
                                <div style={{ padding: '10px 12px' }}>
                                    <p style={{ margin: 0, fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                        No customers found.
                                    </p>
                                </div>
                            ) : (
                                results.map((c) => (
                                    <button
                                        key={c.uuid}
                                        type="button"
                                        onClick={() => onSelect(slot.role, c.id, c.uuid, c.full_name)}
                                        style={{
                                            width: '100%', padding: '9px 12px',
                                            display: 'flex', alignItems: 'center', gap: 10,
                                            background: 'transparent', border: 'none',
                                            borderBottom: '1px solid var(--border-subtle)',
                                            cursor: 'pointer', textAlign: 'left',
                                            color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', fontSize: 13,
                                        }}
                                    >
                                        <User size={13} style={{ color: meta.color, flexShrink: 0 }} />
                                        <div>
                                            <p style={{ margin: 0, fontWeight: 600 }}>{c.full_name}</p>
                                            {c.email && (
                                                <p style={{ margin: 0, fontSize: 11, color: 'var(--text-muted)' }}>{c.email}</p>
                                            )}
                                        </div>
                                    </button>
                                ))
                            )}

                            {/* Add new customer inline option */}
                            <div style={{ padding: '6px 8px', borderTop: '1px solid var(--border-subtle)' }}>
                                <button
                                    type="button"
                                    onClick={() => onAddNew(slot.role)}
                                    style={{
                                        width: '100%', padding: '7px 10px',
                                        background: `color-mix(in srgb, ${meta.color} 10%, var(--bg-card))`,
                                        border: `1px dashed color-mix(in srgb, ${meta.color} 40%, transparent)`,
                                        borderRadius: 'var(--radius-sm)', color: meta.color,
                                        fontSize: 12, fontFamily: 'var(--font-sans)', cursor: 'pointer',
                                        display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6, fontWeight: 700,
                                    }}
                                >
                                    <UserPlus size={12} /> Create new customer
                                </button>
                            </div>
                        </div>
                    )}

                    {/* "Add new" button shown when no search active */}
                    {(!isActive || searchQuery.length < 2) && (
                        <div style={{ marginTop: 8, display: 'flex', justifyContent: 'flex-end' }}>
                            <button
                                type="button"
                                onClick={() => onAddNew(slot.role)}
                                style={{
                                    display: 'flex', alignItems: 'center', gap: 5,
                                    padding: '4px 10px',
                                    background: 'transparent',
                                    border: `1px dashed color-mix(in srgb, ${meta.color} 35%, transparent)`,
                                    borderRadius: 'var(--radius-sm)',
                                    color: meta.color,
                                    fontSize: 11, fontFamily: 'var(--font-sans)', cursor: 'pointer', fontWeight: 600,
                                }}
                            >
                                <UserPlus size={11} /> New customer
                            </button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}

interface Step2CustomersProps {
    onValidChange: (valid: boolean) => void;
}

export function Step2Customers({ onValidChange }: Step2CustomersProps): React.JSX.Element {
    const { form, updateForm } = useClaimWizardStore();
    const [activeRole, setActiveRole] = React.useState<CustomerSlotRole | null>(null);
    const [searchQuery, setSearchQuery] = React.useState('');
    const [modalOpen, setModalOpen] = React.useState(false);
    const [modalTargetRole, setModalTargetRole] = React.useState<CustomerSlotRole>('owner');

    const { data, isPending } = useCustomers({
        search: searchQuery.length >= 2 ? searchQuery : undefined,
        per_page: 20,
        status: 'active',
    });

    const results = (data?.data ?? []).map((c) => ({
        uuid: c.uuid,
        id: c.customer_id,
        full_name: [c.name, c.last_name].filter(Boolean).join(' '),
        email: c.email ?? null,
    }));

    const ownerSelected = form.customer_slots.find((s) => s.role === 'owner')?.customer_id !== null;
    React.useEffect(() => { onValidChange(ownerSelected); }, [ownerSelected, onValidChange]);

    function handleSearch(role: CustomerSlotRole, q: string): void {
        setActiveRole(role);
        setSearchQuery(q);
    }

    function handleSelect(role: CustomerSlotRole, id: number, uuid: string, label: string): void {
        const updated = form.customer_slots.map((s) =>
            s.role === role ? { ...s, customer_id: id, customer_uuid: uuid, customer_label: label } : s,
        );
        updateForm({ customer_slots: updated });
        setSearchQuery('');
        setActiveRole(null);
    }

    function handleClear(role: CustomerSlotRole): void {
        const updated = form.customer_slots.map((s) =>
            s.role === role
                ? { ...s, customer_id: null, customer_uuid: null, customer_label: ROLE_META[role].label }
                : s,
        );
        updateForm({ customer_slots: updated });
    }

    function handleAddNew(role: CustomerSlotRole): void {
        setModalTargetRole(role);
        setSearchQuery('');
        setActiveRole(null);
        setModalOpen(true);
    }

    function handleCustomerCreated(id: number, uuid: string, fullName: string): void {
        handleSelect(modalTargetRole, id, uuid, fullName);
    }

    return (
        <>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
                <div>
                    <h3
                        style={{
                            fontSize: 18, fontWeight: 700,
                            color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', margin: 0,
                        }}
                    >
                        Assign Customers
                    </h3>
                    <p style={{ fontSize: 13, color: 'var(--text-muted)', margin: '4px 0 0', fontFamily: 'var(--font-sans)' }}>
                        Select up to 3 customers for this claim. Owner is required. Use "New customer" to create one inline.
                    </p>
                </div>

                <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                    {form.customer_slots.map((slot) => (
                        <SlotRow
                            key={slot.role}
                            slot={slot}
                            onSearch={handleSearch}
                            onSelect={handleSelect}
                            onClear={handleClear}
                            onAddNew={handleAddNew}
                            searchQuery={searchQuery}
                            results={results}
                            isPending={isPending}
                            activeRole={activeRole}
                        />
                    ))}
                </div>

                {!ownerSelected && (
                    <div
                        style={{
                            padding: '10px 14px',
                            background: 'color-mix(in srgb, var(--accent-warning) 10%, var(--bg-card))',
                            border: '1px solid color-mix(in srgb, var(--accent-warning) 30%, transparent)',
                            borderRadius: 'var(--radius-md)',
                            fontSize: 13, color: 'var(--accent-warning)', fontFamily: 'var(--font-sans)',
                        }}
                    >
                        At least an Owner must be selected to proceed.
                    </div>
                )}
            </div>

            <CreateCustomerModal
                open={modalOpen}
                onClose={() => setModalOpen(false)}
                onCreated={handleCustomerCreated}
            />
        </>
    );
}
