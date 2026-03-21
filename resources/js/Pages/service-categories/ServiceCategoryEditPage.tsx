import * as React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { FolderTree } from 'lucide-react';
import { useUpdateServiceCategory } from '@/modules/service-categories/hooks/useServiceCategoryMutations';
import type { ServiceCategory, ServiceCategoryFormData } from '@/modules/service-categories/types';
import AppLayout from '@/pages/layouts/AppLayout';
import ServiceCategoryForm from './components/ServiceCategoryForm';

interface ServiceCategoryEditPageProps extends PageProps {
    serviceCategory: ServiceCategory;
}

export default function ServiceCategoryEditPage(): React.JSX.Element {
    const { serviceCategory } = usePage<ServiceCategoryEditPageProps>().props;
    const updateServiceCategory = useUpdateServiceCategory();

    async function handleSubmit(data: ServiceCategoryFormData): Promise<void> {
        await updateServiceCategory.mutateAsync({ uuid: serviceCategory.uuid, data });
    }

    return (
        <>
            <Head title={`Edit ${serviceCategory.category}`} />
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
                            <FolderTree size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                Edit Service Category
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Update the current service category information.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <ServiceCategoryForm
                            initialData={{
                                category: serviceCategory.category,
                                type: serviceCategory.type ?? '',
                            }}
                            onSubmit={handleSubmit}
                            isSubmitting={updateServiceCategory.isPending}
                            onCancel={() => router.visit('/service-categories')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
