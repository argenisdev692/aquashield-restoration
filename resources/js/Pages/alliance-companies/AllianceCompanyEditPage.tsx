import { Head, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { Building2 } from 'lucide-react';
import { useUpdateAllianceCompany } from '@/modules/alliance-companies/hooks/useAllianceCompanyMutations';
import type {
    AllianceCompany,
    AllianceCompanyFormData,
} from '@/modules/alliance-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';
import AllianceCompanyForm from './components/AllianceCompanyForm';

interface AllianceCompanyEditPageProps extends PageProps {
    allianceCompany: AllianceCompany;
}

export default function AllianceCompanyEditPage(): React.JSX.Element {
    const { allianceCompany } = usePage<AllianceCompanyEditPageProps>().props;
    const updateAllianceCompany = useUpdateAllianceCompany();

    async function handleSubmit(data: AllianceCompanyFormData): Promise<void> {
        await updateAllianceCompany.mutateAsync({
            uuid: allianceCompany.uuid,
            data,
        });
    }

    return (
        <>
            <Head title={`Edit ${allianceCompany.alliance_company_name}`} />
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
                                Edit alliance company
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Update the current alliance company information.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <AllianceCompanyForm
                            initialData={allianceCompany}
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
