import * as React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { Layers } from 'lucide-react';
import { useCreateProjectType } from '@/modules/project-types/hooks/useProjectTypeMutations';
import type { ProjectTypeFormData, ServiceCategoryOption } from '@/modules/project-types/types';
import AppLayout from '@/pages/layouts/AppLayout';
import ProjectTypeForm from './components/ProjectTypeForm';

interface ProjectTypeCreatePageProps extends PageProps {
    serviceCategories: ServiceCategoryOption[];
}

export default function ProjectTypeCreatePage(): React.JSX.Element {
    const { serviceCategories } = usePage<ProjectTypeCreatePageProps>().props;
    const createProjectType = useCreateProjectType();

    async function handleSubmit(data: ProjectTypeFormData): Promise<void> {
        await createProjectType.mutateAsync(data);
    }

    return (
        <>
            <Head title="New Project Type" />
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
                            <Layers size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                New Project Type
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Define a new type of project within a service category.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <ProjectTypeForm
                            serviceCategories={serviceCategories}
                            onSubmit={handleSubmit}
                            isSubmitting={createProjectType.isPending}
                            onCancel={() => router.visit('/project-types')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
