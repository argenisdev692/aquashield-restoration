import { Head, router } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useMortgageCompanyMutations } from '@/modules/mortgage-companies/hooks/useMortgageCompanyMutations';
import MortgageCompanyForm from './components/MortgageCompanyForm';
import type { MortgageCompanyDetail } from '@/types/api';
import { Building2 } from 'lucide-react';

interface Props {
    mortgageCompany: { data: MortgageCompanyDetail };
}

export default function MortgageCompanyEditPage({ mortgageCompany }: Props) {
    const { updateMortgageCompany } = useMortgageCompanyMutations();
    const company = mortgageCompany.data;

    const handleSubmit = async (data: any) => {
        await updateMortgageCompany.mutateAsync({ 
            uuid: company.uuid, 
            data 
        });
    };

    return (
        <>
            <Head title={`Edit ${company.mortgageCompanyName}`} />
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
                                <Building2 size={24} />
                            </div>
                            <h1 
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}
                            >
                                Edit Mortgage Company
                            </h1>
                        </div>
                        <p 
                            className="text-sm font-medium ml-14"
                            style={{ color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}
                        >
                            Update the details for <span style={{ color: 'var(--accent-primary)' }}>{company.mortgageCompanyName}</span>.
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
                            initialData={company}
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
