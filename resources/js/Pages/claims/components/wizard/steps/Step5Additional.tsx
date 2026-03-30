import * as React from 'react';
import { AlignLeft, Wrench, CheckSquare, User } from 'lucide-react';
import { useClaimWizardStore } from '@/modules/claims/stores/claimWizardStore';
import { useQuery } from '@tanstack/react-query';
import axios from 'axios';

interface SelectOption { id: number; name: string; }

function useCausesOfLoss() {
    return useQuery<SelectOption[], Error>({
        queryKey: ['causes-of-loss', 'select'],
        queryFn: async () => {
            const { data } = await axios.get<{ data: { id: number; cause_of_loss_name: string }[] }>(
                '/causes-of-loss/data/admin',
                { params: { per_page: 100 } },
            );
            return data.data.map((d) => ({ id: d.id, name: d.cause_of_loss_name }));
        },
        staleTime: 1000 * 60 * 10,
    });
}

function useServiceRequests() {
    return useQuery<SelectOption[], Error>({
        queryKey: ['service-requests', 'select'],
        queryFn: async () => {
            const { data } = await axios.get<{ data: { id: number; service_request_name: string }[] }>(
                '/service-requests/data/admin',
                { params: { per_page: 100 } },
            );
            return data.data.map((d) => ({ id: d.id, name: d.service_request_name }));
        },
        staleTime: 1000 * 60 * 10,
    });
}

function useUsers() {
    return useQuery<SelectOption[], Error>({
        queryKey: ['users', 'select'],
        queryFn: async () => {
            const { data } = await axios.get<{ data: { uuid: string; id: number; name: string }[] }>(
                '/users/data/admin',
                { params: { per_page: 200 } },
            );
            return data.data.map((d) => ({ id: d.id, name: d.name }));
        },
        staleTime: 1000 * 60 * 10,
    });
}

const fieldStyle: React.CSSProperties = { display: 'flex', flexDirection: 'column', gap: 6 };
const labelStyle: React.CSSProperties = {
    fontSize: 12, fontWeight: 600, color: 'var(--text-secondary)',
    fontFamily: 'var(--font-sans)', textTransform: 'uppercase', letterSpacing: '0.08em',
};
const textareaStyle: React.CSSProperties = {
    width: '100%', padding: '10px 12px',
    background: 'var(--input-bg)', border: '1px solid var(--input-border)',
    borderRadius: 'var(--input-radius)', color: 'var(--text-primary)',
    fontSize: 14, fontFamily: 'var(--font-sans)', outline: 'none',
    resize: 'vertical', minHeight: 80, boxSizing: 'border-box',
};
const selectStyle: React.CSSProperties = {
    width: '100%', height: 44, padding: '0 36px 0 12px',
    background: 'var(--input-bg)', border: '1px solid var(--input-border)',
    borderRadius: 'var(--input-radius)', color: 'var(--text-primary)',
    fontSize: 14, fontFamily: 'var(--font-sans)', outline: 'none',
    cursor: 'pointer', appearance: 'none',
    backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7a90' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E")`,
    backgroundRepeat: 'no-repeat', backgroundPosition: 'right 12px center', boxSizing: 'border-box',
};

interface MultiSelectChipsProps {
    label: string;
    icon: React.ReactNode;
    options: SelectOption[];
    selected: number[];
    onChange: (ids: number[]) => void;
}

function MultiSelectChips({ label, icon, options, selected, onChange }: MultiSelectChipsProps): React.JSX.Element {
    function toggle(id: number): void {
        onChange(selected.includes(id) ? selected.filter((s) => s !== id) : [...selected, id]);
    }

    return (
        <div style={fieldStyle}>
            <label style={{ ...labelStyle, display: 'flex', alignItems: 'center', gap: 6 }}>
                {icon} {label}
            </label>
            <div style={{ display: 'flex', flexWrap: 'wrap', gap: 8 }}>
                {options.map((opt) => {
                    const isSelected = selected.includes(opt.id);
                    return (
                        <button
                            key={opt.id}
                            type="button"
                            onClick={() => toggle(opt.id)}
                            style={{
                                padding: '5px 12px',
                                borderRadius: 999,
                                border: `1px solid ${isSelected ? 'var(--accent-primary)' : 'var(--border-default)'}`,
                                background: isSelected
                                    ? 'color-mix(in srgb, var(--accent-primary) 15%, var(--bg-card))'
                                    : 'var(--bg-card)',
                                color: isSelected ? 'var(--accent-primary)' : 'var(--text-secondary)',
                                fontSize: 12, fontFamily: 'var(--font-sans)', cursor: 'pointer',
                                fontWeight: isSelected ? 600 : 400,
                                transition: 'all 0.15s ease',
                            }}
                        >
                            {opt.name}
                        </button>
                    );
                })}
                {options.length === 0 && (
                    <span style={{ fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                        No options available
                    </span>
                )}
            </div>
        </div>
    );
}

interface Step5AdditionalProps {
    onValidChange: (valid: boolean) => void;
}

export function Step5Additional({ onValidChange }: Step5AdditionalProps): React.JSX.Element {
    const { form, updateForm } = useClaimWizardStore();
    const { data: causesOfLoss = [] } = useCausesOfLoss();
    const { data: serviceRequests = [] } = useServiceRequests();
    const { data: users = [] } = useUsers();

    React.useEffect(() => { onValidChange(true); }, [onValidChange]);

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
            <div>
                <h3 style={{ fontSize: 18, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', margin: 0 }}>
                    Additional Details
                </h3>
                <p style={{ fontSize: 13, color: 'var(--text-muted)', margin: '4px 0 0', fontFamily: 'var(--font-sans)' }}>
                    Add damage description, scope of work, and related services.
                </p>
            </div>

            {/* Referred By */}
            <div style={fieldStyle}>
                <label htmlFor="user_id_ref_by" style={{ ...labelStyle, display: 'flex', alignItems: 'center', gap: 6 }}>
                    <User size={12} /> Referred By
                </label>
                <select
                    id="user_id_ref_by"
                    value={form.user_id_ref_by ?? ''}
                    onChange={(e) => updateForm({ user_id_ref_by: e.target.value ? Number(e.target.value) : null })}
                    style={selectStyle}
                >
                    <option value="">Select user...</option>
                    {users.map((u) => (
                        <option key={u.id} value={u.id}>{u.name}</option>
                    ))}
                </select>
            </div>

            {/* Damage Description */}
            <div style={fieldStyle}>
                <label htmlFor="damage_description" style={{ ...labelStyle, display: 'flex', alignItems: 'center', gap: 6 }}>
                    <AlignLeft size={12} /> Damage Description
                </label>
                <textarea
                    id="damage_description"
                    value={form.damage_description}
                    onChange={(e) => updateForm({ damage_description: e.target.value })}
                    placeholder="Describe the damage..."
                    style={textareaStyle}
                />
            </div>

            {/* Scope of Work */}
            <div style={fieldStyle}>
                <label htmlFor="scope_of_work" style={{ ...labelStyle, display: 'flex', alignItems: 'center', gap: 6 }}>
                    <Wrench size={12} /> Scope of Work
                </label>
                <textarea
                    id="scope_of_work"
                    value={form.scope_of_work}
                    onChange={(e) => updateForm({ scope_of_work: e.target.value })}
                    placeholder="Describe the work to be done..."
                    style={textareaStyle}
                />
            </div>

            {/* Causes of Loss */}
            <MultiSelectChips
                label="Causes of Loss"
                icon={<AlignLeft size={12} />}
                options={causesOfLoss}
                selected={form.cause_of_loss_ids}
                onChange={(ids) => updateForm({ cause_of_loss_ids: ids })}
            />

            {/* Service Requests */}
            <MultiSelectChips
                label="Service Requests"
                icon={<Wrench size={12} />}
                options={serviceRequests}
                selected={form.service_request_ids}
                onChange={(ids) => updateForm({ service_request_ids: ids })}
            />

            {/* Customer Reviewed */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                <button
                    type="button"
                    role="checkbox"
                    aria-checked={form.customer_reviewed}
                    onClick={() => updateForm({ customer_reviewed: !form.customer_reviewed })}
                    style={{
                        width: 20, height: 20, borderRadius: 4, flexShrink: 0,
                        border: `2px solid ${form.customer_reviewed ? 'var(--accent-primary)' : 'var(--border-default)'}`,
                        background: form.customer_reviewed
                            ? 'color-mix(in srgb, var(--accent-primary) 20%, var(--bg-card))'
                            : 'var(--bg-card)',
                        cursor: 'pointer', display: 'flex', alignItems: 'center', justifyContent: 'center',
                        transition: 'all 0.15s ease',
                    }}
                >
                    {form.customer_reviewed && (
                        <CheckSquare size={12} style={{ color: 'var(--accent-primary)' }} />
                    )}
                </button>
                <span style={{ fontSize: 13, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                    Customer has reviewed the claim details
                </span>
            </div>
        </div>
    );
}
