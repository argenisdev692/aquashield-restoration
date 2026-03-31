import * as React from 'react';
import { FileText, Hash, Calendar, AlignLeft } from 'lucide-react';
import { useClaimWizardStore } from '@/modules/claims/stores/claimWizardStore';
import { useQuery } from '@tanstack/react-query';
import axios from 'axios';

interface SelectOption { id: number; name: string; }

function useTypeDamages() {
    return useQuery<SelectOption[], Error>({
        queryKey: ['type-damages', 'select'],
        queryFn: async () => {
            const { data } = await axios.get<{ data: { id: number; type_damage_name: string }[] }>(
                '/type-damages/data/admin',
                { params: { per_page: 100 } },
            );
            return data.data.map((d) => ({ id: d.id, name: d.type_damage_name }));
        },
        staleTime: 1000 * 60 * 10,
    });
}

function useClaimStatuses() {
    return useQuery<SelectOption[], Error>({
        queryKey: ['claim-statuses', 'select'],
        queryFn: async () => {
            const { data } = await axios.get<{ data: { id: number; claim_status_name: string }[] }>(
                '/claim-statuses/data/admin',
                { params: { per_page: 100 } },
            );
            return data.data.map((d) => ({ id: d.id, name: d.claim_status_name }));
        },
        staleTime: 1000 * 60 * 10,
    });
}

const fieldStyle: React.CSSProperties = {
    display: 'flex',
    flexDirection: 'column',
    gap: 6,
};

const labelStyle: React.CSSProperties = {
    fontSize: 12,
    fontWeight: 600,
    color: 'var(--text-secondary)',
    fontFamily: 'var(--font-sans)',
    textTransform: 'uppercase',
    letterSpacing: '0.08em',
};

const inputStyle: React.CSSProperties = {
    width: '100%',
    height: 44,
    padding: '0 12px',
    background: 'var(--input-bg)',
    border: '1px solid var(--input-border)',
    borderRadius: 'var(--input-radius)',
    color: 'var(--text-primary)',
    fontSize: 14,
    fontFamily: 'var(--font-sans)',
    outline: 'none',
    transition: 'border-color 0.2s ease',
    boxSizing: 'border-box',
};

const selectStyle: React.CSSProperties = {
    ...inputStyle,
    cursor: 'pointer',
    backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%237a7a90' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E")`,
    backgroundRepeat: 'no-repeat',
    backgroundPosition: 'right 12px center',
    paddingRight: 36,
    appearance: 'none',
};

const textareaStyle: React.CSSProperties = {
    width: '100%',
    padding: '10px 12px',
    background: 'var(--input-bg)',
    border: '1px solid var(--input-border)',
    borderRadius: 'var(--input-radius)',
    color: 'var(--text-primary)',
    fontSize: 14,
    fontFamily: 'var(--font-sans)',
    outline: 'none',
    resize: 'vertical',
    minHeight: 80,
    transition: 'border-color 0.2s ease',
    boxSizing: 'border-box',
};

interface Step3ClaimInfoProps {
    onValidChange: (valid: boolean) => void;
}

export function Step3ClaimInfo({ onValidChange }: Step3ClaimInfoProps): React.JSX.Element {
    const { form, updateForm } = useClaimWizardStore();
    const { data: typeDamages = [] } = useTypeDamages();
    const { data: claimStatuses = [] } = useClaimStatuses();

    const isValid =
        form.policy_number.trim().length > 0 &&
        form.type_damage_id !== null &&
        form.claim_status !== null;

    React.useEffect(() => { onValidChange(isValid); }, [isValid, onValidChange]);

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
            <div>
                <h3 style={{ fontSize: 18, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', margin: 0 }}>
                    Claim Information
                </h3>
                <p style={{ fontSize: 13, color: 'var(--text-muted)', margin: '4px 0 0', fontFamily: 'var(--font-sans)' }}>
                    Enter the policy details and claim dates.
                </p>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                {/* Policy Number */}
                <div style={fieldStyle}>
                    <label htmlFor="policy_number" style={labelStyle}>
                        <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <Hash size={12} /> Policy Number <span style={{ color: 'var(--accent-error)', marginLeft: 2 }}>*</span>
                        </span>
                    </label>
                    <input
                        id="policy_number"
                        type="text"
                        value={form.policy_number}
                        onChange={(e) => updateForm({ policy_number: e.target.value })}
                        placeholder="e.g. POL-2024-001"
                        style={{
                            ...inputStyle,
                            borderColor: form.policy_number.trim() ? 'var(--input-border)' : form.policy_number === '' ? 'var(--input-border)' : 'var(--accent-error)',
                        }}
                    />
                </div>

                {/* Claim Number */}
                <div style={fieldStyle}>
                    <label htmlFor="claim_number" style={labelStyle}>
                        <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <Hash size={12} /> Claim Number
                        </span>
                    </label>
                    <input
                        id="claim_number"
                        type="text"
                        value={form.claim_number}
                        onChange={(e) => updateForm({ claim_number: e.target.value })}
                        placeholder="e.g. CLM-2024-001"
                        style={inputStyle}
                    />
                </div>

                {/* Type of Damage */}
                <div style={fieldStyle}>
                    <label htmlFor="type_damage_id" style={labelStyle}>
                        <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <FileText size={12} /> Type of Damage <span style={{ color: 'var(--accent-error)', marginLeft: 2 }}>*</span>
                        </span>
                    </label>
                    <select
                        id="type_damage_id"
                        value={form.type_damage_id ?? ''}
                        onChange={(e) => updateForm({ type_damage_id: e.target.value ? Number(e.target.value) : null })}
                        style={selectStyle}
                    >
                        <option value="">Select damage type...</option>
                        {typeDamages.map((t) => (
                            <option key={t.id} value={t.id}>{t.name}</option>
                        ))}
                    </select>
                </div>

                {/* Claim Status */}
                <div style={fieldStyle}>
                    <label htmlFor="claim_status" style={labelStyle}>
                        <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <FileText size={12} /> Claim Status <span style={{ color: 'var(--accent-error)', marginLeft: 2 }}>*</span>
                        </span>
                    </label>
                    <select
                        id="claim_status"
                        value={form.claim_status ?? ''}
                        onChange={(e) => updateForm({ claim_status: e.target.value ? Number(e.target.value) : null })}
                        style={selectStyle}
                    >
                        <option value="">Select status...</option>
                        {claimStatuses.map((s) => (
                            <option key={s.id} value={s.id}>{s.name}</option>
                        ))}
                    </select>
                </div>

                {/* Date of Loss */}
                <div style={fieldStyle}>
                    <label htmlFor="date_of_loss" style={labelStyle}>
                        <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <Calendar size={12} /> Date of Loss
                        </span>
                    </label>
                    <input
                        id="date_of_loss"
                        type="date"
                        value={form.date_of_loss}
                        onChange={(e) => updateForm({ date_of_loss: e.target.value })}
                        style={inputStyle}
                    />
                </div>

                {/* Claim Date */}
                <div style={fieldStyle}>
                    <label htmlFor="claim_date" style={labelStyle}>
                        <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <Calendar size={12} /> Claim Date
                        </span>
                    </label>
                    <input
                        id="claim_date"
                        type="date"
                        value={form.claim_date}
                        onChange={(e) => updateForm({ claim_date: e.target.value })}
                        style={inputStyle}
                    />
                </div>

                {/* Work Date */}
                <div style={fieldStyle}>
                    <label htmlFor="work_date" style={labelStyle}>
                        <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                            <Calendar size={12} /> Work Date
                        </span>
                    </label>
                    <input
                        id="work_date"
                        type="date"
                        value={form.work_date}
                        onChange={(e) => updateForm({ work_date: e.target.value })}
                        style={inputStyle}
                    />
                </div>

                {/* Number of Floors */}
                <div style={fieldStyle}>
                    <label htmlFor="number_of_floors" style={labelStyle}>Number of Floors</label>
                    <input
                        id="number_of_floors"
                        type="number"
                        min={1}
                        max={99}
                        value={form.number_of_floors}
                        onChange={(e) => updateForm({ number_of_floors: e.target.value })}
                        placeholder="e.g. 2"
                        style={inputStyle}
                    />
                </div>
            </div>

            {/* Description of Loss */}
            <div style={fieldStyle}>
                <label htmlFor="description_of_loss" style={labelStyle}>
                    <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <AlignLeft size={12} /> Description of Loss
                    </span>
                </label>
                <textarea
                    id="description_of_loss"
                    value={form.description_of_loss}
                    onChange={(e) => updateForm({ description_of_loss: e.target.value })}
                    placeholder="Describe the loss event..."
                    style={textareaStyle}
                />
            </div>
        </div>
    );
}
