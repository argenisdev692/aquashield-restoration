import * as React from 'react';
import { Plus, Save } from 'lucide-react';
import { PremiumField } from '@/shadcn/PremiumField';
import type {
    AllianceCompany,
    AllianceCompanyFormData,
} from '@/modules/alliance-companies/types';
import {
    type UserAddressAutocompleteValue,
    useGoogleMapsAddressAutocomplete,
} from '@/modules/users/hooks/useGoogleMapsAddressAutocomplete';

interface AllianceCompanyFormProps {
    initialData?: AllianceCompany;
    onSubmit: (data: AllianceCompanyFormData) => Promise<void> | void;
    isSubmitting: boolean;
    onCancel: () => void;
}

interface AllianceCompanyFormState {
    alliance_company_name: string;
    address: string;
    phone: string;
    email: string;
    website: string;
}

function buildFullUsAddress(value: UserAddressAutocompleteValue): string {
    return [value.address, value.city, value.state, value.zip_code, value.country]
        .map((segment) => segment.trim())
        .filter((segment) => segment.length > 0)
        .join(', ');
}

function normalizeOptional(value: string): string | null {
    const normalized = value.trim();

    return normalized === '' ? null : normalized;
}

export default function AllianceCompanyForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: AllianceCompanyFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<AllianceCompanyFormState>({
        alliance_company_name: initialData?.alliance_company_name ?? '',
        address: initialData?.address ?? '',
        phone: initialData?.phone ?? '',
        email: initialData?.email ?? '',
        website: initialData?.website ?? '',
    });
    const [address2, setAddress2] = React.useState<string>('');
    const [nameError, setNameError] = React.useState<string>('');
    const addressInputRef = React.useRef<HTMLInputElement | null>(null);
    const address2InputRef = React.useRef<HTMLInputElement | null>(null);

    const handleAddressSelected = React.useCallback((value: UserAddressAutocompleteValue): void => {
        setFormData((current) => ({
            ...current,
            address: buildFullUsAddress(value),
        }));
        setAddress2('');
        window.setTimeout(() => {
            address2InputRef.current?.focus();
        }, 0);
    }, []);

    const { isLoading, isReady, errorMessage } = useGoogleMapsAddressAutocomplete({
        inputRef: addressInputRef,
        onAddressSelected: handleAddressSelected,
    });

    async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
        event.preventDefault();

        const companyName = formData.alliance_company_name.trim();

        if (companyName === '') {
            setNameError('Alliance company name is required.');
            return;
        }

        setNameError('');

        const fullAddress = [formData.address, address2]
            .map((segment) => segment.trim())
            .filter((segment) => segment.length > 0)
            .join(', ');

        await onSubmit({
            alliance_company_name: companyName,
            address: fullAddress === '' ? null : fullAddress,
            phone: normalizeOptional(formData.phone),
            email: normalizeOptional(formData.email),
            website: normalizeOptional(formData.website),
        });
    }

    return (
        <form onSubmit={handleSubmit} className="flex flex-col gap-8 p-8">
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <PremiumField
                    label="Alliance Company Name"
                    required
                    value={formData.alliance_company_name}
                    error={nameError}
                    onChange={(event) => {
                        setFormData((current) => ({
                            ...current,
                            alliance_company_name: event.target.value,
                        }));
                        if (nameError !== '') {
                            setNameError('');
                        }
                    }}
                    placeholder="e.g. ServX Alliance"
                />

                <PremiumField
                    label="Email Address"
                    type="email"
                    value={formData.email}
                    onChange={(event) =>
                        setFormData((current) => ({
                            ...current,
                            email: event.target.value,
                        }))
                    }
                    placeholder="e.g. alliance@example.com"
                />

                <PremiumField
                    label="Phone Number"
                    value={formData.phone}
                    onChange={(event) =>
                        setFormData((current) => ({
                            ...current,
                            phone: event.target.value,
                        }))
                    }
                    placeholder="+1 (555) 555-0101"
                />

                <PremiumField
                    label="Website URL"
                    type="url"
                    value={formData.website}
                    onChange={(event) =>
                        setFormData((current) => ({
                            ...current,
                            website: event.target.value,
                        }))
                    }
                    placeholder="https://example.com"
                />

                <div className="md:col-span-2">
                    <PremiumField
                        label="Physical Address"
                        value={formData.address}
                        onChange={(event) =>
                            setFormData((current) => ({
                                ...current,
                                address: event.target.value,
                            }))
                        }
                        inputRef={addressInputRef}
                        autoComplete="street-address"
                        placeholder="Start typing a USA address"
                    />
                </div>

                <div className="md:col-span-2">
                    <PremiumField
                        label="Address 2"
                        value={address2}
                        onChange={(event) => setAddress2(event.target.value)}
                        inputRef={address2InputRef}
                        autoComplete="address-line2"
                        placeholder="Apartment, suite, unit, building, floor"
                    />
                </div>

                <div className="md:col-span-2">
                    <p className="text-xs" style={{ color: 'var(--text-muted)' }}>
                        {errorMessage ?? (
                            isReady
                                ? 'Autocomplete limited to USA addresses. Address 2 remains manual.'
                                : isLoading
                                    ? 'Loading Google Maps autocomplete...'
                                    : 'Google Maps autocomplete is preparing...'
                        )}
                    </p>
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
                    {initialData ? <Save size={16} /> : <Plus size={16} />}
                    <span>{isSubmitting ? 'Saving...' : initialData ? 'Save changes' : 'Create alliance company'}</span>
                </button>
            </div>
        </form>
    );
}
