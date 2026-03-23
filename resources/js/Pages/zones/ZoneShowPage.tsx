import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { ArrowLeft, MapPin, Pencil } from 'lucide-react';
import { useZone } from '@/modules/zones/hooks/useZone';
import { ZONE_TYPE_LABELS } from '@/modules/zones/types';
import AppLayout from '@/pages/layouts/AppLayout';

interface ZoneShowPageProps extends PageProps {
    uuid: string;
}

export default function ZoneShowPage(): React.JSX.Element {
    const { uuid } = usePage<ZoneShowPageProps>().props;
    const { data: zone, isPending, isError } = useZone(uuid);

    return (
        <>
            <Head title={zone?.zoneName ?? 'Zone Detail'} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">

                    {/* Back / Edit actions */}
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href="/zones"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                        >
                            <ArrowLeft size={16} />
                            <span>Back to zones</span>
                        </Link>

                        {zone && !zone.deletedAt ? (
                            <Link
                                href={`/zones/${zone.uuid}/edit`}
                                className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                            >
                                <Pencil size={16} />
                                <span>Edit zone</span>
                            </Link>
                        ) : null}
                    </div>

                    {/* Loading state */}
                    {isPending ? (
                        <div className="card flex items-center justify-center py-16">
                            <div
                                className="h-8 w-8 animate-spin rounded-full border-2 border-t-transparent"
                                style={{ borderColor: 'var(--accent-primary)', borderTopColor: 'transparent' }}
                            />
                        </div>
                    ) : isError || !zone ? (
                        <div className="card flex flex-col items-center justify-center gap-3 py-16">
                            <MapPin size={32} style={{ color: 'var(--text-disabled)' }} />
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Zone not found or an error occurred.
                            </p>
                        </div>
                    ) : (
                        <div className="card overflow-hidden p-0">

                            {/* Header */}
                            <div
                                className="flex items-start gap-4 border-b px-6 py-6"
                                style={{ borderColor: 'var(--border-default)' }}
                            >
                                <div
                                    className="flex h-14 w-14 items-center justify-center rounded-2xl"
                                    style={{
                                        background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                                        color: 'var(--accent-primary)',
                                    }}
                                >
                                    <MapPin size={26} />
                                </div>
                                <div className="space-y-1">
                                    <h1
                                        className="text-3xl font-extrabold"
                                        style={{ color: 'var(--text-primary)', letterSpacing: '-0.5px' }}
                                    >
                                        {zone.zoneName}
                                    </h1>
                                    <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                        Zone details
                                    </p>
                                </div>

                                {/* Deleted badge */}
                                {zone.deletedAt ? (
                                    <span
                                        className="ml-auto inline-flex rounded-full px-3 py-1 text-xs font-semibold"
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
                                        className="ml-auto inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                                        style={{
                                            color: 'var(--accent-success)',
                                            background: 'color-mix(in srgb, var(--accent-success) 15%, transparent)',
                                            border: '1px solid color-mix(in srgb, var(--accent-success) 25%, transparent)',
                                        }}
                                    >
                                        Active
                                    </span>
                                )}
                            </div>

                            {/* Fields grid */}
                            <div className="grid gap-6 px-6 py-6 md:grid-cols-2">

                                <div className="space-y-2">
                                    <p
                                        className="text-xs font-semibold uppercase tracking-[1.5px]"
                                        style={{ color: 'var(--text-disabled)' }}
                                    >
                                        Zone Type
                                    </p>
                                    <p className="text-base font-semibold" style={{ color: 'var(--text-primary)' }}>
                                        {ZONE_TYPE_LABELS[zone.zoneType]}
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <p
                                        className="text-xs font-semibold uppercase tracking-[1.5px]"
                                        style={{ color: 'var(--text-disabled)' }}
                                    >
                                        Code
                                    </p>
                                    <p
                                        className="font-mono text-sm font-semibold"
                                        style={{ color: zone.code ? 'var(--accent-info)' : 'var(--text-disabled)' }}
                                    >
                                        {zone.code ?? '—'}
                                    </p>
                                </div>

                                <div className="space-y-2 md:col-span-2">
                                    <p
                                        className="text-xs font-semibold uppercase tracking-[1.5px]"
                                        style={{ color: 'var(--text-disabled)' }}
                                    >
                                        Description
                                    </p>
                                    <p
                                        className="text-sm leading-7"
                                        style={{ color: 'var(--text-secondary)' }}
                                    >
                                        {zone.description ?? '—'}
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <p
                                        className="text-xs font-semibold uppercase tracking-[1.5px]"
                                        style={{ color: 'var(--text-disabled)' }}
                                    >
                                        Created at
                                    </p>
                                    <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                        {new Date(zone.createdAt).toLocaleString()}
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <p
                                        className="text-xs font-semibold uppercase tracking-[1.5px]"
                                        style={{ color: 'var(--text-disabled)' }}
                                    >
                                        Updated at
                                    </p>
                                    <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                        {new Date(zone.updatedAt).toLocaleString()}
                                    </p>
                                </div>

                                {zone.deletedAt ? (
                                    <div className="space-y-2">
                                        <p
                                            className="text-xs font-semibold uppercase tracking-[1.5px]"
                                            style={{ color: 'var(--text-disabled)' }}
                                        >
                                            Deleted at
                                        </p>
                                        <p className="text-sm" style={{ color: 'var(--accent-error)' }}>
                                            {new Date(zone.deletedAt).toLocaleString()}
                                        </p>
                                    </div>
                                ) : null}
                            </div>
                        </div>
                    )}
                </div>
            </AppLayout>
        </>
    );
}
