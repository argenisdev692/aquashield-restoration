import * as React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { Briefcase } from 'lucide-react';
import { useCreatePortfolio } from '@/modules/portfolios/hooks/usePortfolioMutations';
import type { PortfolioFormData, ProjectTypeOption } from '@/modules/portfolios/types';
import AppLayout from '@/pages/layouts/AppLayout';
import PortfolioForm from './components/PortfolioForm';

interface PortfolioCreatePageProps extends PageProps {
    projectTypes: ProjectTypeOption[];
}

export default function PortfolioCreatePage(): React.JSX.Element {
    const { projectTypes } = usePage<PortfolioCreatePageProps>().props;
    const createPortfolio = useCreatePortfolio();

    async function handleSubmit(data: PortfolioFormData): Promise<void> {
        await createPortfolio.mutateAsync(data);
    }

    return (
        <>
            <Head title="New Portfolio" />
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
                            <Briefcase size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                New Portfolio
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Create a new portfolio entry and add project images.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <PortfolioForm
                            projectTypes={projectTypes}
                            onSubmit={handleSubmit}
                            isSubmitting={createPortfolio.isPending}
                            onCancel={() => router.visit('/portfolios')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
