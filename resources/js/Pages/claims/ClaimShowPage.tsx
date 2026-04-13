import * as React from 'react';
import { usePage, Link } from '@inertiajs/react';
import {
    Pencil, ArrowLeft, MapPin, Users, Building2, FileText, Layers,
    LayoutGrid, BookOpen, ShieldCheck, Handshake, ClipboardList,
    CheckCircle2, Clock, GitMerge, ChevronRight, Receipt,
} from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import type { Claim } from '@/modules/claims/types';
import { ScopeSheetClaimWidget } from '@/pages/scope-sheets/components/ScopeSheetClaimWidget';
import { InvoiceClaimTimeline } from './components/InvoiceClaimTimeline';

interface ClaimShowPageProps {
    claim: { data: Claim };
}

// ─── Sub-components ───────────────────────────────────────────────────────────

interface DetailCardProps {
    title: string;
    icon: React.ReactNode;
    children: React.ReactNode;
}

function DetailCard({ title, icon, children }: DetailCardProps): React.JSX.Element {
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
                    gap: 8,
                    padding: '12px 16px',
                    borderBottom: '1px solid var(--border-subtle)',
                    background: 'var(--bg-elevated)',
                }}
            >
                <span style={{ color: 'var(--accent-primary)' }}>{icon}</span>
                <span
                    style={{
                        fontSize: 12,
                        fontWeight: 700,
                        color: 'var(--text-primary)',
                        fontFamily: 'var(--font-sans)',
                        textTransform: 'uppercase',
                        letterSpacing: '0.08em',
                    }}
                >
                    {title}
                </span>
            </div>
            <div style={{ padding: '16px' }}>{children}</div>
        </div>
    );
}

function Field({ label, value }: { label: string; value: React.ReactNode }): React.JSX.Element {
    return (
        <div style={{ display: 'flex', gap: 8, marginBottom: 10, fontSize: 13, fontFamily: 'var(--font-sans)' }}>
            <span style={{ color: 'var(--text-muted)', minWidth: 160, flexShrink: 0 }}>{label}</span>
            <span style={{ color: 'var(--text-primary)', fontWeight: 500 }}>
                {value ?? <span style={{ color: 'var(--text-disabled)' }}>—</span>}
            </span>
        </div>
    );
}

// ─── Documents Timeline ────────────────────────────────────────────────────────

type DocStatus = 'available' | 'pending';

interface TimelineDoc {
    key: string;
    title: string;
    description: string;
    icon: React.ReactNode;
    status: DocStatus;
    detail: string | null;
    accentColor: string;
}

function buildDocuments(c: Claim): TimelineDoc[] {
    return [
        {
            key: 'alliance',
            title: 'Alliance Company Agreement',
            description: 'Document generated from the alliance company template, referencing the assigned alliance partner.',
            icon: <Handshake size={20} />,
            status: c.claim_alliance !== null ? 'available' : 'pending',
            detail: c.claim_alliance?.alliance_company_name ?? null,
            accentColor: 'var(--accent-success)',
        },
        {
            key: 'adjuster',
            title: 'Adjuster Document',
            description: 'Template document for the insurance adjuster assigned to this claim.',
            icon: <ShieldCheck size={20} />,
            status: (c.insurance_adjuster_assignment !== null || c.public_adjuster_assignment !== null)
                ? 'available'
                : 'pending',
            detail: c.insurance_adjuster_assignment?.adjuster_name
                ?? c.public_adjuster_assignment?.adjuster_name
                ?? null,
            accentColor: 'var(--accent-primary)',
        },
        {
            key: 'agreement',
            title: 'Agreement Template',
            description: 'Public adjuster agreement document based on the public company assignment.',
            icon: <FileText size={20} />,
            status: c.public_company_assignment !== null ? 'available' : 'pending',
            detail: c.public_company_assignment?.company_name ?? null,
            accentColor: 'var(--accent-secondary)',
        },
        {
            key: 'scope',
            title: 'Scope Sheet',
            description: 'Scope of work sheet derived from the service requests and damage description.',
            icon: <ClipboardList size={20} />,
            status: c.service_requests.length > 0 || (c.scope_of_work ?? '').trim().length > 0
                ? 'available'
                : 'pending',
            detail: c.service_requests.length > 0
                ? `${c.service_requests.length} service request${c.service_requests.length > 1 ? 's' : ''}`
                : (c.scope_of_work ? 'Scope defined' : null),
            accentColor: '#10b981',
        },
    ];
}

interface TimelineCardProps {
    doc: TimelineDoc;
    index: number;
    isLast: boolean;
}

function TimelineCard({ doc, index, isLast }: TimelineCardProps): React.JSX.Element {
    const isAvailable = doc.status === 'available';

    return (
        <motion.div
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.3, delay: index * 0.07, ease: [0.25, 0.46, 0.45, 0.94] }}
            style={{ display: 'flex', gap: 0 }}
        >
            {/* Timeline spine */}
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', marginRight: 20, flexShrink: 0 }}>
                {/* Icon circle */}
                <div
                    style={{
                        width: 48,
                        height: 48,
                        borderRadius: '50%',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        background: isAvailable
                            ? `color-mix(in srgb, ${doc.accentColor} 15%, var(--bg-card))`
                            : 'color-mix(in srgb, var(--accent-warning) 12%, var(--bg-card))',
                        border: `2px solid ${isAvailable
                            ? `color-mix(in srgb, ${doc.accentColor} 40%, transparent)`
                            : 'color-mix(in srgb, var(--accent-warning) 35%, transparent)'}`,
                        color: isAvailable ? doc.accentColor : 'var(--accent-warning)',
                        boxShadow: isAvailable
                            ? `0 0 0 4px color-mix(in srgb, ${doc.accentColor} 10%, transparent)`
                            : '0 0 0 4px color-mix(in srgb, var(--accent-warning) 8%, transparent)',
                        flexShrink: 0,
                        transition: 'all 0.25s ease',
                    }}
                >
                    {doc.icon}
                </div>

                {/* Connector line */}
                {!isLast && (
                    <div
                        style={{
                            width: 2,
                            flex: 1,
                            minHeight: 32,
                            background: isAvailable
                                ? `linear-gradient(to bottom, color-mix(in srgb, ${doc.accentColor} 40%, transparent), var(--border-subtle))`
                                : 'var(--border-subtle)',
                            borderRadius: 1,
                            marginTop: 6,
                        }}
                    />
                )}
            </div>

            {/* Card content */}
            <div
                style={{
                    flex: 1,
                    marginBottom: isLast ? 0 : 28,
                    background: 'var(--bg-card)',
                    border: `1px solid ${isAvailable
                        ? `color-mix(in srgb, ${doc.accentColor} 25%, var(--border-default))`
                        : 'var(--border-default)'}`,
                    borderRadius: 'var(--radius-lg)',
                    overflow: 'hidden',
                    boxShadow: isAvailable
                        ? `0 2px 12px color-mix(in srgb, ${doc.accentColor} 10%, transparent)`
                        : 'none',
                    transition: 'all 0.25s ease',
                }}
            >
                {/* Card header */}
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'space-between',
                        padding: '14px 18px',
                        borderBottom: '1px solid var(--border-subtle)',
                        background: isAvailable
                            ? `color-mix(in srgb, ${doc.accentColor} 6%, var(--bg-elevated))`
                            : 'var(--bg-elevated)',
                    }}
                >
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <span
                            style={{
                                fontSize: 14,
                                fontWeight: 700,
                                color: 'var(--text-primary)',
                                fontFamily: 'var(--font-sans)',
                            }}
                        >
                            {doc.title}
                        </span>

                        {/* Status badge */}
                        {isAvailable ? (
                            <span
                                style={{
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: 5,
                                    padding: '2px 10px',
                                    borderRadius: 999,
                                    fontSize: 11,
                                    fontWeight: 700,
                                    fontFamily: 'var(--font-sans)',
                                    background: `color-mix(in srgb, ${doc.accentColor} 15%, var(--bg-card))`,
                                    color: doc.accentColor,
                                    border: `1px solid color-mix(in srgb, ${doc.accentColor} 35%, transparent)`,
                                }}
                            >
                                <CheckCircle2 size={10} /> Available
                            </span>
                        ) : (
                            <span
                                style={{
                                    display: 'inline-flex',
                                    alignItems: 'center',
                                    gap: 5,
                                    padding: '2px 10px',
                                    borderRadius: 999,
                                    fontSize: 11,
                                    fontWeight: 700,
                                    fontFamily: 'var(--font-sans)',
                                    background: 'color-mix(in srgb, var(--accent-warning) 12%, var(--bg-card))',
                                    color: 'var(--accent-warning)',
                                    border: '1px solid color-mix(in srgb, var(--accent-warning) 30%, transparent)',
                                }}
                            >
                                <Clock size={10} /> Pending
                            </span>
                        )}
                    </div>

                    {/* Merge button */}
                    <button
                        type="button"
                        disabled={!isAvailable}
                        aria-label={`Merge PDF for ${doc.title}`}
                        style={{
                            display: 'inline-flex',
                            alignItems: 'center',
                            gap: 6,
                            padding: '6px 14px',
                            borderRadius: 'var(--radius-md)',
                            border: `1px solid ${isAvailable
                                ? `color-mix(in srgb, ${doc.accentColor} 40%, transparent)`
                                : 'var(--border-default)'}`,
                            background: isAvailable
                                ? `color-mix(in srgb, ${doc.accentColor} 12%, var(--bg-elevated))`
                                : 'var(--bg-elevated)',
                            color: isAvailable ? doc.accentColor : 'var(--text-disabled)',
                            fontSize: 12,
                            fontWeight: 700,
                            fontFamily: 'var(--font-sans)',
                            cursor: isAvailable ? 'pointer' : 'not-allowed',
                            transition: 'all 0.15s ease',
                            opacity: isAvailable ? 1 : 0.5,
                            letterSpacing: '0.03em',
                            flexShrink: 0,
                        }}
                    >
                        <GitMerge size={13} />
                        Merge PDF
                    </button>
                </div>

                {/* Card body */}
                <div style={{ padding: '12px 18px 14px', display: 'flex', flexDirection: 'column', gap: 6 }}>
                    <p
                        style={{
                            margin: 0,
                            fontSize: 13,
                            color: 'var(--text-secondary)',
                            fontFamily: 'var(--font-sans)',
                            lineHeight: 1.5,
                        }}
                    >
                        {doc.description}
                    </p>

                    {doc.detail && (
                        <div
                            style={{
                                display: 'inline-flex',
                                alignItems: 'center',
                                gap: 6,
                                marginTop: 4,
                                fontSize: 12,
                                color: isAvailable ? doc.accentColor : 'var(--text-muted)',
                                fontFamily: 'var(--font-sans)',
                                fontWeight: 500,
                            }}
                        >
                            <ChevronRight size={11} />
                            {doc.detail}
                        </div>
                    )}

                    {!isAvailable && (
                        <p
                            style={{
                                margin: '4px 0 0',
                                fontSize: 11,
                                color: 'var(--text-muted)',
                                fontFamily: 'var(--font-sans)',
                                fontStyle: 'italic',
                            }}
                        >
                            Complete the required assignment to generate this document.
                        </p>
                    )}
                </div>
            </div>
        </motion.div>
    );
}

// ─── Tabs ─────────────────────────────────────────────────────────────────────

type TabKey = 'overview' | 'documents' | 'invoices';

interface TabProps {
    id: TabKey;
    label: string;
    icon: React.ReactNode;
    active: boolean;
    onClick: () => void;
}

function Tab({ id, label, icon, active, onClick }: TabProps): React.JSX.Element {
    return (
        <button
            type="button"
            role="tab"
            id={`tab-${id}`}
            aria-selected={active}
            aria-controls={`panel-${id}`}
            onClick={onClick}
            style={{
                display: 'flex',
                alignItems: 'center',
                gap: 7,
                padding: '10px 18px',
                background: 'transparent',
                border: 'none',
                borderBottom: `2px solid ${active ? 'var(--accent-primary)' : 'transparent'}`,
                color: active ? 'var(--accent-primary)' : 'var(--text-muted)',
                fontSize: 13,
                fontWeight: active ? 700 : 500,
                fontFamily: 'var(--font-sans)',
                cursor: 'pointer',
                transition: 'all 0.18s ease',
                whiteSpace: 'nowrap',
            }}
        >
            {icon}
            {label}
        </button>
    );
}

// ─── Page ─────────────────────────────────────────────────────────────────────

export default function ClaimShowPage(): React.JSX.Element {
    const { claim } = usePage().props as unknown as ClaimShowPageProps;
    const c = claim.data;
    const [activeTab, setActiveTab] = React.useState<TabKey>('overview');

    const docs = buildDocuments(c);
    const availableCount = docs.filter((d) => d.status === 'available').length;

    return (
        <AppLayout>
            <div
                style={{
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 20,
                    padding: '24px 28px',
                    maxWidth: 980,
                    margin: '0 auto',
                    width: '100%',
                    fontFamily: 'var(--font-sans)',
                }}
            >
                {/* ── Header ── */}
                <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 16, flexWrap: 'wrap' }}>
                    <div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 4 }}>
                            <Link
                                href="/claims"
                                style={{
                                    display: 'flex', alignItems: 'center', gap: 5,
                                    fontSize: 12, color: 'var(--text-muted)', textDecoration: 'none',
                                }}
                            >
                                <ArrowLeft size={13} /> Claims
                            </Link>
                            <span style={{ color: 'var(--text-disabled)', fontSize: 12 }}>/</span>
                            <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>
                                {c.claim_internal_id}
                            </span>
                        </div>
                        <h1 style={{ margin: 0, fontSize: 22, fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>
                            Claim {c.claim_number ?? c.claim_internal_id}
                        </h1>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginTop: 6, flexWrap: 'wrap' }}>
                            {c.claim_status_name && (
                                <span
                                    style={{
                                        padding: '3px 12px', borderRadius: 999, fontSize: 11, fontWeight: 700,
                                        background: `color-mix(in srgb, ${c.claim_status_color ?? 'var(--accent-primary)'} 15%, var(--bg-card))`,
                                        color: c.claim_status_color ?? 'var(--accent-primary)',
                                        border: `1px solid color-mix(in srgb, ${c.claim_status_color ?? 'var(--accent-primary)'} 30%, transparent)`,
                                    }}
                                >
                                    {c.claim_status_name}
                                </span>
                            )}
                            {c.deleted_at && (
                                <span style={{ padding: '3px 10px', borderRadius: 999, fontSize: 11, fontWeight: 700, background: 'var(--deleted-row-bg)', color: 'var(--accent-error)', border: '1px solid var(--deleted-row-border)' }}>
                                    Deleted
                                </span>
                            )}
                        </div>
                    </div>

                    <PermissionGuard permissions={['UPDATE_CLAIM']}>
                        <Link
                            href={`/claims/${c.uuid}/edit`}
                            style={{
                                display: 'flex', alignItems: 'center', gap: 7,
                                padding: '8px 16px', borderRadius: 'var(--radius-md)',
                                background: 'var(--accent-primary)', color: '#fff',
                                fontSize: 13, fontWeight: 700, textDecoration: 'none',
                            }}
                        >
                            <Pencil size={13} /> Edit Claim
                        </Link>
                    </PermissionGuard>
                </div>

                {/* ── Tab bar ── */}
                <div
                    role="tablist"
                    aria-label="Claim sections"
                    style={{
                        display: 'flex',
                        borderBottom: '1px solid var(--border-default)',
                        background: 'var(--bg-card)',
                        borderRadius: 'var(--radius-lg) var(--radius-lg) 0 0',
                        padding: '0 8px',
                        border: '1px solid var(--border-default)',
                        gap: 0,
                    }}
                >
                    <Tab
                        id="overview"
                        label="Overview"
                        icon={<LayoutGrid size={14} />}
                        active={activeTab === 'overview'}
                        onClick={() => setActiveTab('overview')}
                    />
                    <Tab
                        id="documents"
                        label={`Documents & Timeline`}
                        icon={<BookOpen size={14} />}
                        active={activeTab === 'documents'}
                        onClick={() => setActiveTab('documents')}
                    />
                    <Tab
                        id="invoices"
                        label="Invoices"
                        icon={<Receipt size={14} />}
                        active={activeTab === 'invoices'}
                        onClick={() => setActiveTab('invoices')}
                    />
                    {/* Doc availability counter */}
                    <div style={{ marginLeft: 'auto', display: 'flex', alignItems: 'center', padding: '0 12px', gap: 6 }}>
                        <span style={{ fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                            {availableCount}/{docs.length} docs ready
                        </span>
                        <div style={{ display: 'flex', gap: 3 }}>
                            {docs.map((d) => (
                                <div
                                    key={d.key}
                                    title={d.title}
                                    style={{
                                        width: 6, height: 6, borderRadius: '50%',
                                        background: d.status === 'available' ? 'var(--accent-success)' : 'var(--accent-warning)',
                                    }}
                                />
                            ))}
                        </div>
                    </div>
                </div>

                {/* ── Tab Panels ── */}
                <AnimatePresence mode="wait">
                    {activeTab === 'overview' && (
                        <motion.div
                            key="overview"
                            id="panel-overview"
                            role="tabpanel"
                            aria-labelledby="tab-overview"
                            initial={{ opacity: 0, y: 6 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -6 }}
                            transition={{ duration: 0.2 }}
                        >
                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                                {/* Property */}
                                <DetailCard title="Property" icon={<MapPin size={14} />}>
                                    <Field label="Address" value={c.property_address} />
                                    <Field label="Property ID" value={c.property_id?.toString()} />
                                </DetailCard>

                                {/* Claim Info */}
                                <DetailCard title="Claim Information" icon={<FileText size={14} />}>
                                    <Field label="Policy Number" value={c.policy_number} />
                                    <Field label="Claim Number" value={c.claim_number} />
                                    <Field label="Internal ID" value={c.claim_internal_id} />
                                    <Field label="Date of Loss" value={c.date_of_loss ? new Date(c.date_of_loss).toLocaleDateString() : null} />
                                    <Field label="Claim Date" value={c.claim_date ? new Date(c.claim_date).toLocaleDateString() : null} />
                                    <Field label="Work Date" value={c.work_date ? new Date(c.work_date).toLocaleDateString() : null} />
                                    <Field label="Type of Damage" value={c.type_damage_name} />
                                    <Field label="Floors" value={c.number_of_floors?.toString() ?? null} />
                                    <Field label="Referred By" value={c.referred_by_name} />
                                    <Field label="Customer Reviewed" value={c.customer_reviewed === true ? 'Yes' : c.customer_reviewed === false ? 'No' : null} />
                                </DetailCard>

                                {/* Customers */}
                                <DetailCard title="Customers" icon={<Users size={14} />}>
                                    {c.customers.length === 0 ? (
                                        <span style={{ fontSize: 13, color: 'var(--text-muted)' }}>No customers assigned.</span>
                                    ) : (
                                        c.customers.map((cu, idx) => (
                                            <div
                                                key={cu.uuid}
                                                style={{
                                                    display: 'flex', alignItems: 'flex-start', gap: 10,
                                                    padding: '8px 0',
                                                    borderBottom: idx < c.customers.length - 1 ? '1px solid var(--border-subtle)' : 'none',
                                                }}
                                            >
                                                <div
                                                    style={{
                                                        width: 32, height: 32, borderRadius: '50%', flexShrink: 0,
                                                        background: 'color-mix(in srgb, var(--accent-primary) 15%, var(--bg-elevated))',
                                                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                                                        color: 'var(--accent-primary)', fontSize: 13, fontWeight: 700,
                                                    }}
                                                >
                                                    {cu.full_name.charAt(0).toUpperCase()}
                                                </div>
                                                <div>
                                                    <p style={{ margin: 0, fontSize: 13, fontWeight: 600, color: 'var(--text-primary)' }}>{cu.full_name}</p>
                                                    {cu.email && <p style={{ margin: 0, fontSize: 11, color: 'var(--text-muted)' }}>{cu.email}</p>}
                                                    {cu.cell_phone && <p style={{ margin: 0, fontSize: 11, color: 'var(--text-muted)' }}>{cu.cell_phone}</p>}
                                                </div>
                                            </div>
                                        ))
                                    )}
                                </DetailCard>

                                {/* Companies */}
                                <DetailCard title="Company Assignments" icon={<Building2 size={14} />}>
                                    <Field label="Insurance Co." value={c.insurance_company_assignment?.company_name ?? null} />
                                    <Field label="Public Co." value={c.public_company_assignment?.company_name ?? null} />
                                    <Field label="Ins. Adjuster" value={c.insurance_adjuster_assignment?.adjuster_name ?? null} />
                                    <Field label="Pub. Adjuster" value={c.public_adjuster_assignment?.adjuster_name ?? null} />
                                    <Field label="Alliance Co." value={c.claim_alliance?.alliance_company_name ?? null} />
                                </DetailCard>
                            </div>

                            {/* Additional Details — full width */}
                            <div style={{ marginTop: 16 }}>
                                <DetailCard title="Additional Details" icon={<Layers size={14} />}>
                                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0 24px' }}>
                                        <div>
                                            {c.description_of_loss && <Field label="Description of Loss" value={c.description_of_loss} />}
                                            {c.damage_description && <Field label="Damage Description" value={c.damage_description} />}
                                            {c.scope_of_work && <Field label="Scope of Work" value={c.scope_of_work} />}
                                        </div>
                                        <div>
                                            {c.causes_of_loss.length > 0 && (
                                                <Field
                                                    label="Causes of Loss"
                                                    value={
                                                        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                                                            {c.causes_of_loss.map((col) => (
                                                                <span key={col.id} style={{ padding: '2px 10px', borderRadius: 999, fontSize: 11, background: 'color-mix(in srgb, var(--accent-primary) 12%, var(--bg-elevated))', color: 'var(--accent-primary)', border: '1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)' }}>
                                                                    {col.name}
                                                                </span>
                                                            ))}
                                                        </div>
                                                    }
                                                />
                                            )}
                                            {c.service_requests.length > 0 && (
                                                <Field
                                                    label="Service Requests"
                                                    value={
                                                        <div style={{ display: 'flex', flexWrap: 'wrap', gap: 6 }}>
                                                            {c.service_requests.map((sr) => (
                                                                <span key={sr.id} style={{ padding: '2px 10px', borderRadius: 999, fontSize: 11, background: 'color-mix(in srgb, var(--accent-success) 12%, var(--bg-elevated))', color: 'var(--accent-success)', border: '1px solid color-mix(in srgb, var(--accent-success) 25%, transparent)' }}>
                                                                    {sr.name}
                                                                </span>
                                                            ))}
                                                        </div>
                                                    }
                                                />
                                            )}
                                        </div>
                                    </div>
                                    <div style={{ display: 'flex', gap: 16, marginTop: 8, paddingTop: 8, borderTop: '1px solid var(--border-subtle)', fontSize: 11, color: 'var(--text-muted)' }}>
                                        <span>Created: {new Date(c.created_at).toLocaleDateString()}</span>
                                        <span>Updated: {new Date(c.updated_at).toLocaleDateString()}</span>
                                    </div>
                                </DetailCard>
                            </div>
                        </motion.div>
                    )}

                    {activeTab === 'documents' && (
                        <motion.div
                            key="documents"
                            id="panel-documents"
                            role="tabpanel"
                            aria-labelledby="tab-documents"
                            initial={{ opacity: 0, y: 6 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -6 }}
                            transition={{ duration: 0.2 }}
                        >
                            {/* Summary bar */}
                            <div
                                style={{
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'space-between',
                                    padding: '12px 18px',
                                    background: 'var(--bg-card)',
                                    border: '1px solid var(--border-default)',
                                    borderRadius: 'var(--radius-lg)',
                                    marginBottom: 28,
                                    flexWrap: 'wrap',
                                    gap: 12,
                                }}
                            >
                                <div>
                                    <p style={{ margin: 0, fontSize: 14, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                                        Document Generation Timeline
                                    </p>
                                    <p style={{ margin: '2px 0 0', fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                        Track the availability of generated documents for this claim. Use Merge PDF when ready.
                                    </p>
                                </div>
                                <div style={{ display: 'flex', gap: 16, alignItems: 'center', flexShrink: 0 }}>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--accent-success)', fontFamily: 'var(--font-sans)', fontWeight: 600 }}>
                                        <CheckCircle2 size={13} />
                                        {availableCount} Available
                                    </div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--accent-warning)', fontFamily: 'var(--font-sans)', fontWeight: 600 }}>
                                        <Clock size={13} />
                                        {docs.length - availableCount} Pending
                                    </div>
                                </div>
                            </div>

                            {/* Timeline list */}
                            <div style={{ paddingLeft: 4 }}>
                                {docs.map((doc, idx) => (
                                    <TimelineCard
                                        key={doc.key}
                                        doc={doc}
                                        index={idx}
                                        isLast={false}
                                    />
                                ))}

                                {/* Scope Sheet widget — live query */}
                                <ScopeSheetClaimWidget claimId={c.id} />
                            </div>
                        </motion.div>
                    )}
                    {activeTab === 'invoices' && (
                        <motion.div
                            key="invoices"
                            id="panel-invoices"
                            role="tabpanel"
                            aria-labelledby="tab-invoices"
                            initial={{ opacity: 0, y: 6 }}
                            animate={{ opacity: 1, y: 0 }}
                            exit={{ opacity: 0, y: -6 }}
                            transition={{ duration: 0.2 }}
                        >
                            <InvoiceClaimTimeline claimId={c.id} />
                        </motion.div>
                    )}
                </AnimatePresence>
            </div>
        </AppLayout>
    );
}
