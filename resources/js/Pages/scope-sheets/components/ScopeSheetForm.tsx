import * as React from 'react';
import { motion } from 'framer-motion';
import { FileText, Images, LayoutGrid, Info, Loader2 } from 'lucide-react';
import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import { ScopeSheetPresentationsSection } from './form/ScopeSheetPresentationsSection';
import { ScopeSheetZonesSection } from './form/ScopeSheetZonesSection';
import type { ScopeSheetFormData, ScopeSheetPresentation, ScopeSheetZone } from '@/modules/scope-sheets/types';

// ── Claim search ───────────────────────────────────────────────────────────────

interface ClaimOption {
    id: number;
    uuid: string;
    claim_number: string | null;
    claim_internal_id: string;
    property_address: string | null;
}
interface PaginatedClaims { data: ClaimOption[] }

function useClaimSearch(search: string) {
    return useQuery<PaginatedClaims, Error>({
        queryKey: ['claims', 'search', search],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedClaims>('/claims/data/admin', {
                params: { search: search || undefined, per_page: 20, status: 'active' },
            });
            return data;
        },
        staleTime: 1000 * 30,
        enabled: true,
    });
}

// ── Section Nav ────────────────────────────────────────────────────────────────

const SECTIONS = [
    { id: 'info',          label: 'Claim Info',    icon: <Info size={15} /> },
    { id: 'presentations', label: 'Presentations', icon: <Images size={15} /> },
    { id: 'zones',         label: 'Zones',         icon: <LayoutGrid size={15} /> },
] as const;

type SectionId = typeof SECTIONS[number]['id'];

// ── Form ───────────────────────────────────────────────────────────────────────

interface Props {
    data: ScopeSheetFormData;
    onChange: (data: ScopeSheetFormData) => void;
    onSubmit: () => void;
    isSubmitting: boolean;
    submitLabel?: string;
    lockedClaimId?: number | null;
}

export function ScopeSheetForm({
    data,
    onChange,
    onSubmit,
    isSubmitting,
    submitLabel = 'Create Scope Sheet',
    lockedClaimId,
}: Props): React.JSX.Element {
    const [activeSection, setActiveSection] = React.useState<SectionId>('info');
    const [claimSearch, setClaimSearch] = React.useState('');
    const [claimDropOpen, setClaimDropOpen] = React.useState(false);
    const claimDropRef = React.useRef<HTMLDivElement | null>(null);
    const sectionRefs = React.useRef<Record<string, HTMLElement | null>>({});

    const { data: claimData, isPending: claimsLoading } = useClaimSearch(claimSearch);
    const claimOptions = claimData?.data ?? [];

    /* ── Scroll-spy: update active section on scroll ── */
    React.useEffect(() => {
        const observer = new IntersectionObserver(
            (entries) => {
                for (const entry of entries) {
                    if (entry.isIntersecting) {
                        setActiveSection(entry.target.id as SectionId);
                    }
                }
            },
            { threshold: 0.4 },
        );
        for (const sec of SECTIONS) {
            const el = sectionRefs.current[sec.id];
            if (el) observer.observe(el);
        }
        return () => observer.disconnect();
    }, []);

    /* ── Close claim dropdown on outside click ── */
    React.useEffect(() => {
        if (!claimDropOpen) return;
        function handle(e: MouseEvent): void {
            if (claimDropRef.current && !claimDropRef.current.contains(e.target as Node)) {
                setClaimDropOpen(false);
            }
        }
        document.addEventListener('mousedown', handle);
        return () => document.removeEventListener('mousedown', handle);
    }, [claimDropOpen]);

    function scrollTo(id: SectionId): void {
        sectionRefs.current[id]?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setActiveSection(id);
    }

    function handleSelectClaim(c: ClaimOption): void {
        onChange({ ...data, claim_id: c.id });
        setClaimDropOpen(false);
        setClaimSearch('');
    }

    const selectedClaim = React.useMemo(
        () => claimOptions.find((c) => c.id === data.claim_id) ?? null,
        [claimOptions, data.claim_id],
    );

    function handleSubmit(e: React.FormEvent): void {
        e.preventDefault();
        onSubmit();
    }

    return (
        <form onSubmit={handleSubmit} style={{ display: 'flex', gap: 24, alignItems: 'flex-start', fontFamily: 'var(--font-sans)' }}>
            {/* ── Sticky side nav ── */}
            <aside
                style={{
                    width: 200,
                    flexShrink: 0,
                    position: 'sticky',
                    top: 24,
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 4,
                }}
            >
                <div
                    style={{
                        fontSize: 10,
                        fontWeight: 700,
                        color: 'var(--text-disabled)',
                        textTransform: 'uppercase',
                        letterSpacing: '1.8px',
                        marginBottom: 8,
                        paddingLeft: 4,
                    }}
                >
                    Sections
                </div>
                {SECTIONS.map((sec) => {
                    const isActive = activeSection === sec.id;
                    return (
                        <button
                            key={sec.id}
                            type="button"
                            onClick={() => scrollTo(sec.id)}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 8,
                                padding: '8px 12px',
                                borderRadius: 'var(--radius-md)',
                                border: 'none',
                                background: isActive
                                    ? 'color-mix(in srgb, var(--accent-primary) 14%, var(--bg-card))'
                                    : 'transparent',
                                color: isActive ? 'var(--accent-primary)' : 'var(--text-muted)',
                                fontSize: 13,
                                fontWeight: isActive ? 700 : 500,
                                fontFamily: 'var(--font-sans)',
                                cursor: 'pointer',
                                transition: 'all 0.2s ease',
                                textAlign: 'left',
                                borderLeft: isActive ? '3px solid var(--accent-primary)' : '3px solid transparent',
                            }}
                        >
                            {sec.icon}
                            {sec.label}
                        </button>
                    );
                })}

                {/* Submit button in sidebar */}
                <div style={{ marginTop: 24 }}>
                    <button
                        type="submit"
                        disabled={isSubmitting}
                        aria-label={submitLabel}
                        className="btn-modern btn-modern-primary"
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            gap: 8,
                            width: '100%',
                            padding: '10px 16px',
                            borderRadius: 'var(--radius-md)',
                            border: 'none',
                            background: isSubmitting ? 'var(--bg-hover)' : 'var(--accent-primary)',
                            color: isSubmitting ? 'var(--text-muted)' : 'var(--bg-base)',
                            fontSize: 13,
                            fontWeight: 700,
                            fontFamily: 'var(--font-sans)',
                            cursor: isSubmitting ? 'not-allowed' : 'pointer',
                            transition: 'all 0.2s ease',
                        }}
                    >
                        {isSubmitting ? (
                            <>
                                <Loader2 size={14} style={{ animation: 'spin 1s linear infinite' }} />
                                Saving…
                            </>
                        ) : (
                            <>
                                <FileText size={14} />
                                {submitLabel}
                            </>
                        )}
                    </button>
                </div>
            </aside>

            {/* ── Main content ── */}
            <div style={{ flex: 1, minWidth: 0, display: 'flex', flexDirection: 'column', gap: 32 }}>

                {/* ══ SECTION 1: Claim Info ══ */}
                <motion.section
                    id="info"
                    ref={(el) => { sectionRefs.current['info'] = el; }}
                    initial={{ opacity: 0, y: 16 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.3 }}
                    style={{
                        background: 'var(--bg-card)',
                        border: '1px solid var(--border-default)',
                        borderRadius: 'var(--radius-lg)',
                        overflow: 'hidden',
                    }}
                >
                    {/* Card header */}
                    <div
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            gap: 10,
                            padding: '14px 20px',
                            borderBottom: '1px solid var(--border-subtle)',
                            background: 'var(--bg-elevated)',
                        }}
                    >
                        <div
                            style={{
                                width: 32,
                                height: 32,
                                borderRadius: 'var(--radius-md)',
                                background: 'color-mix(in srgb, var(--accent-primary) 15%, var(--bg-card))',
                                border: '1px solid color-mix(in srgb, var(--accent-primary) 30%, transparent)',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                color: 'var(--accent-primary)',
                                fontSize: 14,
                                fontWeight: 800,
                            }}
                        >
                            1
                        </div>
                        <div>
                            <div style={{ fontSize: 14, fontWeight: 700, color: 'var(--text-primary)' }}>Claim Information</div>
                            <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>Link to a claim and describe the scope sheet</div>
                        </div>
                    </div>

                    <div style={{ padding: '20px', display: 'flex', flexDirection: 'column', gap: 18 }}>
                        {/* Claim selector */}
                        <div>
                            <label
                                htmlFor="claim-search"
                                style={labelStyle}
                            >
                                Claim <span style={{ color: 'var(--accent-error)' }}>*</span>
                            </label>
                            {lockedClaimId ? (
                                <div style={lockedFieldStyle}>
                                    Claim #{lockedClaimId} — linked from claim page
                                </div>
                            ) : (
                                <div style={{ position: 'relative' }} ref={claimDropRef}>
                                    <div
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: 8,
                                            background: 'var(--input-bg)',
                                            border: `1px solid ${claimDropOpen ? 'var(--accent-primary)' : 'var(--border-default)'}`,
                                            borderRadius: 'var(--radius-md)',
                                            padding: '0 12px',
                                            height: 'var(--input-height)',
                                            cursor: 'pointer',
                                            transition: 'border-color 0.2s ease',
                                        }}
                                        onClick={() => setClaimDropOpen((v) => !v)}
                                    >
                                        <input
                                            id="claim-search"
                                            type="text"
                                            value={claimDropOpen ? claimSearch : (selectedClaim ? `${selectedClaim.claim_number ?? selectedClaim.claim_internal_id} — ${selectedClaim.property_address ?? ''}` : '')}
                                            onChange={(e) => { setClaimSearch(e.target.value); setClaimDropOpen(true); }}
                                            onFocus={() => setClaimDropOpen(true)}
                                            placeholder="Search by claim number or address…"
                                            style={{
                                                flex: 1,
                                                background: 'transparent',
                                                border: 'none',
                                                outline: 'none',
                                                color: 'var(--text-primary)',
                                                fontSize: 13,
                                                fontFamily: 'var(--font-sans)',
                                                height: '100%',
                                            }}
                                        />
                                        {claimsLoading && <Loader2 size={14} style={{ color: 'var(--text-muted)', animation: 'spin 1s linear infinite', flexShrink: 0 }} />}
                                    </div>

                                    {claimDropOpen && (
                                        <div
                                            role="listbox"
                                            aria-label="Select claim"
                                            style={{
                                                position: 'absolute',
                                                top: 'calc(100% + 4px)',
                                                left: 0,
                                                right: 0,
                                                zIndex: 100,
                                                background: 'var(--bg-elevated)',
                                                border: '1px solid var(--border-default)',
                                                borderRadius: 'var(--radius-lg)',
                                                boxShadow: '0 8px 32px rgba(0,0,0,0.3)',
                                                maxHeight: 260,
                                                overflowY: 'auto',
                                            }}
                                        >
                                            {claimOptions.length === 0 && !claimsLoading && (
                                                <div style={{ padding: '16px', textAlign: 'center', fontSize: 13, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>No claims found</div>
                                            )}
                                            {claimOptions.map((c) => (
                                                <button
                                                    key={c.uuid}
                                                    type="button"
                                                    role="option"
                                                    aria-selected={data.claim_id === c.id}
                                                    onClick={() => handleSelectClaim(c)}
                                                    style={{
                                                        display: 'block',
                                                        width: '100%',
                                                        padding: '10px 14px',
                                                        textAlign: 'left',
                                                        background: data.claim_id === c.id ? 'color-mix(in srgb, var(--accent-primary) 12%, var(--bg-elevated))' : 'transparent',
                                                        border: 'none',
                                                        borderBottom: '1px solid var(--border-subtle)',
                                                        cursor: 'pointer',
                                                        transition: 'background 0.15s ease',
                                                    }}
                                                    onMouseEnter={(e) => { e.currentTarget.style.background = 'var(--bg-hover)'; }}
                                                    onMouseLeave={(e) => { e.currentTarget.style.background = data.claim_id === c.id ? 'color-mix(in srgb, var(--accent-primary) 12%, var(--bg-elevated))' : 'transparent'; }}
                                                >
                                                    <div style={{ fontSize: 13, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                                                        {c.claim_number ?? c.claim_internal_id}
                                                    </div>
                                                    {c.property_address && (
                                                        <div style={{ fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)', marginTop: 2 }}>
                                                            {c.property_address}
                                                        </div>
                                                    )}
                                                </button>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>

                        {/* Description */}
                        <div>
                            <label htmlFor="scope-description" style={labelStyle}>
                                Scope Sheet Description
                            </label>
                            <textarea
                                id="scope-description"
                                value={data.scope_sheet_description}
                                onChange={(e) => onChange({ ...data, scope_sheet_description: e.target.value })}
                                placeholder="Briefly describe the scope of this assessment…"
                                rows={4}
                                style={{
                                    width: '100%',
                                    resize: 'vertical',
                                    background: 'var(--input-bg)',
                                    border: '1px solid var(--border-default)',
                                    borderRadius: 'var(--radius-md)',
                                    color: 'var(--text-primary)',
                                    fontFamily: 'var(--font-sans)',
                                    fontSize: 13,
                                    padding: '10px 12px',
                                    outline: 'none',
                                    boxSizing: 'border-box',
                                    transition: 'border-color 0.2s ease',
                                }}
                                onFocus={(e) => { e.target.style.borderColor = 'var(--accent-primary)'; }}
                                onBlur={(e) => { e.target.style.borderColor = 'var(--border-default)'; }}
                            />
                        </div>
                    </div>
                </motion.section>

                {/* ══ SECTION 2: Presentation Photos ══ */}
                <motion.section
                    id="presentations"
                    ref={(el) => { sectionRefs.current['presentations'] = el; }}
                    initial={{ opacity: 0, y: 16 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.3, delay: 0.08 }}
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
                            gap: 10,
                            padding: '14px 20px',
                            borderBottom: '1px solid var(--border-subtle)',
                            background: 'var(--bg-elevated)',
                        }}
                    >
                        <div
                            style={{
                                width: 32,
                                height: 32,
                                borderRadius: 'var(--radius-md)',
                                background: 'color-mix(in srgb, var(--accent-primary) 15%, var(--bg-card))',
                                border: '1px solid color-mix(in srgb, var(--accent-primary) 30%, transparent)',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                color: 'var(--accent-primary)',
                                fontSize: 14,
                                fontWeight: 800,
                            }}
                        >
                            2
                        </div>
                        <div>
                            <div style={{ fontSize: 14, fontWeight: 700, color: 'var(--text-primary)' }}>Presentation Photos</div>
                            <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>Property overview photos (4 standard + extras)</div>
                        </div>
                    </div>
                    <div style={{ padding: '20px' }}>
                        <ScopeSheetPresentationsSection
                            presentations={data.presentations}
                            onChange={(presentations: ScopeSheetPresentation[]) => onChange({ ...data, presentations })}
                        />
                    </div>
                </motion.section>

                {/* ══ SECTION 3: Zones ══ */}
                <motion.section
                    id="zones"
                    ref={(el) => { sectionRefs.current['zones'] = el; }}
                    initial={{ opacity: 0, y: 16 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.3, delay: 0.16 }}
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
                            gap: 10,
                            padding: '14px 20px',
                            borderBottom: '1px solid var(--border-subtle)',
                            background: 'var(--bg-elevated)',
                        }}
                    >
                        <div
                            style={{
                                width: 32,
                                height: 32,
                                borderRadius: 'var(--radius-md)',
                                background: 'color-mix(in srgb, var(--accent-secondary) 15%, var(--bg-card))',
                                border: '1px solid color-mix(in srgb, var(--accent-secondary) 30%, transparent)',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                color: 'var(--accent-secondary)',
                                fontSize: 14,
                                fontWeight: 800,
                            }}
                        >
                            3
                        </div>
                        <div>
                            <div style={{ fontSize: 14, fontWeight: 700, color: 'var(--text-primary)' }}>Damage Zones</div>
                            <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>Add zones from the catalog and document with photos</div>
                        </div>
                    </div>
                    <div style={{ padding: '20px' }}>
                        <ScopeSheetZonesSection
                            zones={data.zones}
                            onChange={(zones: ScopeSheetZone[]) => onChange({ ...data, zones })}
                        />
                    </div>
                </motion.section>

            </div>
        </form>
    );
}

const labelStyle: React.CSSProperties = {
    display: 'block',
    fontSize: 12,
    fontWeight: 700,
    color: 'var(--text-secondary)',
    fontFamily: 'var(--font-sans)',
    textTransform: 'uppercase',
    letterSpacing: '0.06em',
    marginBottom: 8,
};

const lockedFieldStyle: React.CSSProperties = {
    height: 'var(--input-height)',
    display: 'flex',
    alignItems: 'center',
    padding: '0 12px',
    background: 'color-mix(in srgb, var(--accent-primary) 8%, var(--bg-elevated))',
    border: '1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)',
    borderRadius: 'var(--radius-md)',
    fontSize: 13,
    color: 'var(--text-secondary)',
    fontFamily: 'var(--font-sans)',
};
