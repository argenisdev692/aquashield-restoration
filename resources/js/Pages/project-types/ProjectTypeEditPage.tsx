import * as React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { Layers } from 'lucide-react';
import { useUpdateProjectType } from '@/modules/project-types/hooks/useProjectTypeMutations';
import type { ProjectType, ProjectTypeFormData, ServiceCategoryOption } from '@/modules/project-types/types';
import AppLayout from '@/pages/layouts/AppLayout';
import ProjectTypeForm from './components/ProjectTypeForm';

interface ProjectTypeEditPageProps extends PageProps {
    projectType: ProjectType;
    serviceCategories: ServiceCategoryOption[];
}

export default function ProjectTypeEditPage(): React.JSX.Element {
    const { projectType, serviceCategories } = usePage<ProjectTypeEditPageProps>().props;
    const updateProjectType = useUpdateProjectType();

    async function handleSubmit(data: ProjectTypeFormData): Promise<void> {
        await updateProjectType.mutateAsync({ uuid: projectType.uuid, data });
    }

    return (
        <>
            <Head title={`Edit ${projectType.title}`} />
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
                                Edit Project Type
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Update the current project type information.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <ProjectTypeForm
                            initialData={{
                                title: projectType.title,
                                description: projectType.description ?? '',
                                status: projectType.status,
                                service_category_uuid: projectType.service_category_uuid,
                            }}
                            serviceCategories={serviceCategories}
                            onSubmit={handleSubmit}
                            isSubmitting={updateProjectType.isPending}
                            onCancel={() => router.visit('/project-types')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
