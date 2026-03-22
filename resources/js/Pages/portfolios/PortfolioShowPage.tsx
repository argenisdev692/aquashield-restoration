import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { ArrowLeft, FolderOpen, Image, Pencil, Trash2, Upload } from 'lucide-react';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { useDeletePortfolioImage, useUploadPortfolioImage } from '@/modules/portfolios/hooks/usePortfolioMutations';
import type { Portfolio, PortfolioImage } from '@/modules/portfolios/types';
import AppLayout from '@/pages/layouts/AppLayout';
import { formatDateShort } from '@/utils/dateFormatter';

interface PortfolioShowPageProps extends PageProps {
    portfolio: Portfolio;
}

export default function PortfolioShowPage(): React.JSX.Element {
    const { portfolio } = usePage<PortfolioShowPageProps>().props;
    const fileInputRef = React.useRef<HTMLInputElement>(null);
    const [pendingDeleteImage, setPendingDeleteImage] = React.useState<PortfolioImage | null>(null);

    const uploadImage = useUploadPortfolioImage(portfolio.uuid);
    const deleteImage = useDeletePortfolioImage(portfolio.uuid);

    async function handleFileChange(event: React.ChangeEvent<HTMLInputElement>): Promise<void> {
        const file = event.target.files?.[0];
        if (!file) return;
        await uploadImage.mutateAsync(file);
        if (fileInputRef.current) {
            fileInputRef.current.value = '';
        }
    }

    async function handleConfirmDeleteImage(): Promise<void> {
        if (pendingDeleteImage === null) return;
        await deleteImage.mutateAsync(pendingDeleteImage.uuid);
        setPendingDeleteImage(null);
    }

    const isDeleted = Boolean(portfolio.deleted_at);

    return (
        <>
            <Head title={`Portfolio · ${portfolio.project_type_title ?? portfolio.uuid.slice(0, 8)}`} />
            <AppLayout>
                <div className="mx-auto flex max-w-5xl flex-col gap-6">
                    <div className="flex items-center gap-4">
                        <Link
                            href="/portfolios"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold"
                        >
                            <ArrowLeft size={16} />
                            Back
                        </Link>
                    </div>

                    <div className="card flex flex-col gap-6 p-6">
                        <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div className="flex items-start gap-4">
                                <div
                                    className="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl"
                                    style={{
                                        background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                                        color: 'var(--accent-primary)',
                                    }}
                                >
                                    <FolderOpen size={24} />
                                </div>
                                <div>
                                    <h1 className="text-2xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                        {portfolio.project_type_title ?? 'Uncategorized Portfolio'}
                                    </h1>
                                    {portfolio.service_category_name !== null && (
                                        <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                            {portfolio.service_category_name}
                                        </p>
                                    )}
                                </div>
                            </div>

                            {!isDeleted && (
                                <Link
                                    href={`/portfolios/${portfolio.uuid}/edit`}
                                    className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold"
                                >
                                    <Pencil size={14} />
                                    Edit
                                </Link>
                            )}
                        </div>

                        <dl className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div>
                                <dt className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                    Images
                                </dt>
                                <dd className="mt-1 text-lg font-bold" style={{ color: 'var(--text-primary)' }}>
                                    {portfolio.images.length}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                    Created
                                </dt>
                                <dd className="mt-1 text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                                    {formatDateShort(portfolio.created_at)}
                                </dd>
                            </div>
                            <div>
                                <dt className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-muted)' }}>
                                    Status
                                </dt>
                                <dd className="mt-1">
                                    {isDeleted ? (
                                        <span
                                            className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                                            style={{
                                                color: 'var(--accent-error)',
                                                background: 'color-mix(in srgb, var(--accent-error) 15%, transparent)',
                                                border: '1px solid color-mix(in srgb, var(--accent-error) 25%, transparent)',
                                            }}
                                        >
                                            Deleted
                                        </span>
                                    ) : (
                                        <span
                                            className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                                            style={{
                                                color: 'var(--accent-success)',
                                                background: 'color-mix(in srgb, var(--accent-success) 15%, transparent)',
                                                border: '1px solid color-mix(in srgb, var(--accent-success) 25%, transparent)',
                                            }}
                                        >
                                            Active
                                        </span>
                                    )}
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <div className="card flex flex-col gap-4 p-6">
                        <div className="flex items-center justify-between">
                            <h2 className="text-lg font-bold" style={{ color: 'var(--text-primary)' }}>
                                Images
                                <span className="ml-2 text-sm font-normal" style={{ color: 'var(--text-muted)' }}>
                                    ({portfolio.images.length})
                                </span>
                            </h2>

                            {!isDeleted && (
                                <>
                                    <button
                                        type="button"
                                        onClick={() => fileInputRef.current?.click()}
                                        disabled={uploadImage.isPending}
                                        className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold disabled:opacity-50"
                                    >
                                        <Upload size={14} />
                                        {uploadImage.isPending ? 'Uploading…' : 'Upload Image'}
                                    </button>
                                    <input
                                        ref={fileInputRef}
                                        type="file"
                                        accept="image/jpeg,image/png,image/webp"
                                        className="sr-only"
                                        onChange={(e) => { void handleFileChange(e); }}
                                        aria-label="Upload portfolio image"
                                    />
                                </>
                            )}
                        </div>

                        {portfolio.images.length === 0 ? (
                            <div
                                className="flex flex-col items-center justify-center gap-3 rounded-2xl py-12"
                                style={{
                                    border: '2px dashed var(--border-default)',
                                    color: 'var(--text-muted)',
                                }}
                            >
                                <Image size={32} />
                                <p className="text-sm">No images uploaded yet.</p>
                            </div>
                        ) : (
                            <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                                {portfolio.images.map((img) => (
                                    <div
                                        key={img.uuid}
                                        className="group relative overflow-hidden rounded-xl"
                                        style={{ border: '1px solid var(--border-default)' }}
                                    >
                                        <img
                                            src={`/storage/${img.path}`}
                                            alt="Portfolio image"
                                            className="aspect-square w-full object-cover"
                                        />
                                        {!isDeleted && (
                                            <div className="absolute inset-0 flex items-center justify-center gap-2 opacity-0 transition-opacity group-hover:opacity-100"
                                                style={{ background: 'color-mix(in srgb, var(--bg-base) 60%, transparent)' }}
                                            >
                                                <button
                                                    type="button"
                                                    onClick={() => setPendingDeleteImage(img)}
                                                    className="inline-flex h-9 w-9 items-center justify-center rounded-lg"
                                                    aria-label="Delete image"
                                                    style={{
                                                        color: 'var(--accent-error)',
                                                        background: 'color-mix(in srgb, var(--accent-error) 15%, var(--bg-surface))',
                                                        border: '1px solid color-mix(in srgb, var(--accent-error) 30%, var(--border-default))',
                                                    }}
                                                >
                                                    <Trash2 size={14} />
                                                </button>
                                            </div>
                                        )}
                                        {img.order !== null && (
                                            <span
                                                className="absolute left-2 top-2 flex h-6 w-6 items-center justify-center rounded-full text-xs font-bold"
                                                style={{
                                                    background: 'var(--bg-base)',
                                                    color: 'var(--text-primary)',
                                                    border: '1px solid var(--border-default)',
                                                }}
                                            >
                                                {img.order}
                                            </span>
                                        )}
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                <DeleteConfirmModal
                    open={pendingDeleteImage !== null}
                    entityLabel="this image"
                    isDeleting={deleteImage.isPending}
                    onConfirm={() => { void handleConfirmDeleteImage(); }}
                    onCancel={() => setPendingDeleteImage(null)}
                />
            </AppLayout>
        </>
    );
}
