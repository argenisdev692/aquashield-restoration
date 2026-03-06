import { Head, router } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useMortgageCompanyMutations } from '@/modules/mortgage-companies/hooks/useMortgageCompanyMutations';
import type { MortgageCompanyFormData } from '@/modules/mortgage-companies/types';
import MortgageCompanyForm from './components/MortgageCompanyForm';
import { Plus } from 'lucide-react';

export default function MortgageCompanyCreatePage(): React.JSX.Element {
    const { createMortgageCompany } = useMortgageCompanyMutations();

    const handleSubmit = async (data: MortgageCompanyFormData): Promise<void> => {
        await createMortgageCompany.mutateAsync(data);
    };

    return (
        <>
            <Head title="Create Mortgage Company" />
            <AppLayout>
                <div className="max-w-4xl mx-auto space-y-8 animate-in fade-in duration-500">
                    <div className="flex flex-col gap-2">
                        <div className="flex items-center gap-3">
                            <div 
                                className="p-3 rounded-2xl shadow-sm"
                                style={{
                                    background: 'color-mix(in srgb, var(--accent-primary) 10%, transparent)',
                                    color: 'var(--accent-primary)',
                                }}
                            >
                                <Plus size={24} />
                            </div>
                            <h1 
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}
                            >
                                Create New Mortgage Company
                            </h1>
                        </div>
                        <p 
                            className="text-sm font-medium ml-14"
                            style={{ color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}
                        >
                            Register a new mortgage lender to manage loan information.
                        </p>
                    </div>

                    <div 
                        className="rounded-3xl overflow-hidden shadow-2xl transition-all"
                        style={{
                            border: '1px solid var(--border-default)',
                            background: 'var(--bg-card)',
                        }}
                    >
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
