import * as React from 'react';
import { Head, router } from '@inertiajs/react';
import { FolderTree } from 'lucide-react';
import { useCreateServiceCategory } from '@/modules/service-categories/hooks/useServiceCategoryMutations';
import type { ServiceCategoryFormData } from '@/modules/service-categories/types';
import AppLayout from '@/pages/layouts/AppLayout';
import ServiceCategoryForm from './components/ServiceCategoryForm';

export default function ServiceCategoryCreatePage(): React.JSX.Element {
    const createServiceCategory = useCreateServiceCategory();

    async function handleSubmit(data: ServiceCategoryFormData): Promise<void> {
        await createServiceCategory.mutateAsync(data);
    }

    return (
        <>
            <Head title="New Service Category" />
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
                                New Service Category
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Add a new service category to organize project types.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <ServiceCategoryForm
                            onSubmit={handleSubmit}
                            isSubmitting={createServiceCategory.isPending}
                            onCancel={() => router.visit('/service-categories')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
