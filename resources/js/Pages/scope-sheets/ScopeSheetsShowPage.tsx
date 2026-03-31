import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    ArrowLeft, Pencil, MapPin, Images, LayoutGrid, FileText,
    Download, Loader2, ClipboardList, User, Calendar, Hash,
    Eye, ChevronDown, ChevronRight,
} from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useScopeSheet } from '@/modules/scope-sheets/hooks/useScopeSheet';
import { PhotoViewModal } from './components/form/PhotoViewModal';
import { PRESENTATION_PHOTO_TYPE_LABELS } from '@/modules/scope-sheets/types';

interface PageProps { uuid: string }

function formatDate(str: string | null): string {
    if (!str) return '—';
    return new Date(str).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
}

// ─── Section Card ─────────────────────────────────────────────────────────────

function SectionCard({ title, icon, children, accent = 'var(--accent-primary)' }: {
    title: string;
    icon: React.ReactNode;
    children: React.ReactNode;
    accent?: string;
}): React.JSX.Element {
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
                    display: 'flex', alignItems: 'center', gap: 10,
                    padding: '12px 18px',
                    borderBottom: '1px solid var(--border-subtle)',
                    background: 'var(--bg-elevated)',
                }}
            >
                <span style={{ color: accent }}>{icon}</span>
                <span style={{ fontSize: 13, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', textTransform: 'uppercase', letterSpacing: '0.08em' }}>
                    {title}
                </span>
            </div>
            <div style={{ padding: 18 }}>{children}</div>
        </div>
    );
}

function Field({ label, value }: { label: string; value: React.ReactNode }): React.JSX.Element {
    return (
        <div style={{ display: 'flex', gap: 12, marginBottom: 10, fontSize: 13, fontFamily: 'var(--font-sans)', alignItems: 'flex-start' }}>
            <span style={{ color: 'var(--text-muted)', minWidth: 150, flexShrink: 0 }}>{label}</span>
            <span style={{ color: 'var(--text-primary)', fontWeight: 500, flex: 1 }}>
                {value ?? <span style={{ color: 'var(--text-disabled)' }}>—</span>}
            </span>
        </div>
    );
}

// ─── Photo Grid ───────────────────────────────────────────────────────────────

function PhotoGrid({ photos, onView }: {
    photos: { src: string; caption?: string }[];
    onView: (idx: number) => void;
}): React.JSX.Element {
    if (photos.length === 0) {
        return (
            <div style={{ fontSize: 13, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)', fontStyle: 'italic' }}>
                No photos added.
            </div>
        );
    }

    return (
        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(180px, 1fr))', gap: 10 }}>
            {photos.map((p, idx) => (
                <div
                    key={idx}
                    onClick={() => onView(idx)}
                    role="button"
                    tabIndex={0}
                    aria-label={`View ${p.caption ?? 'photo'}`}
                    onKeyDown={(e) => { if (e.key === 'Enter' || e.key === ' ') onView(idx); }}
                    style={{
                        position: 'relative',
                        borderRadius: 'var(--radius-md)',
                        overflow: 'hidden',
                        aspectRatio: '4/3',
                        cursor: 'pointer',
                        border: '1px solid var(--border-default)',
                        background: 'var(--bg-elevated)',
                    }}
                >
                    <img
                        src={p.src}
                        alt={p.caption ?? `Photo ${idx + 1}`}
                        loading="lazy"
                        style={{ width: '100%', height: '100%', objectFit: 'cover', display: 'block', transition: 'transform 0.25s ease' }}
                        onMouseEnter={(e) => { e.currentTarget.style.transform = 'scale(1.04)'; }}
                        onMouseLeave={(e) => { e.currentTarget.style.transform = 'scale(1)'; }}
                    />
                    <div
                        style={{
                            position: 'absolute', inset: 0, display: 'flex', alignItems: 'flex-end',
                            background: 'linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 50%)',
                            padding: '8px',
                            opacity: 0, transition: 'opacity 0.2s ease',
                        }}
                        onMouseEnter={(e) => { e.currentTarget.style.opacity = '1'; }}
                        onMouseLeave={(e) => { e.currentTarget.style.opacity = '0'; }}
                    >
                        <div style={{ display: 'flex', alignItems: 'center', gap: 5, color: '#fff', fontSize: 11, fontFamily: 'var(--font-sans)' }}>
                            <Eye size={12} />
                            {p.caption ?? `Photo ${idx + 1}`}
                        </div>
                    </div>
                </div>
            ))}
        </div>
    );
}

// ─── Zone Accordion ───────────────────────────────────────────────────────────

function ZoneAccordion({ zone, index, onViewPhoto }: {
    zone: { zone_name?: string; zone_notes: string; photos: { photo_path: string }[] };
    index: number;
    onViewPhoto: (photos: { src: string; caption?: string }[], idx: number) => void;
}): React.JSX.Element {
    const [open, setOpen] = React.useState(true);

    return (
        <div
            style={{
                border: '1px solid var(--border-default)',
                borderRadius: 'var(--radius-lg)',
                overflow: 'hidden',
                background: 'var(--bg-elevated)',
            }}
        >
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                aria-expanded={open}
                style={{
                    display: 'flex', alignItems: 'center', gap: 10,
                    width: '100%', padding: '12px 16px', background: 'transparent',
                    border: 'none', borderBottom: open ? '1px solid var(--border-subtle)' : 'none',
                    cursor: 'pointer', textAlign: 'left', transition: 'background 0.15s ease',
                }}
            >
                <div
                    style={{
                        width: 26, height: 26, borderRadius: '50%', flexShrink: 0,
                        background: 'color-mix(in srgb, var(--accent-secondary) 18%, var(--bg-card))',
                        border: '1px solid color-mix(in srgb, var(--accent-secondary) 35%, transparent)',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                        fontSize: 11, fontWeight: 800, color: 'var(--accent-secondary)', fontFamily: 'var(--font-sans)',
                    }}
                >
                    {index + 1}
                </div>
                <MapPin size={14} style={{ color: 'var(--accent-secondary)', flexShrink: 0 }} />
                <span style={{ flex: 1, fontSize: 14, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                    {zone.zone_name ?? `Zone ${index + 1}`}
                </span>
                <span style={{ fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)', marginRight: 6 }}>
                    {zone.photos.length} photo{zone.photos.length !== 1 ? 's' : ''}
                </span>
                <div style={{ color: 'var(--text-muted)', transition: 'transform 0.2s ease', transform: open ? 'rotate(0deg)' : 'rotate(-90deg)' }}>
                    <ChevronDown size={15} />
                </div>
            </button>

            {open && (
                <div style={{ padding: '14px 16px', display: 'flex', flexDirection: 'column', gap: 14 }}>
                    {zone.zone_notes.trim() && (
                        <div
                            style={{
                                padding: '10px 14px',
                                background: 'var(--bg-card)',
                                border: '1px solid var(--border-subtle)',
                                borderRadius: 'var(--radius-md)',
                                fontSize: 13,
                                color: 'var(--text-secondary)',
                                fontFamily: 'var(--font-sans)',
                                lineHeight: 1.6,
                            }}
                        >
                            {zone.zone_notes}
                        </div>
                    )}
                    <PhotoGrid
                        photos={zone.photos.map((ph, pi) => ({
                            src: ph.photo_path,
                            caption: `${zone.zone_name ?? `Zone ${index + 1}`} — Photo ${pi + 1}`,
                        }))}
                        onView={(idx) =>
                            onViewPhoto(
                                zone.photos.map((ph, pi) => ({
                                    src: ph.photo_path,
                                    caption: `${zone.zone_name ?? `Zone ${index + 1}`} — Photo ${pi + 1}`,
                                })),
                                idx,
                            )
                        }
                    />
                </div>
            )}
        </div>
    );
}

// ─── Page ─────────────────────────────────────────────────────────────────────

export default function ScopeSheetsShowPage(): React.JSX.Element {
    const { uuid } = usePage().props as unknown as PageProps;
    const { data: sheet, isPending, isError } = useScopeSheet(uuid);
    const [viewingPhotos, setViewingPhotos] = React.useState<{ src: string; caption?: string }[]>([]);
    const [viewingIdx, setViewingIdx] = React.useState<number>(0);
    const [activeTab, setActiveTab] = React.useState<'overview' | 'zones'>('overview');

    function openPhotoViewer(photos: { src: string; caption?: string }[], idx: number): void {
        setViewingPhotos(photos);
        setViewingIdx(idx);
    }

    function handleGeneratePdf(): void {
        window.open(`/scope-sheets/data/admin/${uuid}/generate-pdf`, '_blank');
    }

    if (isPending) {
        return (
            <AppLayout>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: 320, gap: 12, fontFamily: 'var(--font-sans)', color: 'var(--text-muted)' }}>
                    <Loader2 size={20} style={{ animation: 'spin 1s linear infinite' }} />
                    Loading scope sheet…
                </div>
            </AppLayout>
        );
    }

    if (isError || !sheet) {
        return (
            <AppLayout>
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', minHeight: 320, fontFamily: 'var(--font-sans)', color: 'var(--accent-error)' }}>
                    Scope sheet not found.
                </div>
            </AppLayout>
        );
    }

    const tabs = [
        { id: 'overview' as const, label: 'Overview', icon: <FileText size={14} /> },
        { id: 'zones' as const, label: `Zones (${sheet.zones.length})`, icon: <LayoutGrid size={14} /> },
    ];

    return (
        <>
            <Head title={`Scope Sheet — ${sheet.claim_number ?? sheet.claim_internal_id ?? uuid}`} />
            <AppLayout>
                <div
                    style={{
                        padding: '24px 28px',
                        maxWidth: 1000,
                        margin: '0 auto',
                        width: '100%',
                        fontFamily: 'var(--font-sans)',
                        display: 'flex',
                        flexDirection: 'column',
                        gap: 20,
                    }}
                >
                    {/* ── Breadcrumb & Header ── */}
                    <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 16, flexWrap: 'wrap' }}>
                        <div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 6 }}>
                                <Link href="/scope-sheets" style={{ display: 'flex', alignItems: 'center', gap: 5, fontSize: 12, color: 'var(--text-muted)', textDecoration: 'none' }}>
                                    <ArrowLeft size={13} /> Scope Sheets
                                </Link>
                                <span style={{ color: 'var(--text-disabled)', fontSize: 12 }}>/</span>
                                <Link href={`/claims/${sheet.claim_id}`} style={{ fontSize: 12, color: 'var(--text-muted)', textDecoration: 'none' }}>
                                    Claim
                                </Link>
                                <ChevronRight size={11} style={{ color: 'var(--text-disabled)' }} />
                                <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>Scope Sheet</span>
                            </div>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                <div
                                    style={{
                                        width: 40, height: 40, borderRadius: 'var(--radius-md)', flexShrink: 0,
                                        background: 'color-mix(in srgb, var(--accent-primary) 15%, var(--bg-card))',
                                        border: '1px solid color-mix(in srgb, var(--accent-primary) 30%, transparent)',
                                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                                        color: 'var(--accent-primary)',
                                    }}
                                >
                                    <ClipboardList size={20} />
                                </div>
                                <div>
                                    <h1 style={{ margin: 0, fontSize: 22, fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>
                                        Scope Sheet
                                    </h1>
                                    <p style={{ margin: '3px 0 0', fontSize: 13, color: 'var(--text-muted)' }}>
                                        Claim {sheet.claim_number ?? sheet.claim_internal_id}
                                        {sheet.property_address ? ` — ${sheet.property_address}` : ''}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Action buttons */}
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                            <PermissionGuard permissions={['VIEW_SCOPE_SHEET']}>
                                <button
                                    type="button"
                                    onClick={handleGeneratePdf}
                                    aria-label="Generate PDF"
                                    style={headerBtnStyle('var(--accent-success)')}
                                >
                                    <Download size={14} /> Generate PDF
                                </button>
                            </PermissionGuard>
                            <PermissionGuard permissions={['UPDATE_SCOPE_SHEET']}>
                                <Link
                                    href={`/scope-sheets/${uuid}/edit`}
                                    style={headerBtnStyle('var(--accent-warning)')}
                                >
                                    <Pencil size={14} /> Edit
                                </Link>
                            </PermissionGuard>
                        </div>
                    </div>

                    {/* ── Stat chips ── */}
                    <div style={{ display: 'flex', gap: 10, flexWrap: 'wrap' }}>
                        {[
                            { icon: <Images size={13} />, label: `${sheet.presentations.length} Presentation Photos`, color: 'var(--accent-primary)' },
                            { icon: <LayoutGrid size={13} />, label: `${sheet.zones.length} Zones`, color: 'var(--accent-secondary)' },
                            { icon: <Calendar size={13} />, label: formatDate(sheet.created_at), color: 'var(--text-muted)' },
                            ...(sheet.deleted_at ? [{ icon: <Hash size={13} />, label: 'Deleted', color: 'var(--accent-error)' }] : []),
                        ].map((chip, i) => (
                            <div
                                key={i}
                                style={{
                                    display: 'inline-flex', alignItems: 'center', gap: 6,
                                    padding: '4px 12px', borderRadius: 999,
                                    background: `color-mix(in srgb, ${chip.color} 10%, var(--bg-card))`,
                                    border: `1px solid color-mix(in srgb, ${chip.color} 22%, transparent)`,
                                    fontSize: 12, fontWeight: 600, color: chip.color,
                                    fontFamily: 'var(--font-sans)',
                                }}
                            >
                                {chip.icon} {chip.label}
                            </div>
                        ))}
                    </div>

                    {/* ── Tabs ── */}
                    <div
                        role="tablist"
                        style={{
                            display: 'flex', borderBottom: '1px solid var(--border-subtle)',
                        }}
                    >
                        {tabs.map((tab) => (
                            <button
                                key={tab.id}
                                type="button"
                                role="tab"
                                aria-selected={activeTab === tab.id}
                                onClick={() => setActiveTab(tab.id)}
                                style={{
                                    display: 'flex', alignItems: 'center', gap: 7,
                                    padding: '10px 18px', background: 'transparent', border: 'none',
                                    borderBottom: `2px solid ${activeTab === tab.id ? 'var(--accent-primary)' : 'transparent'}`,
                                    color: activeTab === tab.id ? 'var(--accent-primary)' : 'var(--text-muted)',
                                    fontSize: 13, fontWeight: activeTab === tab.id ? 700 : 500,
                                    fontFamily: 'var(--font-sans)', cursor: 'pointer',
                                    transition: 'all 0.18s ease',
                                }}
                            >
                                {tab.icon} {tab.label}
                            </button>
                        ))}
                    </div>

                    {/* ── Tab panels ── */}
                    {activeTab === 'overview' && (
                        <motion.div
                            key="overview"
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.25 }}
                            style={{ display: 'flex', flexDirection: 'column', gap: 16 }}
                        >
                            {/* Claim Info */}
                            <SectionCard title="Claim Information" icon={<FileText size={16} />}>
                                <Field label="Claim Number" value={sheet.claim_number} />
                                <Field label="Claim Internal ID" value={sheet.claim_internal_id} />
                                <Field label="Property Address" value={sheet.property_address} />
                                <Field label="Inspector" value={
                                    <span style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                                        <User size={13} /> {sheet.generated_by_name ?? '—'}
                                    </span>
                                } />
                                <Field label="Created" value={formatDate(sheet.created_at)} />
                                {sheet.scope_sheet_description && (
                                    <div
                                        style={{
                                            marginTop: 10, padding: '10px 14px',
                                            background: 'var(--bg-elevated)',
                                            border: '1px solid var(--border-subtle)',
                                            borderRadius: 'var(--radius-md)',
                                            fontSize: 13, color: 'var(--text-secondary)',
                                            fontFamily: 'var(--font-sans)', lineHeight: 1.6,
                                        }}
                                    >
                                        {sheet.scope_sheet_description}
                                    </div>
                                )}
                            </SectionCard>

                            {/* Presentation Photos */}
                            <SectionCard title={`Presentation Photos (${sheet.presentations.length})`} icon={<Images size={16} />}>
                                <PhotoGrid
                                    photos={sheet.presentations.map((p) => ({
                                        src: p.photo_path,
                                        caption: PRESENTATION_PHOTO_TYPE_LABELS[p.photo_type] ?? p.photo_type,
                                    }))}
                                    onView={(idx) =>
                                        openPhotoViewer(
                                            sheet.presentations.map((p) => ({
                                                src: p.photo_path,
                                                caption: PRESENTATION_PHOTO_TYPE_LABELS[p.photo_type] ?? p.photo_type,
                                            })),
                                            idx,
                                        )
                                    }
                                />
                            </SectionCard>
                        </motion.div>
                    )}

                    {activeTab === 'zones' && (
                        <motion.div
                            key="zones"
                            initial={{ opacity: 0, y: 10 }}
                            animate={{ opacity: 1, y: 0 }}
                            transition={{ duration: 0.25 }}
                            style={{ display: 'flex', flexDirection: 'column', gap: 12 }}
                        >
                            {sheet.zones.length === 0 ? (
                                <div
                                    style={{
                                        display: 'flex', flexDirection: 'column', alignItems: 'center',
                                        justifyContent: 'center', gap: 12, padding: '48px 24px',
                                        background: 'var(--bg-card)', border: '1px solid var(--border-default)',
                                        borderRadius: 'var(--radius-lg)', color: 'var(--text-muted)',
                                        fontFamily: 'var(--font-sans)',
                                    }}
                                >
                                    <LayoutGrid size={32} style={{ opacity: 0.4 }} />
                                    <span style={{ fontSize: 14, fontWeight: 600 }}>No zones added to this scope sheet.</span>
                                    <PermissionGuard permissions={['UPDATE_SCOPE_SHEET']}>
                                        <Link
                                            href={`/scope-sheets/${uuid}/edit`}
                                            style={{ fontSize: 13, color: 'var(--accent-primary)', textDecoration: 'none', fontWeight: 600 }}
                                        >
                                            Add zones →
                                        </Link>
                                    </PermissionGuard>
                                </div>
                            ) : (
                                sheet.zones.map((zone, idx) => (
                                    <ZoneAccordion
                                        key={zone.uuid ?? `zone-${idx}`}
                                        zone={zone}
                                        index={idx}
                                        onViewPhoto={openPhotoViewer}
                                    />
                                ))
                            )}
                        </motion.div>
                    )}
                </div>

                {/* ── Photo viewer modal ── */}
                <PhotoViewModal
                    open={viewingPhotos.length > 0}
                    src={viewingPhotos[viewingIdx]?.src ?? ''}
                    caption={viewingPhotos[viewingIdx]?.caption}
                    onClose={() => setViewingPhotos([])}
                />
            </AppLayout>
        </>
    );
}

function headerBtnStyle(color: string): React.CSSProperties {
    return {
        display: 'inline-flex', alignItems: 'center', gap: 6,
        padding: '8px 14px', borderRadius: 'var(--radius-md)',
        border: `1px solid color-mix(in srgb, ${color} 40%, transparent)`,
        background: `color-mix(in srgb, ${color} 12%, var(--bg-card))`,
        color, fontSize: 13, fontWeight: 700, fontFamily: 'var(--font-sans)',
        cursor: 'pointer', transition: 'all 0.15s ease', textDecoration: 'none',
    };
}
