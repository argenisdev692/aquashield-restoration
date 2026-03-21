import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { FolderTree, Pencil } from 'lucide-react';
import { formatDateShort } from '@/utils/dateFormatter';
import type { ServiceCategory } from '@/modules/service-categories/types';
import AppLayout from '@/pages/layouts/AppLayout';

interface ServiceCategoryShowPageProps extends PageProps {
    serviceCategory: ServiceCategory;
}

export default function ServiceCategoryShowPage(): React.JSX.Element {
    const { serviceCategory } = usePage<ServiceCategoryShowPageProps>().props;
    const isDeleted = Boolean(serviceCategory.deleted_at);

    return (
        <>
            <Head title={serviceCategory.category} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex items-start justify-between gap-4">
                        <div className="flex items-start gap-4">
                            <div
                                className="flex h-14 w-14 items-center justify-center rounded-2xl"
                                style={{
                                    background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                                    color: 'var(--accent-primary)',
                                }}
                            >
                                <FolderTree size={24} />
                            </div>
                            <div className="space-y-1">
                                <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                    {serviceCategory.category}
                                </h1>
                                <span
                                    className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                                    style={{
                                        color: isDeleted ? 'var(--accent-error)' : 'var(--accent-success)',
                                        background: isDeleted
                                            ? 'color-mix(in srgb, var(--accent-error) 15%, transparent)'
                                            : 'color-mix(in srgb, var(--accent-success) 15%, transparent)',
                                        border: isDeleted
                                            ? '1px solid color-mix(in srgb, var(--accent-error) 25%, transparent)'
                                            : '1px solid color-mix(in srgb, var(--accent-success) 25%, transparent)',
                                    }}
                                >
                                    {isDeleted ? 'Deleted' : 'Active'}
                                </span>
                            </div>
                        </div>

                        {!isDeleted && (
                            <Link
                                href={`/service-categories/${serviceCategory.uuid}/edit`}
                                className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold"
                            >
                                <Pencil size={14} />
                                Edit
                            </Link>
                        )}
                    </div>

                    <div className="card flex flex-col gap-6" style={{ fontFamily: 'var(--font-sans)' }}>
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div className="space-y-1">
                                <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                                    Category Name
                                </p>
                                <p className="text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                                    {serviceCategory.category}
                                </p>
                            </div>

                            <div className="space-y-1">
                                <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                                    Type
                                </p>
                                <p className="text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                                    {serviceCategory.type ?? '—'}
                                </p>
                            </div>

                            <div className="space-y-1">
                                <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                                    Created At
                                </p>
                                <p className="text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                                    {formatDateShort(serviceCategory.created_at)}
                                </p>
                            </div>

                            {serviceCategory.deleted_at && (
                                <div className="space-y-1">
                                    <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                                        Deleted At
                                    </p>
                                    <p className="text-sm font-medium" style={{ color: 'var(--accent-error)' }}>
                                        {formatDateShort(serviceCategory.deleted_at)}
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="flex gap-3">
                        <Link
                            href="/service-categories"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold"
                        >
                            ← Back to Service Categories
                        </Link>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
