import * as React from 'react';
import axios from 'axios';
import { Head, router } from '@inertiajs/react';
import { Building2 } from 'lucide-react';
import { usePublicCompanyMutations } from '@/modules/public-companies/hooks/usePublicCompanyMutations';
import type {
    PublicCompany,
    PublicCompanyFormData,
    PublicCompanyFormErrors,
    UpdatePublicCompanyPayload,
} from '@/modules/public-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';
import PublicCompanyForm from './components/PublicCompanyForm';

interface Props {
    publicCompany: { data: PublicCompany };
}

function buildFormData(company: PublicCompany): PublicCompanyFormData {
    return {
        public_company_name: company.public_company_name,
        address: company.address ?? '',
        phone: company.phone ?? '',
        email: company.email ?? '',
        website: company.website ?? '',
        unit: company.unit || company.address_2 || '',
    };
}

function buildPayload(form: PublicCompanyFormData): UpdatePublicCompanyPayload {
    return {
        ...form,
        address_2: form.unit,
    };
}

function extractValidationErrors(error: unknown): PublicCompanyFormErrors {
    if (!axios.isAxiosError(error)) {
        return {};
    }

    const responseData = error.response?.data as { errors?: Record<string, string[]> } | undefined;

    if (!responseData?.errors) {
        return {};
    }

    return Object.entries(responseData.errors).reduce<PublicCompanyFormErrors>((carry, [field, messages]) => {
        const firstMessage = messages[0];

        if (typeof firstMessage !== 'string' || firstMessage.length === 0) {
            return carry;
        }

        if (field === 'address_2') {
            carry.unit = firstMessage;
            return carry;
        }

        carry[field as keyof PublicCompanyFormData] = firstMessage;

        return carry;
    }, {});
}

export default function PublicCompanyEditPage({ publicCompany }: Props): React.JSX.Element {
    const company = publicCompany.data;
    const [form, setForm] = React.useState<PublicCompanyFormData>(() => buildFormData(company));
    const [errors, setErrors] = React.useState<PublicCompanyFormErrors>({});
    const { updatePublicCompany } = usePublicCompanyMutations();

    function handleChange<K extends keyof PublicCompanyFormData>(field: K, value: PublicCompanyFormData[K]): void {
        setForm((previous) => ({ ...previous, [field]: value }));
        setErrors((previous) => ({ ...previous, [field]: undefined }));
    }

    async function handleSubmit(): Promise<void> {
        setErrors({});

        try {
            await updatePublicCompany.mutateAsync({
                uuid: company.uuid,
                payload: buildPayload(form),
            });
            router.visit('/public-companies');
        } catch (error) {
            setErrors(extractValidationErrors(error));
        }
    }

    return (
        <>
            <Head title={`Edit ${company.public_company_name}`} />
            <AppLayout>
                <div className="mx-auto max-w-4xl space-y-8">
                    <div className="flex flex-col gap-2">
                        <div className="flex items-center gap-3">
                            <div
                                className="flex h-12 w-12 items-center justify-center rounded-2xl"
                                style={{
                                    background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                                    color: 'var(--accent-primary)',
                                }}
                            >
                                <Building2 size={24} />
                            </div>
                            <h1 className="text-3xl font-extrabold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                                Edit Public Company
                            </h1>
                        </div>
                        <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                            Update the information for <span style={{ color: 'var(--accent-primary)' }}>{company.public_company_name}</span>.
                        </p>
                    </div>

                    <div className="overflow-hidden rounded-3xl border shadow-xl" style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}>
                        <PublicCompanyForm
                            data={form}
                            errors={errors}
                            onChange={handleChange}
                            onSubmit={() => {
                                void handleSubmit();
                            }}
                            isSubmitting={updatePublicCompany.isPending}
                            onCancel={() => router.visit('/public-companies')}
                            submitLabel="Update Company"
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
