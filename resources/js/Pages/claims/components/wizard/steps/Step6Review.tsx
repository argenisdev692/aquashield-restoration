import * as React from 'react';
import { MapPin, Users, FileText, Building2, Layers, Edit2 } from 'lucide-react';
import { useClaimWizardStore } from '@/modules/claims/stores/claimWizardStore';
import type { WizardStep } from '@/modules/claims/stores/claimWizardStore';

interface ReviewSectionProps {
    title: string;
    icon: React.ReactNode;
    step: WizardStep;
    onEdit: (step: WizardStep) => void;
    children: React.ReactNode;
}

function ReviewSection({ title, icon, step, onEdit, children }: ReviewSectionProps): React.JSX.Element {
    return (
        <div
            style={{
                background: 'var(--bg-card)',
                border: '1px solid var(--border-default)',
                borderRadius: 'var(--radius-lg)',
                overflow: 'hidden',
            }}
        >
            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'space-between',
                    padding: '12px 16px',
                    borderBottom: '1px solid var(--border-subtle)',
                    background: 'var(--bg-elevated)',
                }}
            >
                <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                    <span style={{ color: 'var(--accent-primary)' }}>{icon}</span>
                    <span style={{ fontSize: 13, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', textTransform: 'uppercase', letterSpacing: '0.08em' }}>
                        {title}
                    </span>
                </div>
                <button
                    type="button"
                    onClick={() => onEdit(step)}
                    aria-label={`Edit ${title}`}
                    style={{
                        display: 'flex', alignItems: 'center', gap: 6,
                        padding: '4px 10px', borderRadius: 'var(--radius-sm)',
                        border: '1px solid var(--border-default)',
                        background: 'transparent', color: 'var(--accent-primary)',
                        fontSize: 11, fontFamily: 'var(--font-sans)', cursor: 'pointer',
                        fontWeight: 600, transition: 'all 0.15s ease',
                    }}
                >
                    <Edit2 size={11} /> Edit
                </button>
            </div>
            <div style={{ padding: '14px 16px' }}>
                {children}
            </div>
        </div>
    );
}

function ReviewRow({ label, value }: { label: string; value: React.ReactNode }): React.JSX.Element {
    return (
        <div style={{ display: 'flex', gap: 8, marginBottom: 8, fontSize: 13, fontFamily: 'var(--font-sans)' }}>
            <span style={{ color: 'var(--text-muted)', minWidth: 140, flexShrink: 0 }}>{label}</span>
            <span style={{ color: 'var(--text-primary)', fontWeight: 500, wordBreak: 'break-word' }}>
                {value ?? <span style={{ color: 'var(--text-disabled)' }}>—</span>}
            </span>
        </div>
    );
}

interface Step6ReviewProps {
    onValidChange: (valid: boolean) => void;
    onEditStep: (step: WizardStep) => void;
    isSubmitting: boolean;
}

export function Step6Review({ onValidChange, onEditStep, isSubmitting }: Step6ReviewProps): React.JSX.Element {
    const { form } = useClaimWizardStore();

    React.useEffect(() => { onValidChange(true); }, [onValidChange]);

    const ownerSlot = form.customer_slots.find((s) => s.role === 'owner');
    const coOwnerSlot = form.customer_slots.find((s) => s.role === 'co_owner');
    const extraSlot = form.customer_slots.find((s) => s.role === 'extra');

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>
            <div>
                <h3 style={{ fontSize: 18, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', margin: 0 }}>
                    Review & Submit
                </h3>
                <p style={{ fontSize: 13, color: 'var(--text-muted)', margin: '4px 0 0', fontFamily: 'var(--font-sans)' }}>
                    Review all information before submitting. Click Edit on any section to make changes.
                </p>
            </div>

            {/* Step 1 — Property */}
            <ReviewSection title="Property" icon={<MapPin size={14} />} step={1} onEdit={onEditStep}>
                <ReviewRow label="Address" value={form.property_address || null} />
                <ReviewRow label="Property ID" value={form.property_id?.toString() ?? null} />
            </ReviewSection>

            {/* Step 2 — Customers */}
            <ReviewSection title="Customers" icon={<Users size={14} />} step={2} onEdit={onEditStep}>
                <ReviewRow label="Owner" value={ownerSlot?.customer_id ? ownerSlot.customer_label : null} />
                <ReviewRow label="Co-Owner" value={coOwnerSlot?.customer_id ? coOwnerSlot.customer_label : null} />
                <ReviewRow label="Extra" value={extraSlot?.customer_id ? extraSlot.customer_label : null} />
            </ReviewSection>

            {/* Step 3 — Claim Info */}
            <ReviewSection title="Claim Information" icon={<FileText size={14} />} step={3} onEdit={onEditStep}>
                <ReviewRow label="Policy Number" value={form.policy_number || null} />
                <ReviewRow label="Claim Number" value={form.claim_number || null} />
                <ReviewRow label="Date of Loss" value={form.date_of_loss || null} />
                <ReviewRow label="Claim Date" value={form.claim_date || null} />
                <ReviewRow label="Work Date" value={form.work_date || null} />
                <ReviewRow label="Number of Floors" value={form.number_of_floors || null} />
                <ReviewRow label="Type Damage ID" value={form.type_damage_id?.toString() ?? null} />
                <ReviewRow label="Claim Status ID" value={form.claim_status?.toString() ?? null} />
                {form.description_of_loss && (
                    <ReviewRow label="Description" value={form.description_of_loss} />
                )}
            </ReviewSection>

            {/* Step 4 — Companies */}
            <ReviewSection title="Companies" icon={<Building2 size={14} />} step={4} onEdit={onEditStep}>
                <ReviewRow label="Insurance Co." value={form.insurance_company_name || null} />
                <ReviewRow label="Public Co." value={form.public_company_name || null} />
                <ReviewRow label="Alliance Co." value={form.alliance_company_name || null} />
                <ReviewRow label="Mortgage Co." value={form.mortgage_company_name || null} />
                {!form.insurance_company_id && !form.public_company_id && !form.alliance_company_id && (
                    <p style={{ margin: '4px 0 0', fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                        No companies assigned — can be updated later from the claim detail page.
                    </p>
                )}
            </ReviewSection>

            {/* Step 5 — Additional */}
            <ReviewSection title="Additional Details" icon={<Layers size={14} />} step={5} onEdit={onEditStep}>
                <ReviewRow label="Referred By ID" value={form.user_id_ref_by?.toString() ?? null} />
                <ReviewRow
                    label="Causes of Loss"
                    value={form.cause_of_loss_ids.length > 0 ? `${form.cause_of_loss_ids.length} selected` : null}
                />
                <ReviewRow
                    label="Service Requests"
                    value={form.service_request_ids.length > 0 ? `${form.service_request_ids.length} selected` : null}
                />
                <ReviewRow
                    label="Customer Reviewed"
                    value={form.customer_reviewed ? 'Yes' : 'No'}
                />
                {form.damage_description && <ReviewRow label="Damage Desc." value={form.damage_description} />}
                {form.scope_of_work && <ReviewRow label="Scope of Work" value={form.scope_of_work} />}
            </ReviewSection>

            {/* Submit notice */}
            <div
                style={{
                    padding: '12px 16px',
                    background: 'color-mix(in srgb, var(--accent-primary) 8%, var(--bg-card))',
                    border: '1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)',
                    borderRadius: 'var(--radius-md)',
                    fontSize: 13,
                    color: 'var(--text-primary)',
                    fontFamily: 'var(--font-sans)',
                }}
            >
                {isSubmitting
                    ? 'Submitting claim, please wait...'
                    : 'Click "Submit Claim" below to create the claim record.'}
            </div>
        </div>
    );
}
