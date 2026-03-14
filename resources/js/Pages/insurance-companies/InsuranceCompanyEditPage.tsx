import { Head, router } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useInsuranceCompanyMutations } from '@/modules/insurance-companies/hooks/useInsuranceCompanyMutations';
import InsuranceCompanyForm from './components/InsuranceCompanyForm';
import { InsuranceCompany } from '@/modules/insurance-companies/types';
import { ShieldEllipsis } from 'lucide-react';

interface Props {
    insuranceCompany: { data: InsuranceCompany };
}

export default function InsuranceCompanyEditPage({ insuranceCompany }: Props) {
    const { updateInsuranceCompany } = useInsuranceCompanyMutations();
    const company = insuranceCompany.data;

    const handleSubmit = async (data: Partial<InsuranceCompany>) => {
        await updateInsuranceCompany.mutateAsync({ 
            uuid: company.uuid, 
            data 
        });
    };

    return (
        <>
            <Head title={`Edit ${company.insurance_company_name}`} />
            <AppLayout>
                <div className="max-w-4xl mx-auto space-y-8 animate-in fade-in duration-500">
                    <div className="flex flex-col gap-2">
                        <div className="flex items-center gap-3">
                            <div className="p-3 rounded-2xl bg-(--accent-primary)/10 text-(--accent-primary) shadow-sm">
                                <ShieldEllipsis size={24} />
                            </div>
                            <h1 className="text-3xl font-extrabold tracking-tight text-(--text-primary)">
                                Edit Carrier Information
                            </h1>
                        </div>
                        <p className="text-sm text-(--text-muted) font-medium ml-14">
                            Update the details for <span className="text-(--accent-primary)">{company.insurance_company_name}</span>.
                        </p>
                    </div>

                    <div className="rounded-3xl border border-(--border-default) bg-(--bg-card) shadow-2xl overflow-hidden hover:shadow-blue-500/5 transition-all">
                        <InsuranceCompanyForm
                            initialData={company}
                            onSubmit={handleSubmit}
                            isSubmitting={updateInsuranceCompany.isPending}
                            onCancel={() => router.visit('/insurance-companies')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
