import * as React from 'react';
import { Plus, Save } from 'lucide-react';
import { formatUsPhoneInput, normalizeUsPhoneForPayload } from '@/common/helpers/phone';
import { PremiumField } from '@/shadcn/PremiumField';
import type { Customer, CustomerFormData } from '@/modules/customers/types';

interface CustomerFormProps {
    initialData?: Customer;
    onSubmit: (data: CustomerFormData) => Promise<void> | void;
    isSubmitting: boolean;
    onCancel: () => void;
    userId: number;
}

interface CustomerFormState {
    name: string;
    last_name: string;
    email: string;
    cell_phone: string;
    home_phone: string;
    occupation: string;
}

function normalizeOptional(value: string): string | null {
    const normalized = value.trim();
    return normalized === '' ? null : normalized;
}

export default function CustomerForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
    userId,
}: CustomerFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<CustomerFormState>({
        name: initialData?.name ?? '',
        last_name: initialData?.last_name ?? '',
        email: initialData?.email ?? '',
        cell_phone: formatUsPhoneInput(initialData?.cell_phone ?? ''),
        home_phone: formatUsPhoneInput(initialData?.home_phone ?? ''),
        occupation: initialData?.occupation ?? '',
    });
    const [nameError, setNameError] = React.useState<string>('');
    const [emailError, setEmailError] = React.useState<string>('');

    async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
        event.preventDefault();

        let hasError = false;

        if (formData.name.trim() === '') {
            setNameError('Name is required.');
            hasError = true;
        }

        if (formData.email.trim() === '') {
            setEmailError('Email address is required.');
            hasError = true;
        }

        if (hasError) {
            return;
        }

        const payload: CustomerFormData = {
            name: formData.name.trim(),
            last_name: normalizeOptional(formData.last_name),
            email: formData.email.trim(),
            cell_phone: normalizeUsPhoneForPayload(formData.cell_phone),
            home_phone: normalizeUsPhoneForPayload(formData.home_phone),
            occupation: normalizeOptional(formData.occupation),
            user_id: userId,
        };

        await onSubmit(payload);
    }

    return (
        <form
            onSubmit={(event) => { void handleSubmit(event); }}
            className="flex flex-col gap-6 p-6"
            noValidate
        >
            <div className="grid gap-5 md:grid-cols-2">
                <PremiumField
                    label="First Name"
                    required
                    value={formData.name}
                    error={nameError}
                    onChange={(event) => {
                        setFormData((current) => ({ ...current, name: event.target.value }));
                        if (nameError !== '') setNameError('');
                    }}
                    placeholder="e.g. John"
                />

                <PremiumField
                    label="Last Name"
                    value={formData.last_name}
                    onChange={(event) =>
                        setFormData((current) => ({ ...current, last_name: event.target.value }))
                    }
                    placeholder="e.g. Doe"
                />

                <div className="md:col-span-2">
                    <PremiumField
                        label="Email Address"
                        type="email"
                        required
                        value={formData.email}
                        error={emailError}
                        onChange={(event) => {
                            setFormData((current) => ({ ...current, email: event.target.value }));
                            if (emailError !== '') setEmailError('');
                        }}
                        placeholder="e.g. john.doe@example.com"
                    />
                </div>

                <PremiumField
                    label="Cell Phone"
                    type="tel"
                    inputMode="numeric"
                    maxLength={14}
                    value={formData.cell_phone}
                    onChange={(event) =>
                        setFormData((current) => ({
                            ...current,
                            cell_phone: formatUsPhoneInput(event.target.value),
                        }))
                    }
                    placeholder="(555) 000-0000"
                />

                <PremiumField
                    label="Home Phone"
                    type="tel"
                    inputMode="numeric"
                    maxLength={14}
                    value={formData.home_phone}
                    onChange={(event) =>
                        setFormData((current) => ({
                            ...current,
                            home_phone: formatUsPhoneInput(event.target.value),
                        }))
                    }
                    placeholder="(555) 000-0000"
                />

                <div className="md:col-span-2">
                    <PremiumField
                        label="Occupation"
                        value={formData.occupation}
                        onChange={(event) =>
                            setFormData((current) => ({ ...current, occupation: event.target.value }))
                        }
                        placeholder="e.g. Civil Engineer"
                    />
                </div>
            </div>

            <div
                className="flex flex-col gap-3 border-t pt-6 sm:flex-row sm:justify-end"
                style={{ borderColor: 'var(--border-default)' }}
            >
                <button
                    type="button"
                    onClick={onCancel}
                    disabled={isSubmitting}
                    className="btn-ghost px-5 py-3 text-sm font-semibold"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    disabled={isSubmitting}
                    className="btn-primary inline-flex items-center justify-center gap-2 px-5 py-3 text-sm font-semibold"
                >
                    {initialData !== undefined ? <Save size={16} /> : <Plus size={16} />}
                    <span>
                        {isSubmitting
                            ? 'Saving...'
                            : initialData !== undefined
                              ? 'Save changes'
                              : 'Create customer'}
                    </span>
                </button>
            </div>
        </form>
    );
}
