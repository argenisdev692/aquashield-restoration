import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { Layers, Pencil } from 'lucide-react';
import { formatDateShort } from '@/utils/dateFormatter';
import type { ProjectType } from '@/modules/project-types/types';
import AppLayout from '@/pages/layouts/AppLayout';

interface ProjectTypeShowPageProps extends PageProps {
    projectType: ProjectType;
}

export default function ProjectTypeShowPage(): React.JSX.Element {
    const { projectType } = usePage<ProjectTypeShowPageProps>().props;
    const isDeleted = Boolean(projectType.deleted_at);
    const statusAccent =
        projectType.status === 'active' ? 'var(--accent-success)' : 'var(--accent-warning)';

    return (
        <>
            <Head title={projectType.title} />
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
                                <Layers size={24} />
                            </div>
                            <div className="space-y-1">
                                <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                    {projectType.title}
                                </h1>
                                <div className="flex items-center gap-2">
                                    <span
                                        className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                                        style={{
                                            color: statusAccent,
                                            background: `color-mix(in srgb, ${statusAccent} 15%, transparent)`,
                                            border: `1px solid color-mix(in srgb, ${statusAccent} 25%, transparent)`,
                                        }}
                                    >
                                        {projectType.status}
                                    </span>
                                    {isDeleted && (
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
                                    )}
                                </div>
                            </div>
                        </div>

                        {!isDeleted && (
                            <Link
                                href={`/project-types/${projectType.uuid}/edit`}
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
                                    Title
                                </p>
                                <p className="text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                                    {projectType.title}
                                </p>
                            </div>

                            <div className="space-y-1">
                                <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                                    Service Category
                                </p>
                                <p className="text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                                    {projectType.service_category_name ?? '—'}
                                </p>
                            </div>

                            {projectType.description && (
                                <div className="col-span-full space-y-1">
                                    <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                                        Description
                                    </p>
                                    <p className="text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                                        {projectType.description}
                                    </p>
                                </div>
                            )}

                            <div className="space-y-1">
                                <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                                    Created At
                                </p>
                                <p className="text-sm font-medium" style={{ color: 'var(--text-primary)' }}>
                                    {formatDateShort(projectType.created_at)}
                                </p>
                            </div>

                            {projectType.deleted_at && (
                                <div className="space-y-1">
                                    <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                                        Deleted At
                                    </p>
                                    <p className="text-sm font-medium" style={{ color: 'var(--accent-error)' }}>
                                        {formatDateShort(projectType.deleted_at)}
                                    </p>
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="flex gap-3">
                        <Link
                            href="/project-types"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold"
                        >
                            ← Back to Project Types
                        </Link>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
