import { Head, router } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { usePublicCompanyMutations } from '@/modules/public-companies/hooks/usePublicCompanyMutations';
import PublicCompanyForm from './components/PublicCompanyForm';
import { PublicCompany } from '@/modules/public-companies/types';
import { ShieldEllipsis } from 'lucide-react';

interface Props {
    PublicCompany: { data: PublicCompany };
}

export default function PublicCompanyEditPage({ PublicCompany }: Props) {
    const { updatePublicCompany } = usePublicCompanyMutations();
    const company = PublicCompany.data;

    const handleSubmit = async (data: any) => {
        await updatePublicCompany.mutateAsync({ 
            uuid: company.uuid, 
            data 
        });
    };

    return (
        <>
            <Head title={`Edit ${company.public_company_name}`} />
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
                            Update the details for <span className="text-(--accent-primary)">{company.public_company_name}</span>.
                        </p>
                    </div>

                    <div className="rounded-3xl border border-(--border-default) bg-(--bg-card) shadow-2xl overflow-hidden hover:shadow-blue-500/5 transition-all">
                        <PublicCompanyForm
                            initialData={company}
                            onSubmit={handleSubmit}
                            isSubmitting={updatePublicCompany.isPending}
                            onCancel={() => router.visit('/public-companies')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
