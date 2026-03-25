import * as React from 'react';
import { Head, router } from '@inertiajs/react';
import { Building2 } from 'lucide-react';
import { useCreateProperty } from '@/modules/properties/hooks/usePropertyMutations';
import type { PropertyFormData } from '@/modules/properties/types';
import AppLayout from '@/pages/layouts/AppLayout';
import PropertyForm from './components/PropertyForm';

export default function PropertyCreatePage(): React.JSX.Element {
    const createProperty = useCreateProperty();

    async function handleSubmit(data: PropertyFormData): Promise<void> {
        await createProperty.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Property" />
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
                            <Building2 size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1
                                className="text-3xl font-extrabold"
                                style={{ color: 'var(--text-primary)' }}
                            >
                                Create property
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Add a new property record to your CRM.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <PropertyForm
                            onSubmit={handleSubmit}
                            isSubmitting={createProperty.isPending}
                            onCancel={() => router.visit('/properties')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
