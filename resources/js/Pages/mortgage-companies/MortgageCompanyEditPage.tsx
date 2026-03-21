import * as React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { Building2 } from 'lucide-react';
import { useUpdateMortgageCompany } from '@/modules/mortgage-companies/hooks/useMortgageCompanyMutations';
import type { MortgageCompany, MortgageCompanyFormData } from '@/modules/mortgage-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';
import MortgageCompanyForm from './components/MortgageCompanyForm';

interface MortgageCompanyEditPageProps extends PageProps {
    mortgageCompany: MortgageCompany;
}

export default function MortgageCompanyEditPage(): React.JSX.Element {
    const { mortgageCompany } = usePage<MortgageCompanyEditPageProps>().props;
    const updateMortgageCompany = useUpdateMortgageCompany();

    async function handleSubmit(data: MortgageCompanyFormData): Promise<void> {
        await updateMortgageCompany.mutateAsync({
            uuid: mortgageCompany.uuid,
            data,
        });
    }

    return (
        <>
            <Head title={`Edit ${mortgageCompany.mortgage_company_name}`} />
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
                            <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                Edit mortgage company
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Update the current mortgage company information.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <MortgageCompanyForm
                            initialData={mortgageCompany}
                            onSubmit={handleSubmit}
                            isSubmitting={updateMortgageCompany.isPending}
                            onCancel={() => router.visit('/mortgage-companies')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
