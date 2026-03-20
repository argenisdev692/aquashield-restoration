import * as React from 'react';
import axios from 'axios';
import { Head, router } from '@inertiajs/react';
import { ShieldEllipsis } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useInsuranceCompanyMutations } from '@/modules/insurance-companies/hooks/useInsuranceCompanyMutations';
import type {
    InsuranceCompany,
    InsuranceCompanyFormData,
    InsuranceCompanyFormErrors,
} from '@/modules/insurance-companies/types';
import InsuranceCompanyForm from './components/InsuranceCompanyForm';

interface Props {
    insuranceCompany: { data: InsuranceCompany };
}

function buildFormData(company: InsuranceCompany): InsuranceCompanyFormData {
    return {
        insurance_company_name: company.insurance_company_name,
        address: company.address ?? '',
        address_2: company.address_2 ?? '',
        phone: company.phone ?? '',
        email: company.email ?? '',
        website: company.website ?? '',
    };
}

function extractValidationErrors(error: unknown): InsuranceCompanyFormErrors {
    if (!axios.isAxiosError(error)) {
        return {};
    }

    const responseData = error.response?.data as { errors?: Record<string, string[]> } | undefined;

    if (!responseData?.errors) {
        return {};
    }

    return Object.entries(responseData.errors).reduce<InsuranceCompanyFormErrors>((carry, [field, messages]) => {
        const firstMessage = messages[0];

        if (typeof firstMessage === 'string' && firstMessage.length > 0) {
            carry[field as keyof InsuranceCompanyFormData] = firstMessage;
        }

        return carry;
    }, {});
}

export default function InsuranceCompanyEditPage({ insuranceCompany }: Props): React.JSX.Element {
    const company = insuranceCompany.data;
    const [form, setForm] = React.useState<InsuranceCompanyFormData>(() => buildFormData(company));
    const [errors, setErrors] = React.useState<InsuranceCompanyFormErrors>({});
    const { updateInsuranceCompany } = useInsuranceCompanyMutations();

    function handleChange<K extends keyof InsuranceCompanyFormData>(field: K, value: InsuranceCompanyFormData[K]): void {
        setForm((previous) => ({ ...previous, [field]: value }));
        setErrors((previous) => ({ ...previous, [field]: undefined }));
    }

    async function handleSubmit(): Promise<void> {
        setErrors({});

        try {
            await updateInsuranceCompany.mutateAsync({ uuid: company.uuid, payload: form });
            router.visit('/insurance-companies');
        } catch (error) {
            setErrors(extractValidationErrors(error));
        }
    }

    return (
        <>
            <Head title={`Edit ${company.insurance_company_name}`} />
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
                                <ShieldEllipsis size={24} />
                            </div>
                            <h1 className="text-3xl font-extrabold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                                Edit Insurance Company
                            </h1>
                        </div>
                        <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                            Update the information for <span style={{ color: 'var(--accent-primary)' }}>{company.insurance_company_name}</span>.
                        </p>
                    </div>

                    <div className="overflow-hidden rounded-3xl border shadow-xl" style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}>
                        <InsuranceCompanyForm
                            data={form}
                            errors={errors}
                            onChange={handleChange}
                            onSubmit={handleSubmit}
                            isSubmitting={updateInsuranceCompany.isPending}
                            onCancel={() => router.visit('/insurance-companies')}
                            submitLabel="Update Company"
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
