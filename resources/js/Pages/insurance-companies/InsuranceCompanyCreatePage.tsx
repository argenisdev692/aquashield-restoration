import * as React from 'react';
import axios from 'axios';
import { Head, router } from '@inertiajs/react';
import { ShieldPlus } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useInsuranceCompanyMutations } from '@/modules/insurance-companies/hooks/useInsuranceCompanyMutations';
import type {
    InsuranceCompanyFormData,
    InsuranceCompanyFormErrors,
} from '@/modules/insurance-companies/types';
import InsuranceCompanyForm from './components/InsuranceCompanyForm';

const INITIAL_FORM_DATA: InsuranceCompanyFormData = {
    insurance_company_name: '',
    address: '',
    address_2: '',
    phone: '',
    email: '',
    website: '',
};

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

        if (typeof firstMessage === 'string' && firstMessage.length > 0 && field in INITIAL_FORM_DATA) {
            carry[field as keyof InsuranceCompanyFormData] = firstMessage;
        }

        return carry;
    }, {});
}

export default function InsuranceCompanyCreatePage(): React.JSX.Element {
    const [form, setForm] = React.useState<InsuranceCompanyFormData>(INITIAL_FORM_DATA);
    const [errors, setErrors] = React.useState<InsuranceCompanyFormErrors>({});
    const { createInsuranceCompany } = useInsuranceCompanyMutations();

    function handleChange<K extends keyof InsuranceCompanyFormData>(field: K, value: InsuranceCompanyFormData[K]): void {
        setForm((previous) => ({ ...previous, [field]: value }));
        setErrors((previous) => ({ ...previous, [field]: undefined }));
    }

    async function handleSubmit(): Promise<void> {
        setErrors({});

        try {
            await createInsuranceCompany.mutateAsync(form);
            router.visit('/insurance-companies');
        } catch (error) {
            setErrors(extractValidationErrors(error));
        }
    }

    return (
        <>
            <Head title="Create Insurance Company" />
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
                                <ShieldPlus size={24} />
                            </div>
                            <h1 className="text-3xl font-extrabold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                                Create Insurance Company
                            </h1>
                        </div>
                        <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                            Register a new insurance carrier for the CRM.
                        </p>
                    </div>

                    <div className="overflow-hidden rounded-3xl border shadow-xl" style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}>
                        <InsuranceCompanyForm
                            data={form}
                            errors={errors}
                            onChange={handleChange}
                            onSubmit={handleSubmit}
                            isSubmitting={createInsuranceCompany.isPending}
                            onCancel={() => router.visit('/insurance-companies')}
                            submitLabel="Create Company"
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
