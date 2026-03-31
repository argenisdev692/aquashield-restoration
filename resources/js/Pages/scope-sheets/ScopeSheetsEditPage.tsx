import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, ClipboardList, Loader2 } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { ScopeSheetForm } from './components/ScopeSheetForm';
import { useScopeSheet } from '@/modules/scope-sheets/hooks/useScopeSheet';
import { useUpdateScopeSheet } from '@/modules/scope-sheets/hooks/useScopeSheetMutations';
import type { ScopeSheetFormData } from '@/modules/scope-sheets/types';

interface PageProps {
    uuid: string;
}

export default function ScopeSheetsEditPage(): React.JSX.Element {
    const { uuid } = usePage().props as unknown as PageProps;
    const { data: sheet, isPending, isError } = useScopeSheet(uuid);
    const updateMutation = useUpdateScopeSheet();

    const [formData, setFormData] = React.useState<ScopeSheetFormData | null>(null);

    // Populate form once data loads
    React.useEffect(() => {
        if (!sheet || formData !== null) return;
        setFormData({
            claim_id: sheet.claim_id,
            generated_by: sheet.generated_by,
            scope_sheet_description: sheet.scope_sheet_description ?? '',
            presentations: sheet.presentations.map((p, i) => ({
                uuid: p.uuid,
                photo_type: p.photo_type,
                photo_path: p.photo_path,
                photo_order: p.photo_order ?? i,
            })),
            zones: sheet.zones.map((z, zi) => ({
                uuid: z.uuid,
                zone_id: z.zone_id,
                zone_name: z.zone_name,
                zone_order: z.zone_order ?? zi,
                zone_notes: z.zone_notes ?? '',
                photos: (z.photos ?? []).map((ph, pi) => ({
                    uuid: ph.uuid,
                    photo_path: ph.photo_path,
                    photo_order: ph.photo_order ?? pi,
                })),
            })),
        });
    }, [sheet, formData]);

    function handleSubmit(): void {
        if (!formData) return;
        updateMutation.mutate({ uuid, form: formData });
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
                    Failed to load scope sheet.
                </div>
            </AppLayout>
        );
    }

    return (
        <>
            <Head title={`Edit Scope Sheet — ${sheet.claim_number ?? sheet.claim_internal_id ?? uuid}`} />
            <AppLayout>
                <div
                    style={{
                        padding: '24px 28px',
                        maxWidth: 1100,
                        margin: '0 auto',
                        width: '100%',
                        fontFamily: 'var(--font-sans)',
                    }}
                >
                    {/* ── Header ── */}
                    <div style={{ marginBottom: 28 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 8 }}>
                            <Link
                                href={`/scope-sheets/${uuid}`}
                                style={{ display: 'flex', alignItems: 'center', gap: 5, fontSize: 12, color: 'var(--text-muted)', textDecoration: 'none' }}
                            >
                                <ArrowLeft size={13} /> View
                            </Link>
                            <span style={{ color: 'var(--text-disabled)', fontSize: 12 }}>/</span>
                            <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>Edit</span>
                        </div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                            <div
                                style={{
                                    width: 40,
                                    height: 40,
                                    borderRadius: 'var(--radius-md)',
                                    background: 'color-mix(in srgb, var(--accent-warning) 15%, var(--bg-card))',
                                    border: '1px solid color-mix(in srgb, var(--accent-warning) 30%, transparent)',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    color: 'var(--accent-warning)',
                                }}
                            >
                                <ClipboardList size={20} />
                            </div>
                            <div>
                                <h1 style={{ margin: 0, fontSize: 22, fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>
                                    Edit Scope Sheet
                                </h1>
                                <p style={{ margin: '3px 0 0', fontSize: 13, color: 'var(--text-muted)' }}>
                                    Claim {sheet.claim_number ?? sheet.claim_internal_id}
                                    {sheet.property_address ? ` — ${sheet.property_address}` : ''}
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* ── Form ── */}
                    <PermissionGuard permissions={['UPDATE_SCOPE_SHEET']}>
                        {formData !== null && (
                            <ScopeSheetForm
                                data={formData}
                                onChange={setFormData}
                                onSubmit={handleSubmit}
                                isSubmitting={updateMutation.isPending}
                                submitLabel="Save Changes"
                                lockedClaimId={sheet.claim_id}
                            />
                        )}
                    </PermissionGuard>
                </div>
            </AppLayout>
        </>
    );
}
