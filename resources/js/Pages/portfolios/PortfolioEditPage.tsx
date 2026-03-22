import * as React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { Pencil } from 'lucide-react';
import { useUpdatePortfolio } from '@/modules/portfolios/hooks/usePortfolioMutations';
import type { Portfolio, PortfolioFormData, ProjectTypeOption } from '@/modules/portfolios/types';
import AppLayout from '@/pages/layouts/AppLayout';
import PortfolioForm from './components/PortfolioForm';

interface PortfolioEditPageProps extends PageProps {
    portfolio: Portfolio;
    projectTypes: ProjectTypeOption[];
}

export default function PortfolioEditPage(): React.JSX.Element {
    const { portfolio, projectTypes } = usePage<PortfolioEditPageProps>().props;
    const updatePortfolio = useUpdatePortfolio();

    async function handleSubmit(data: PortfolioFormData): Promise<void> {
        await updatePortfolio.mutateAsync({ uuid: portfolio.uuid, data });
    }

    return (
        <>
            <Head title="Edit Portfolio" />
            <AppLayout>
                <div className="mx-auto flex max-w-2xl flex-col gap-6">
                    <div className="flex items-start gap-4">
                        <div
                            className="flex h-14 w-14 items-center justify-center rounded-2xl"
                            style={{
                                background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                                color: 'var(--accent-primary)',
                            }}
                        >
                            <Pencil size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                Edit Portfolio
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Update the project type association for this portfolio.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <PortfolioForm
                            projectTypes={projectTypes}
                            initialData={{ project_type_uuid: portfolio.project_type_uuid }}
                            onSubmit={handleSubmit}
                            isSubmitting={updatePortfolio.isPending}
                            onCancel={() => router.visit(`/portfolios/${portfolio.uuid}`)}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
