import * as React from 'react';
import { Building2, Plus, X, ChevronDown, Loader2 } from 'lucide-react';
import { useClaimWizardStore } from '@/modules/claims/stores/claimWizardStore';
import { useInsuranceCompanies } from '@/modules/insurance-companies/hooks/useInsuranceCompanies';
import { usePublicCompanies } from '@/modules/public-companies/hooks/usePublicCompanies';
import { useAllianceCompanies } from '@/modules/alliance-companies/hooks/useAllianceCompanies';
import { useMortgageCompanies } from '@/modules/mortgage-companies/hooks/useMortgageCompanies';
import { CreateCompanyModal } from '../modals/CreateCompanyModal';
import type { CompanyType } from '../modals/CreateCompanyModal';

interface CompanyOption { id: number; name: string; }

interface CompanySelectProps {
    label: string;
    selectedId: number | null;
    selectedName: string;
    options: CompanyOption[];
    isPending: boolean;
    onSelect: (id: number, name: string) => void;
    onClear: () => void;
    onAdd: () => void;
    accentColor?: string;
}

function CompanySelect({
    label, selectedId, selectedName, options, isPending,
    onSelect, onClear, onAdd, accentColor = 'var(--accent-primary)',
}: CompanySelectProps): React.JSX.Element {
    const [open, setOpen] = React.useState(false);
    const [search, setSearch] = React.useState('');
    const ref = React.useRef<HTMLDivElement>(null);

    const filtered = options.filter((o) =>
        o.name.toLowerCase().includes(search.toLowerCase()),
    );

    React.useEffect(() => {
        function handleClickOutside(e: MouseEvent): void {
            if (ref.current && !ref.current.contains(e.target as Node)) {
                setOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    return (
        <div
            style={{
                background: 'var(--bg-card)',
                border: `1px solid ${selectedId ? `color-mix(in srgb, ${accentColor} 40%, transparent)` : 'var(--border-default)'}`,
                borderRadius: 'var(--radius-lg)',
                padding: 16,
                transition: 'border-color 0.2s ease',
            }}
        >
            {/* Label row */}
            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 10 }}>
                <Building2 size={14} style={{ color: accentColor }} />
                <span style={{
                    fontSize: 12, fontWeight: 700, color: accentColor,
                    textTransform: 'uppercase', letterSpacing: '0.1em', fontFamily: 'var(--font-sans)',
                }}>
                    {label}
                </span>
                <span style={{ fontSize: 10, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>(optional)</span>
            </div>

            {selectedId !== null ? (
                /* Selected state */
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', gap: 8 }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <div style={{
                            width: 32, height: 32, borderRadius: 'var(--radius-md)',
                            background: `color-mix(in srgb, ${accentColor} 15%, var(--bg-elevated))`,
                            display: 'flex', alignItems: 'center', justifyContent: 'center', color: accentColor, flexShrink: 0,
                        }}>
                            <Building2 size={14} />
                        </div>
                        <div>
                            <p style={{ margin: 0, fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                                {selectedName}
                            </p>
                            <p style={{ margin: 0, fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>ID: {selectedId}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={onClear}
                        aria-label={`Remove ${label}`}
                        style={{
                            width: 28, height: 28, borderRadius: 'var(--radius-sm)',
                            border: '1px solid var(--border-default)', background: 'transparent',
                            color: 'var(--accent-error)', cursor: 'pointer',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                        }}
                    >
                        <X size={14} />
                    </button>
                </div>
            ) : (
                /* Dropdown select state */
                <div ref={ref} style={{ position: 'relative' }}>
                    <button
                        type="button"
                        onClick={() => setOpen((v) => !v)}
                        style={{
                            width: '100%', height: 40, padding: '0 12px',
                            background: 'var(--input-bg)', border: '1px solid var(--input-border)',
                            borderRadius: 'var(--input-radius)', color: 'var(--text-muted)',
                            fontSize: 13, fontFamily: 'var(--font-sans)', cursor: 'pointer',
                            display: 'flex', alignItems: 'center', justifyContent: 'space-between',
                            transition: 'border-color 0.2s ease',
                        }}
                    >
                        <span>Select {label.toLowerCase()}...</span>
                        {isPending ? <Loader2 size={14} className="animate-spin" /> : <ChevronDown size={14} />}
                    </button>

                    {open && (
                        <div style={{
                            position: 'absolute', top: 'calc(100% + 4px)', left: 0, right: 0, zIndex: 70,
                            background: 'var(--bg-elevated)', border: '1px solid var(--border-default)',
                            borderRadius: 'var(--radius-md)', boxShadow: '0 8px 24px rgba(0,0,0,0.3)',
                            overflow: 'hidden',
                        }}>
                            {/* Search */}
                            <div style={{ padding: '8px 10px', borderBottom: '1px solid var(--border-subtle)' }}>
                                <input
                                    type="text"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder="Search..."
                                    autoFocus
                                    style={{
                                        width: '100%', height: 32, padding: '0 10px',
                                        background: 'var(--bg-card)', border: '1px solid var(--border-default)',
                                        borderRadius: 'var(--radius-sm)', color: 'var(--text-primary)',
                                        fontSize: 12, fontFamily: 'var(--font-sans)', outline: 'none',
                                        boxSizing: 'border-box',
                                    }}
                                />
                            </div>

                            {/* Options list */}
                            <div style={{ maxHeight: 180, overflowY: 'auto' }}>
                                {filtered.length === 0 ? (
                                    <p style={{ margin: 0, padding: '12px 14px', fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                        No results found.
                                    </p>
                                ) : (
                                    filtered.map((opt) => (
                                        <button
                                            key={opt.id}
                                            type="button"
                                            onClick={() => { onSelect(opt.id, opt.name); setOpen(false); setSearch(''); }}
                                            style={{
                                                width: '100%', padding: '9px 14px', display: 'flex', alignItems: 'center', gap: 10,
                                                background: 'transparent', border: 'none', borderBottom: '1px solid var(--border-subtle)',
                                                cursor: 'pointer', textAlign: 'left', color: 'var(--text-primary)',
                                                fontFamily: 'var(--font-sans)', fontSize: 13,
                                            }}
                                        >
                                            <Building2 size={12} style={{ color: accentColor, flexShrink: 0 }} />
                                            {opt.name}
                                        </button>
                                    ))
                                )}
                            </div>

                            {/* Add new — triggers modal */}
                            <div style={{ padding: 8, borderTop: '1px solid var(--border-subtle)' }}>
                                <button
                                    type="button"
                                    onClick={() => { setOpen(false); setSearch(''); onAdd(); }}
                                    style={{
                                        width: '100%', padding: '8px 12px',
                                        background: `color-mix(in srgb, ${accentColor} 12%, var(--bg-card))`,
                                        border: `1px dashed color-mix(in srgb, ${accentColor} 40%, transparent)`,
                                        borderRadius: 'var(--radius-md)', color: accentColor,
                                        fontSize: 12, fontFamily: 'var(--font-sans)', cursor: 'pointer',
                                        display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6, fontWeight: 700,
                                    }}
                                >
                                    <Plus size={14} /> Add new {label.toLowerCase()}
                                </button>
                            </div>
                        </div>
                    )}

                    {/* "Add new" shortcut visible when dropdown is closed */}
                    {!open && (
                        <div style={{ marginTop: 6, display: 'flex', justifyContent: 'flex-end' }}>
                            <button
                                type="button"
                                onClick={onAdd}
                                style={{
                                    display: 'flex', alignItems: 'center', gap: 5,
                                    padding: '3px 9px',
                                    background: 'transparent',
                                    border: `1px dashed color-mix(in srgb, ${accentColor} 35%, transparent)`,
                                    borderRadius: 'var(--radius-sm)',
                                    color: accentColor,
                                    fontSize: 11, fontFamily: 'var(--font-sans)', cursor: 'pointer', fontWeight: 600,
                                }}
                            >
                                <Plus size={11} /> New
                            </button>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
}

interface Step4CompaniesProps {
    onValidChange: (valid: boolean) => void;
}

export function Step4Companies({ onValidChange }: Step4CompaniesProps): React.JSX.Element {
    const { form, updateForm } = useClaimWizardStore();

    const { data: insuranceData, isPending: insLoading } = useInsuranceCompanies({ per_page: 200, status: 'active' });
    const { data: publicData,    isPending: pubLoading }  = usePublicCompanies({ per_page: 200, status: 'active' });
    const { data: allianceData,  isPending: allLoading }  = useAllianceCompanies({ per_page: 200, status: 'active' });
    const { data: mortgageData,  isPending: morLoading }  = useMortgageCompanies({ page: 1, per_page: 200, status: 'active' });

    const insuranceOptions: CompanyOption[] = (insuranceData?.data ?? []).map((c) => ({
        id: c.company_id,
        name: c.insurance_company_name,
    }));
    const publicOptions: CompanyOption[] = (publicData?.data ?? []).map((c) => ({
        id: c.company_id,
        name: c.public_company_name,
    }));
    const allianceOptions: CompanyOption[] = (allianceData?.data ?? []).map((c) => ({
        id: c.company_id,
        name: c.alliance_company_name,
    }));
    const mortgageOptions: CompanyOption[] = (mortgageData?.data ?? []).map((c) => ({
        id: c.company_id,
        name: c.mortgage_company_name,
    }));

    /* Modal state */
    const [modalOpen, setModalOpen] = React.useState(false);
    const [modalType, setModalType] = React.useState<CompanyType>('insurance');

    React.useEffect(() => { onValidChange(true); }, [onValidChange]);

    function openModal(type: CompanyType): void {
        setModalType(type);
        setModalOpen(true);
    }

    function handleCompanyCreated(id: number, name: string): void {
        switch (modalType) {
            case 'insurance':
                updateForm({ insurance_company_id: id, insurance_company_name: name });
                break;
            case 'public':
                updateForm({ public_company_id: id, public_company_name: name });
                break;
            case 'alliance':
                updateForm({ alliance_company_id: id, alliance_company_name: name });
                break;
            case 'mortgage':
                updateForm({ mortgage_company_id: id, mortgage_company_name: name });
                break;
        }
    }

    return (
        <>
            <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
                <div>
                    <h3 style={{ fontSize: 18, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', margin: 0 }}>
                        Company Assignments
                    </h3>
                    <p style={{ fontSize: 13, color: 'var(--text-muted)', margin: '4px 0 0', fontFamily: 'var(--font-sans)' }}>
                        Assign companies to this claim. All fields are optional. Use "New" to create one inline.
                    </p>
                </div>

                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 14 }}>
                    <CompanySelect
                        label="Insurance Company"
                        selectedId={form.insurance_company_id}
                        selectedName={form.insurance_company_name}
                        options={insuranceOptions}
                        isPending={insLoading}
                        onSelect={(id, name) => updateForm({ insurance_company_id: id, insurance_company_name: name })}
                        onClear={() => updateForm({ insurance_company_id: null, insurance_company_name: '' })}
                        onAdd={() => openModal('insurance')}
                        accentColor="var(--accent-primary)"
                    />

                    <CompanySelect
                        label="Public Company"
                        selectedId={form.public_company_id}
                        selectedName={form.public_company_name}
                        options={publicOptions}
                        isPending={pubLoading}
                        onSelect={(id, name) => updateForm({ public_company_id: id, public_company_name: name })}
                        onClear={() => updateForm({ public_company_id: null, public_company_name: '' })}
                        onAdd={() => openModal('public')}
                        accentColor="var(--accent-secondary)"
                    />

                    <CompanySelect
                        label="Alliance Company"
                        selectedId={form.alliance_company_id}
                        selectedName={form.alliance_company_name}
                        options={allianceOptions}
                        isPending={allLoading}
                        onSelect={(id, name) => updateForm({ alliance_company_id: id, alliance_company_name: name })}
                        onClear={() => updateForm({ alliance_company_id: null, alliance_company_name: '' })}
                        onAdd={() => openModal('alliance')}
                        accentColor="var(--accent-success)"
                    />

                    <CompanySelect
                        label="Mortgage Company"
                        selectedId={form.mortgage_company_id}
                        selectedName={form.mortgage_company_name}
                        options={mortgageOptions}
                        isPending={morLoading}
                        onSelect={(id, name) => updateForm({ mortgage_company_id: id, mortgage_company_name: name })}
                        onClear={() => updateForm({ mortgage_company_id: null, mortgage_company_name: '' })}
                        onAdd={() => openModal('mortgage')}
                        accentColor="var(--accent-warning)"
                    />
                </div>

                <div style={{
                    padding: '10px 14px',
                    background: 'color-mix(in srgb, var(--accent-info) 8%, var(--bg-card))',
                    border: '1px solid color-mix(in srgb, var(--accent-info) 25%, transparent)',
                    borderRadius: 'var(--radius-md)',
                    fontSize: 12, color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)',
                }}>
                    Company assignments can be updated later from the claim detail page.
                </div>
            </div>

            <CreateCompanyModal
                open={modalOpen}
                type={modalType}
                onClose={() => setModalOpen(false)}
                onCreated={handleCompanyCreated}
            />
        </>
    );
}
