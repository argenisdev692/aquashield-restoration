import { Head, router } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useAllianceCompanyMutations } from '@/modules/alliance-companies/hooks/useAllianceCompanyMutations';
import AllianceCompanyForm from './components/AllianceCompanyForm';
import { AllianceCompany } from '@/modules/alliance-companies/types';
import { ShieldEllipsis } from 'lucide-react';

interface Props {
    AllianceCompany: { data: AllianceCompany };
}

export default function AllianceCompanyEditPage({ AllianceCompany }: Props) {
    const { updateAllianceCompany } = useAllianceCompanyMutations();
    const company = AllianceCompany.data;

    const handleSubmit = async (data: Partial<AllianceCompany>) => {
        await updateAllianceCompany.mutateAsync({ 
            uuid: company.uuid, 
            data 
        });
    };

    return (
        <>
            <Head title={`Edit ${company.alliance_company_name}`} />
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
                            Update the details for <span className="text-(--accent-primary)">{company.alliance_company_name}</span>.
                        </p>
                    </div>

                    <div className="rounded-3xl border border-(--border-default) bg-(--bg-card) shadow-2xl overflow-hidden hover:shadow-blue-500/5 transition-all">
                        <AllianceCompanyForm
                            initialData={company}
                            onSubmit={handleSubmit}
                            isSubmitting={updateAllianceCompany.isPending}
                            onCancel={() => router.visit('/alliance-companies')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
