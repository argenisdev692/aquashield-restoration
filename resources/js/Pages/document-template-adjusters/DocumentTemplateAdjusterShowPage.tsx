import * as React from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { ArrowLeft, ExternalLink, Pencil, Trash2 } from 'lucide-react';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useDeleteDocumentTemplateAdjuster } from '@/modules/document-template-adjusters/hooks/useDocumentTemplateAdjusterMutations';
import { ADJUSTER_TEMPLATE_TYPES } from '@/modules/document-template-adjusters/types';
import type { DocumentTemplateAdjuster } from '@/modules/document-template-adjusters/types';
import { formatDateShort } from '@/utils/dateFormatter';
import AppLayout from '@/pages/layouts/AppLayout';

interface ShowPageProps extends PageProps {
    uuid: string;
    documentTemplateAdjuster: DocumentTemplateAdjuster;
}

export default function DocumentTemplateAdjusterShowPage(): React.JSX.Element {
    const { documentTemplateAdjuster: t } = usePage<ShowPageProps>().props;
    const [showDeleteModal, setShowDeleteModal] = React.useState(false);
    const deleteMutation = useDeleteDocumentTemplateAdjuster();

    async function handleConfirmDelete(): Promise<void> {
        await deleteMutation.mutateAsync(t.uuid);
        router.visit('/document-template-adjusters');
    }

    const typeLabel = ADJUSTER_TEMPLATE_TYPES.find((x) => x.value === t.template_type_adjuster)?.label
        ?? t.template_type_adjuster;

    return (
        <>
            <Head title={`Adjuster Template — ${typeLabel}`} />
            <AppLayout>
                <div className="flex flex-col gap-6">

                    {/* ── Header ── */}
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div className="flex items-center gap-3">
                            <Link
                                href="/document-template-adjusters"
                                className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0"
                                aria-label="Back to list"
                            >
                                <ArrowLeft size={16} />
                            </Link>
                            <div>
                                <h1
                                    className="text-2xl font-extrabold tracking-tight"
                                    style={{ color: 'var(--text-primary)' }}
                                >
                                    {typeLabel}
                                </h1>
                                <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                    Document Template Adjuster
                                </p>
                            </div>
                        </div>

                        <div className="flex items-center gap-2">
                            <PermissionGuard permissions={['UPDATE_DOCUMENT_TEMPLATE_ADJUSTER']}>
                                <Link
                                    href={`/document-template-adjusters/${t.uuid}/edit`}
                                    className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold"
                                >
                                    <Pencil size={14} />
                                    Edit
                                </Link>
                            </PermissionGuard>
                            <PermissionGuard permissions={['DELETE_DOCUMENT_TEMPLATE_ADJUSTER']}>
                                <button
                                    type="button"
                                    onClick={() => setShowDeleteModal(true)}
                                    className="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition-colors"
                                    style={{
                                        color: 'var(--accent-error)',
                                        border: '1px solid color-mix(in srgb, var(--accent-error) 30%, transparent)',
                                        background: 'color-mix(in srgb, var(--accent-error) 8%, transparent)',
                                    }}
                                    aria-label="Delete template adjuster"
                                >
                                    <Trash2 size={14} />
                                    Delete
                                </button>
                            </PermissionGuard>
                        </div>
                    </div>

                    {/* ── Detail Card ── */}
                    <div
                        className="card grid grid-cols-1 gap-6 sm:grid-cols-2"
                        style={{ fontFamily: 'var(--font-sans)' }}
                    >
                        <DetailField label="Template Type">
                            <span
                                className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                                style={{
                                    color: 'var(--accent-primary)',
                                    background: 'color-mix(in srgb, var(--accent-primary) 15%, transparent)',
                                    border: '1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)',
                                }}
                            >
                                {typeLabel}
                            </span>
                        </DetailField>

                        <DetailField
                            label="Public Adjuster"
                            value={t.public_adjuster_name ?? String(t.public_adjuster_id)}
                        />

                        <DetailField
                            label="Uploaded By"
                            value={t.uploaded_by_name ?? String(t.uploaded_by)}
                        />

                        <DetailField
                            label="Created"
                            value={formatDateShort(t.created_at)}
                        />

                        <DetailField
                            label="Last Updated"
                            value={formatDateShort(t.updated_at)}
                        />

                        {t.template_description_adjuster ? (
                            <div className="sm:col-span-2">
                                <DetailField
                                    label="Description"
                                    value={t.template_description_adjuster}
                                />
                            </div>
                        ) : null}

                        {t.template_path_adjuster ? (
                            <div className="sm:col-span-2">
                                <p
                                    className="mb-2 text-xs font-semibold uppercase tracking-widest"
                                    style={{ color: 'var(--text-muted)' }}
                                >
                                    File
                                </p>
                                <a
                                    href={t.template_path_adjuster}
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    className="inline-flex items-center gap-2 text-sm font-medium"
                                    style={{ color: 'var(--accent-primary)' }}
                                >
                                    <ExternalLink size={14} />
                                    View / Download File
                                </a>
                            </div>
                        ) : null}
                    </div>
                </div>

                <DeleteConfirmModal
                    open={showDeleteModal}
                    entityLabel={typeLabel}
                    onConfirm={() => { void handleConfirmDelete(); }}
                    onCancel={() => setShowDeleteModal(false)}
                    isDeleting={deleteMutation.isPending}
                />
            </AppLayout>
        </>
    );
}

function DetailField({
    label,
    value,
    children,
}: {
    label: string;
    value?: string;
    children?: React.ReactNode;
}): React.JSX.Element {
    return (
        <div>
            <p
                className="mb-1 text-xs font-semibold uppercase tracking-widest"
                style={{ color: 'var(--text-muted)' }}
            >
                {label}
            </p>
            {children ?? (
                <p className="text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                    {value ?? '—'}
                </p>
            )}
        </div>
    );
}
