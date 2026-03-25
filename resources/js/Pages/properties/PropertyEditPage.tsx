import * as React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { Building2 } from 'lucide-react';
import { useUpdateProperty } from '@/modules/properties/hooks/usePropertyMutations';
import type { Property, PropertyFormData } from '@/modules/properties/types';
import AppLayout from '@/pages/layouts/AppLayout';
import PropertyForm from './components/PropertyForm';

interface PropertyEditPageProps extends PageProps {
    property: Property;
}

export default function PropertyEditPage(): React.JSX.Element {
    const { property } = usePage<PropertyEditPageProps>().props;
    const updateProperty = useUpdateProperty();

    async function handleSubmit(data: PropertyFormData): Promise<void> {
        await updateProperty.mutateAsync({ uuid: property.uuid, data });
    }

    return (
        <>
            <Head title={`Edit ${property.property_address}`} />
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
                        <div className="min-w-0 space-y-1">
                            <h1
                                className="text-3xl font-extrabold"
                                style={{ color: 'var(--text-primary)' }}
                            >
                                Edit property
                            </h1>
                            <p
                                className="truncate text-sm"
                                style={{ color: 'var(--text-muted)' }}
                            >
                                {property.property_address}
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <PropertyForm
                            initialData={property}
                            onSubmit={handleSubmit}
                            isSubmitting={updateProperty.isPending}
                            onCancel={() => router.visit('/properties')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
