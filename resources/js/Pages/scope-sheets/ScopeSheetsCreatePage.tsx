import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowLeft, ClipboardList } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { ScopeSheetForm } from './components/ScopeSheetForm';
import { useCreateScopeSheet } from '@/modules/scope-sheets/hooks/useScopeSheetMutations';
import type { ScopeSheetFormData } from '@/modules/scope-sheets/types';
import { DEFAULT_SCOPE_SHEET_FORM } from '@/modules/scope-sheets/types';
import type { AuthPageProps } from '@/types/auth';

export default function ScopeSheetsCreatePage(): React.JSX.Element {
    const { auth } = usePage<AuthPageProps>().props;

    // Support pre-filling claim_id from URL query params
    const urlParams = new URLSearchParams(window.location.search);
    const prefilledClaimId = urlParams.get('claim_id') ? Number(urlParams.get('claim_id')) : null;

    const [formData, setFormData] = React.useState<ScopeSheetFormData>({
        ...DEFAULT_SCOPE_SHEET_FORM,
        claim_id: prefilledClaimId,
        generated_by: auth.user?.id ?? null,
    });

    const createMutation = useCreateScopeSheet();

    function handleSubmit(): void {
        createMutation.mutate(formData);
    }

    return (
        <>
            <Head title="New Scope Sheet" />
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
                                href="/scope-sheets"
                                style={{ display: 'flex', alignItems: 'center', gap: 5, fontSize: 12, color: 'var(--text-muted)', textDecoration: 'none' }}
                            >
                                <ArrowLeft size={13} /> Scope Sheets
                            </Link>
                            <span style={{ color: 'var(--text-disabled)', fontSize: 12 }}>/</span>
                            <span style={{ fontSize: 12, color: 'var(--text-secondary)' }}>New</span>
                        </div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 12 }}>
                            <div
                                style={{
                                    width: 40,
                                    height: 40,
                                    borderRadius: 'var(--radius-md)',
                                    background: 'color-mix(in srgb, var(--accent-primary) 15%, var(--bg-card))',
                                    border: '1px solid color-mix(in srgb, var(--accent-primary) 30%, transparent)',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    color: 'var(--accent-primary)',
                                }}
                            >
                                <ClipboardList size={20} />
                            </div>
                            <div>
                                <h1 style={{ margin: 0, fontSize: 22, fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>
                                    New Scope Sheet
                                </h1>
                                <p style={{ margin: '3px 0 0', fontSize: 13, color: 'var(--text-muted)' }}>
                                    Document damage zones and attach photos for this claim.
                                </p>
                            </div>
                        </div>
                    </div>

                    {/* ── Form ── */}
                    <PermissionGuard permissions={['CREATE_SCOPE_SHEET']}>
                        <ScopeSheetForm
                            data={formData}
                            onChange={setFormData}
                            onSubmit={handleSubmit}
                            isSubmitting={createMutation.isPending}
                            submitLabel="Create Scope Sheet"
                            lockedClaimId={prefilledClaimId}
                        />
                    </PermissionGuard>
                </div>
            </AppLayout>
        </>
    );
}
