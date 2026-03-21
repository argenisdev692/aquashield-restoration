import * as React from 'react';
import axios from 'axios';
import { Head, router } from '@inertiajs/react';
import { Building2 } from 'lucide-react';
import { usePublicCompanyMutations } from '@/modules/public-companies/hooks/usePublicCompanyMutations';
import type {
    CreatePublicCompanyPayload,
    PublicCompanyFormData,
    PublicCompanyFormErrors,
} from '@/modules/public-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';
import PublicCompanyForm from './components/PublicCompanyForm';

const INITIAL_FORM_DATA: PublicCompanyFormData = {
    public_company_name: '',
    address: '',
    phone: '',
    email: '',
    website: '',
    unit: '',
};

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

        if (field in INITIAL_FORM_DATA) {
            carry[field as keyof PublicCompanyFormData] = firstMessage;
        }

        return carry;
    }, {});
}

function buildPayload(form: PublicCompanyFormData): CreatePublicCompanyPayload {
    return {
        ...form,
        address_2: form.unit,
    };
}

export default function PublicCompanyCreatePage(): React.JSX.Element {
    const [form, setForm] = React.useState<PublicCompanyFormData>(INITIAL_FORM_DATA);
    const [errors, setErrors] = React.useState<PublicCompanyFormErrors>({});
    const { createPublicCompany } = usePublicCompanyMutations();

    function handleChange<K extends keyof PublicCompanyFormData>(field: K, value: PublicCompanyFormData[K]): void {
        setForm((previous) => ({ ...previous, [field]: value }));
        setErrors((previous) => ({ ...previous, [field]: undefined }));
    }

    async function handleSubmit(): Promise<void> {
        setErrors({});

        try {
            await createPublicCompany.mutateAsync(buildPayload(form));
            router.visit('/public-companies');
        } catch (error) {
            setErrors(extractValidationErrors(error));
        }
    }

    return (
        <>
            <Head title="Create Public Company" />
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
                                Create Public Company
                            </h1>
                        </div>
                        <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                            Register a new public company for the CRM.
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
                            isSubmitting={createPublicCompany.isPending}
                            onCancel={() => router.visit('/public-companies')}
                            submitLabel="Create Company"
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
