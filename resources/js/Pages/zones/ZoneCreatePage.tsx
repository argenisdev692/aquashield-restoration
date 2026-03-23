import * as React from 'react';
import { Head, router } from '@inertiajs/react';
import { MapPin } from 'lucide-react';
import { useCreateZone } from '@/modules/zones/hooks/useZoneMutations';
import type { ZoneFormData } from '@/modules/zones/types';
import AppLayout from '@/pages/layouts/AppLayout';
import ZoneForm from './components/ZoneForm';

export default function ZoneCreatePage(): React.JSX.Element {
    const createZone = useCreateZone();

    async function handleSubmit(data: ZoneFormData): Promise<void> {
        await createZone.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Zone" />
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
                                Create zone
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Add a new zone to the reference catalog.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <ZoneForm
                            onSubmit={handleSubmit}
                            isSubmitting={createZone.isPending}
                            onCancel={() => router.visit('/zones')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
