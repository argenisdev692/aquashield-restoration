import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { ArrowLeft, Download, ExternalLink, FileText, Pencil, Trash2, Users } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useFileEsx } from '@/modules/files-esx/hooks/useFileEsx';
import { useFileEsxMutations } from '@/modules/files-esx/hooks/useFileEsxMutations';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { formatDateShort } from '@/utils/dateFormatter';
import { router } from '@inertiajs/react';

interface FileEsxShowPageProps extends PageProps {
    uuid: string;
}

function DetailRow({ label, value }: { label: string; value: React.ReactNode }): React.JSX.Element {
    return (
        <div
            className="flex flex-col gap-1 rounded-xl p-4"
            style={{ background: 'var(--bg-surface)', border: '1px solid var(--border-subtle)' }}
        >
            <span className="text-xs font-semibold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                {label}
            </span>
            <span className="text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                {value}
            </span>
        </div>
    );
}

export default function FileEsxShowPage(): React.JSX.Element {
    const { uuid } = usePage<FileEsxShowPageProps>().props;
    const { data, isPending } = useFileEsx(uuid);
    const file = data?.data ?? null;
    const [pendingDelete, setPendingDelete] = React.useState<boolean>(false);

    const { deleteFileEsx } = useFileEsxMutations();

    async function handleConfirmDelete(): Promise<void> {
        try {
            await deleteFileEsx.mutateAsync(uuid);
            router.visit('/files-esx');
        } catch {
        }
    }

    return (
        <>
            <Head title={file?.file_name ?? 'File ESX'} />
            <AppLayout>
                <div className="mx-auto max-w-3xl">
                    {/* Back */}
                    <Link
                        href="/files-esx"
                        prefetch
                        className="mb-6 inline-flex items-center gap-2 text-sm font-medium transition-colors"
                        style={{ color: 'var(--text-muted)' }}
                    >
                        <ArrowLeft size={16} />
                        Back to Files ESX
                    </Link>

                    {isPending ? (
                        <div className="flex items-center justify-center py-20">
                            <div
                                className="h-10 w-10 animate-spin rounded-full border-2 border-t-transparent"
                                style={{ borderColor: 'var(--accent-primary)' }}
                            />
                        </div>
                    ) : file === null ? (
                        <div
                            className="rounded-2xl p-12 text-center"
                            style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
                        >
                            <p style={{ color: 'var(--text-muted)' }}>File ESX not found.</p>
                        </div>
                    ) : (
                        <>
                            {/* Header card */}
                            <div
                                className="mb-6 rounded-2xl p-6 shadow-xl"
                                style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
                            >
                                <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                                    <div className="flex items-center gap-4">
                                        <div
                                            className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl"
                                            style={{
                                                background: 'color-mix(in srgb, var(--accent-primary) 15%, transparent)',
                                                border: '1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)',
                                            }}
                                        >
                                            <FileText size={22} style={{ color: 'var(--accent-primary)' }} />
                                        </div>
                                        <div>
                                            <h1
                                                className="text-2xl font-extrabold tracking-tight"
                                                style={{ color: 'var(--text-primary)' }}
                                            >
                                                {file.file_name ?? 'Unnamed File'}
                                            </h1>
                                            <p className="mt-0.5 font-mono text-xs" style={{ color: 'var(--text-muted)' }}>
                                                {file.file_path}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-2 shrink-0">
                                        {file.file_url && (
                                            <>
                                                <a
                                                    href={file.file_url}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    className="btn-ghost flex items-center gap-2 px-4 py-2 text-sm font-semibold"
                                                    aria-label="Open file in new tab"
                                                    title="Open file"
                                                >
                                                    <ExternalLink size={15} />
                                                    Open
                                                </a>
                                                <a
                                                    href={file.file_url}
                                                    download={file.file_name ?? undefined}
                                                    className="btn-ghost flex items-center gap-2 px-4 py-2 text-sm font-semibold"
                                                    aria-label="Download file"
                                                    title="Download file"
                                                    style={{ color: 'var(--accent-primary)' }}
                                                >
                                                    <Download size={15} />
                                                    Download
                                                </a>
                                            </>
                                        )}
                                        <PermissionGuard permissions={['UPDATE_FILES_ESX']}>
                                            <Link
                                                href={`/files-esx/${uuid}/edit`}
                                                prefetch
                                                className="btn-ghost flex items-center gap-2 px-4 py-2 text-sm font-semibold"
                                            >
                                                <Pencil size={15} />
                                                Edit
                                            </Link>
                                        </PermissionGuard>
                                        <PermissionGuard permissions={['DELETE_FILES_ESX']}>
                                            <button
                                                type="button"
                                                onClick={() => setPendingDelete(true)}
                                                className="btn-ghost flex items-center gap-2 px-4 py-2 text-sm font-semibold"
                                                style={{ color: 'var(--accent-error)' }}
                                            >
                                                <Trash2 size={15} />
                                                Delete
                                            </button>
                                        </PermissionGuard>
                                    </div>
                                </div>
                            </div>

                            {/* Details grid */}
                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <DetailRow label="File Name" value={file.file_name ?? '—'} />
                                <DetailRow
                                    label="File Path"
                                    value={
                                        <span className="font-mono text-xs" style={{ color: 'var(--text-secondary)' }}>
                                            {file.file_path}
                                        </span>
                                    }
                                />
                                <DetailRow
                                    label="Uploaded By"
                                    value={
                                        file.uploader ? (
                                            <div className="flex flex-col gap-0.5">
                                                <span>{file.uploader.name}</span>
                                                <span className="text-xs" style={{ color: 'var(--text-muted)' }}>
                                                    {file.uploader.email}
                                                </span>
                                            </div>
                                        ) : '—'
                                    }
                                />
                                <DetailRow label="Created" value={formatDateShort(file.created_at)} />
                                <DetailRow label="Last Updated" value={formatDateShort(file.updated_at)} />
                            </div>

                            {/* Assigned Adjusters */}
                            <div
                                className="mt-6 rounded-2xl p-6 shadow-sm"
                                style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
                            >
                                <div className="mb-4 flex items-center gap-3">
                                    <Users size={18} style={{ color: 'var(--accent-primary)' }} />
                                    <h2 className="text-base font-bold" style={{ color: 'var(--text-primary)' }}>
                                        Assigned Adjusters
                                    </h2>
                                    <span
                                        className="rounded-full px-2 py-0.5 text-xs font-semibold"
                                        style={{
                                            background: 'color-mix(in srgb, var(--accent-primary) 15%, transparent)',
                                            color: 'var(--accent-primary)',
                                        }}
                                    >
                                        {file.assigned_adjusters.length}
                                    </span>
                                </div>

                                {file.assigned_adjusters.length === 0 ? (
                                    <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                        No adjusters assigned yet.
                                    </p>
                                ) : (
                                    <div className="flex flex-col gap-2">
                                        {file.assigned_adjusters.map((adjuster) => (
                                            <div
                                                key={adjuster.id}
                                                className="flex items-center gap-3 rounded-xl px-4 py-3"
                                                style={{
                                                    background: 'var(--bg-surface)',
                                                    border: '1px solid var(--border-subtle)',
                                                }}
                                            >
                                                <div
                                                    className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                                                    style={{
                                                        background: 'color-mix(in srgb, var(--accent-secondary) 20%, transparent)',
                                                        color: 'var(--accent-secondary)',
                                                    }}
                                                >
                                                    {adjuster.name.charAt(0).toUpperCase()}
                                                </div>
                                                <div>
                                                    <p className="text-sm font-semibold" style={{ color: 'var(--text-primary)' }}>
                                                        {adjuster.name}
                                                    </p>
                                                    <p className="text-xs" style={{ color: 'var(--text-muted)' }}>
                                                        {adjuster.email}
                                                    </p>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        </>
                    )}
                </div>

                <DeleteConfirmModal
                    open={pendingDelete}
                    entityLabel={file?.file_name ?? uuid}
                    onConfirm={handleConfirmDelete}
                    onCancel={() => setPendingDelete(false)}
                    isDeleting={deleteFileEsx.isPending}
                />
            </AppLayout>
        </>
    );
}
