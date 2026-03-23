import * as React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { MapPin } from 'lucide-react';
import { useZone } from '@/modules/zones/hooks/useZone';
import { useUpdateZone } from '@/modules/zones/hooks/useZoneMutations';
import type { ZoneFormData } from '@/modules/zones/types';
import AppLayout from '@/pages/layouts/AppLayout';
import ZoneForm from './components/ZoneForm';

interface ZoneEditPageProps extends PageProps {
    uuid: string;
}

export default function ZoneEditPage(): React.JSX.Element {
    const { uuid } = usePage<ZoneEditPageProps>().props;
    const { data: zone, isPending } = useZone(uuid);
    const updateZone = useUpdateZone();

    async function handleSubmit(data: ZoneFormData): Promise<void> {
        await updateZone.mutateAsync({ uuid, data });
    }

    return (
        <>
            <Head title={zone ? `Edit ${zone.zoneName}` : 'Edit Zone'} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">

                    <div className="flex items-start gap-4">
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
                                {zone ? `Edit ${zone.zoneName}` : 'Edit Zone'}
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Update the zone information below.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        {isPending ? (
                            <div className="flex items-center justify-center py-16">
                                <div
                                    className="h-8 w-8 animate-spin rounded-full border-2 border-t-transparent"
                                    style={{ borderColor: 'var(--accent-primary)', borderTopColor: 'transparent' }}
                                />
                            </div>
                        ) : zone ? (
                            <ZoneForm
                                initialData={{
                                    zone_name:   zone.zoneName,
                                    zone_type:   zone.zoneType,
                                    code:        zone.code        ?? '',
                                    description: zone.description ?? '',
                                    user_id:     zone.userId,
                                }}
                                onSubmit={handleSubmit}
                                isSubmitting={updateZone.isPending}
                                onCancel={() => router.visit(`/zones/${uuid}`)}
                            />
                        ) : (
                            <div className="flex flex-col items-center justify-center gap-3 py-16">
                                <MapPin size={32} style={{ color: 'var(--text-disabled)' }} />
                                <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                    Zone not found.
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
