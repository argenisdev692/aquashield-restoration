import { Head, router } from '@inertiajs/react';
import { Building2 } from 'lucide-react';
import { useCreateAllianceCompany } from '@/modules/alliance-companies/hooks/useAllianceCompanyMutations';
import type { AllianceCompanyFormData } from '@/modules/alliance-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';
import AllianceCompanyForm from './components/AllianceCompanyForm';

export default function AllianceCompanyCreatePage(): React.JSX.Element {
    const createAllianceCompany = useCreateAllianceCompany();

    async function handleSubmit(data: AllianceCompanyFormData): Promise<void> {
        await createAllianceCompany.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Alliance Company" />
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
                                Create alliance company
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Add a new alliance company record to your companies area.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <AllianceCompanyForm
                            onSubmit={handleSubmit}
                            isSubmitting={createAllianceCompany.isPending}
                            onCancel={() => router.visit('/alliance-companies')}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
