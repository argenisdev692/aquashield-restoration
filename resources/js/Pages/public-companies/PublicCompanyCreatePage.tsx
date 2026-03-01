import { Head, router } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { usePublicCompanyMutations } from '@/modules/public-companies/hooks/usePublicCompanyMutations';
import PublicCompanyForm from './components/PublicCompanyForm';
import { ShieldPlus } from 'lucide-react';

export default function PublicCompanyCreatePage() {
    const { createPublicCompany } = usePublicCompanyMutations();

    const handleSubmit = async (data: any) => {
        await createPublicCompany.mutateAsync(data);
    };

    return (
        <>
            <Head title="Create Public Company" />
            <AppLayout>
                <div className="max-w-4xl mx-auto space-y-8 animate-in fade-in duration-500">
                    <div className="flex flex-col gap-2">
                        <div className="flex items-center gap-3">
                            <div className="p-3 rounded-2xl bg-(--accent-primary)/10 text-(--accent-primary) shadow-sm">
                                <ShieldPlus size={24} />
                            </div>
                            <h1 className="text-3xl font-extrabold tracking-tight text-(--text-primary)">
                                Create New Carrier
                            </h1>
                        </div>
                        <p className="text-sm text-(--text-muted) font-medium ml-14">
                            Register a new Public company to manage claims and adjusters.
                        </p>
                    </div>

                    <div className="rounded-3xl border border-(--border-default) bg-(--bg-card) shadow-2xl overflow-hidden hover:shadow-blue-500/5 transition-all">
                        <PublicCompanyForm
                            onSubmit={handleSubmit}
                            isSubmitting={createPublicCompany.isPending}
                            onCancel={() => router.visit('/public-companies')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
