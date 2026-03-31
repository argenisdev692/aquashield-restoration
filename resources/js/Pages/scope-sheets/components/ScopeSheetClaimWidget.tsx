import * as React from 'react';
import { Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import {
    ClipboardList, Plus, Eye, Pencil, Download,
    Images, LayoutGrid, CheckCircle2, Clock, Loader2,
} from 'lucide-react';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useScopeSheetByClaim } from '@/modules/scope-sheets/hooks/useScopeSheet';

interface Props {
    claimId: number;
}

export function ScopeSheetClaimWidget({ claimId }: Props): React.JSX.Element {
    const { data, isPending } = useScopeSheetByClaim(claimId);

    const existing = data?.data?.[0] ?? null;
    const hasSheet = existing !== null;

    function handleGeneratePdf(uuid: string): void {
        window.open(`/scope-sheets/data/admin/${uuid}/generate-pdf`, '_blank');
    }

    return (
        <motion.div
            initial={{ opacity: 0, x: -16 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.3, ease: [0.25, 0.46, 0.45, 0.94] }}
            style={{ display: 'flex', gap: 0 }}
        >
            {/* Timeline spine icon */}
            <div style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', marginRight: 20, flexShrink: 0 }}>
                <div
                    style={{
                        width: 48,
                        height: 48,
                        borderRadius: '50%',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        background: hasSheet
                            ? 'color-mix(in srgb, var(--accent-primary) 15%, var(--bg-card))'
                            : 'color-mix(in srgb, var(--accent-warning) 12%, var(--bg-card))',
                        border: `2px solid ${hasSheet
                            ? 'color-mix(in srgb, var(--accent-primary) 40%, transparent)'
                            : 'color-mix(in srgb, var(--accent-warning) 35%, transparent)'}`,
                        color: hasSheet ? 'var(--accent-primary)' : 'var(--accent-warning)',
                        boxShadow: hasSheet
                            ? '0 0 0 4px color-mix(in srgb, var(--accent-primary) 10%, transparent)'
                            : '0 0 0 4px color-mix(in srgb, var(--accent-warning) 8%, transparent)',
                        flexShrink: 0,
                        transition: 'all 0.25s ease',
                    }}
                >
                    <ClipboardList size={20} />
                </div>
            </div>

            {/* Card */}
            <div
                style={{
                    flex: 1,
                    background: 'var(--bg-card)',
                    border: `1px solid ${hasSheet
                        ? 'color-mix(in srgb, var(--accent-primary) 25%, var(--border-default))'
                        : 'var(--border-default)'}`,
                    borderRadius: 'var(--radius-lg)',
                    overflow: 'hidden',
                    boxShadow: hasSheet
                        ? '0 2px 12px color-mix(in srgb, var(--accent-primary) 10%, transparent)'
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
                        background: hasSheet
                            ? 'color-mix(in srgb, var(--accent-primary) 6%, var(--bg-elevated))'
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
                            Scope Sheet
                        </span>

                        {/* Status badge */}
                        {isPending ? (
                            <span style={{ display: 'inline-flex', alignItems: 'center', gap: 5, fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                <Loader2 size={10} style={{ animation: 'spin 1s linear infinite' }} /> Loading…
                            </span>
                        ) : hasSheet ? (
                            <span
                                style={{
                                    display: 'inline-flex', alignItems: 'center', gap: 5,
                                    padding: '2px 10px', borderRadius: 999, fontSize: 11, fontWeight: 700,
                                    fontFamily: 'var(--font-sans)',
                                    background: 'color-mix(in srgb, var(--accent-primary) 15%, var(--bg-card))',
                                    color: 'var(--accent-primary)',
                                    border: '1px solid color-mix(in srgb, var(--accent-primary) 35%, transparent)',
                                }}
                            >
                                <CheckCircle2 size={10} /> Available
                            </span>
                        ) : (
                            <span
                                style={{
                                    display: 'inline-flex', alignItems: 'center', gap: 5,
                                    padding: '2px 10px', borderRadius: 999, fontSize: 11, fontWeight: 700,
                                    fontFamily: 'var(--font-sans)',
                                    background: 'color-mix(in srgb, var(--accent-warning) 12%, var(--bg-card))',
                                    color: 'var(--accent-warning)',
                                    border: '1px solid color-mix(in srgb, var(--accent-warning) 30%, transparent)',
                                }}
                            >
                                <Clock size={10} /> Not Created
                            </span>
                        )}
                    </div>

                    {/* Action buttons */}
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        {!isPending && hasSheet && (
                            <>
                                <PermissionGuard permissions={['VIEW_SCOPE_SHEET']}>
                                    <button
                                        type="button"
                                        onClick={() => handleGeneratePdf(existing.uuid)}
                                        aria-label="Generate PDF"
                                        style={actionBtnStyle('var(--accent-success)')}
                                    >
                                        <Download size={13} /> PDF
                                    </button>
                                </PermissionGuard>
                                <PermissionGuard permissions={['UPDATE_SCOPE_SHEET']}>
                                    <Link
                                        href={`/scope-sheets/${existing.uuid}/edit`}
                                        aria-label="Edit scope sheet"
                                        style={actionBtnStyle('var(--accent-warning)')}
                                    >
                                        <Pencil size={13} /> Edit
                                    </Link>
                                </PermissionGuard>
                                <PermissionGuard permissions={['VIEW_SCOPE_SHEET']}>
                                    <Link
                                        href={`/scope-sheets/${existing.uuid}`}
                                        aria-label="View scope sheet"
                                        style={actionBtnStyle('var(--accent-primary)')}
                                    >
                                        <Eye size={13} /> View
                                    </Link>
                                </PermissionGuard>
                            </>
                        )}

                        {!isPending && !hasSheet && (
                            <PermissionGuard permissions={['CREATE_SCOPE_SHEET']}>
                                <Link
                                    href={`/scope-sheets/create?claim_id=${claimId}`}
                                    aria-label="Create scope sheet for this claim"
                                    style={{
                                        display: 'inline-flex', alignItems: 'center', gap: 6,
                                        padding: '6px 14px', borderRadius: 'var(--radius-md)',
                                        background: 'var(--accent-primary)',
                                        border: 'none',
                                        color: 'var(--bg-base)', fontSize: 12, fontWeight: 700,
                                        fontFamily: 'var(--font-sans)', textDecoration: 'none',
                                        transition: 'all 0.15s ease',
                                    }}
                                >
                                    <Plus size={13} /> Add Scope Sheet
                                </Link>
                            </PermissionGuard>
                        )}
                    </div>
                </div>

                {/* Card body */}
                <div style={{ padding: '12px 18px 14px', display: 'flex', flexDirection: 'column', gap: 6 }}>
                    <p
                        style={{
                            margin: 0, fontSize: 13, color: 'var(--text-secondary)',
                            fontFamily: 'var(--font-sans)', lineHeight: 1.5,
                        }}
                    >
                        {hasSheet
                            ? 'Scope sheet document with damage zones and photos for this claim.'
                            : 'No scope sheet has been created for this claim yet. Add one to document damage zones and photos.'}
                    </p>

                    {hasSheet && existing && (
                        <div style={{ display: 'flex', gap: 12, marginTop: 6, flexWrap: 'wrap' }}>
                            <div style={{ display: 'inline-flex', alignItems: 'center', gap: 5, fontSize: 12, color: 'var(--accent-primary)', fontFamily: 'var(--font-sans)', fontWeight: 500 }}>
                                <Images size={12} /> {existing.presentations_count} presentation photo{existing.presentations_count !== 1 ? 's' : ''}
                            </div>
                            <div style={{ display: 'inline-flex', alignItems: 'center', gap: 5, fontSize: 12, color: 'var(--accent-secondary)', fontFamily: 'var(--font-sans)', fontWeight: 500 }}>
                                <LayoutGrid size={12} /> {existing.zones_count} zone{existing.zones_count !== 1 ? 's' : ''}
                            </div>
                            {existing.scope_sheet_description && (
                                <div
                                    style={{
                                        width: '100%', marginTop: 4,
                                        fontSize: 12, color: 'var(--text-muted)',
                                        fontFamily: 'var(--font-sans)', lineHeight: 1.5,
                                        overflow: 'hidden', textOverflow: 'ellipsis',
                                        display: '-webkit-box', WebkitLineClamp: 2, WebkitBoxOrient: 'vertical',
                                    }}
                                >
                                    {existing.scope_sheet_description}
                                </div>
                            )}
                        </div>
                    )}

                    {!hasSheet && !isPending && (
                        <p style={{ margin: '4px 0 0', fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)', fontStyle: 'italic' }}>
                            Use the "Add Scope Sheet" button to get started.
                        </p>
                    )}
                </div>
            </div>
        </motion.div>
    );
}

function actionBtnStyle(color: string): React.CSSProperties {
    return {
        display: 'inline-flex', alignItems: 'center', gap: 5,
        padding: '5px 12px', borderRadius: 'var(--radius-md)',
        border: `1px solid color-mix(in srgb, ${color} 40%, transparent)`,
        background: `color-mix(in srgb, ${color} 12%, var(--bg-elevated))`,
        color, fontSize: 12, fontWeight: 700, fontFamily: 'var(--font-sans)',
        cursor: 'pointer', transition: 'all 0.15s ease', textDecoration: 'none',
        flexShrink: 0,
    };
}
