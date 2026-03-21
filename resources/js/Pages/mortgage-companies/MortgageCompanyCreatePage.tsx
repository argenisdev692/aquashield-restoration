import * as React from 'react';
import { Head, router } from '@inertiajs/react';
import { Building2 } from 'lucide-react';
import { useCreateMortgageCompany } from '@/modules/mortgage-companies/hooks/useMortgageCompanyMutations';
import type { MortgageCompanyFormData } from '@/modules/mortgage-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';
import MortgageCompanyForm from './components/MortgageCompanyForm';

export default function MortgageCompanyCreatePage(): React.JSX.Element {
    const createMortgageCompany = useCreateMortgageCompany();

    async function handleSubmit(data: MortgageCompanyFormData): Promise<void> {
        await createMortgageCompany.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Mortgage Company" />
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
                                Create mortgage company
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Add a new mortgage company record to your companies area.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <MortgageCompanyForm
                            onSubmit={handleSubmit}
                            isSubmitting={createMortgageCompany.isPending}
                            onCancel={() => router.visit('/mortgage-companies')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
