import * as React from 'react';
import { type UserAddressAutocompleteValue, useGoogleMapsAddressAutocomplete } from '@/modules/users/hooks/useGoogleMapsAddressAutocomplete';
import { PremiumField } from '@/shadcn/PremiumField';
import { Plus, Save, X } from 'lucide-react';
import type {
    PublicCompanyFormData,
    PublicCompanyFormErrors,
} from '@/modules/public-companies/types';

interface PublicCompanyFormProps {
    data: PublicCompanyFormData;
    errors: PublicCompanyFormErrors;
    onChange: <K extends keyof PublicCompanyFormData>(field: K, value: PublicCompanyFormData[K]) => void;
    onSubmit: () => void;
    isSubmitting: boolean;
    onCancel: () => void;
    submitLabel: string;
}

function buildFullUsAddress(value: UserAddressAutocompleteValue): string {
    return [value.address, value.city, value.state, value.zip_code, value.country]
        .map((segment) => segment.trim())
        .filter((segment) => segment.length > 0)
        .join(', ');
}

export default function PublicCompanyForm({
    data,
    errors,
    onChange,
    onSubmit,
    isSubmitting,
    onCancel,
    submitLabel,
}: PublicCompanyFormProps): React.JSX.Element {
    const addressInputRef = React.useRef<HTMLInputElement | null>(null);
    const unitInputRef = React.useRef<HTMLInputElement | null>(null);

    const handleAddressSelected = React.useCallback((value: UserAddressAutocompleteValue): void => {
        onChange('address', buildFullUsAddress(value));
        window.setTimeout(() => {
            unitInputRef.current?.focus();
        }, 0);
    }, [onChange]);

    const { isLoading, isReady, errorMessage } = useGoogleMapsAddressAutocomplete({
        inputRef: addressInputRef,
        onAddressSelected: handleAddressSelected,
    });

    function handleFormSubmit(event: React.FormEvent<HTMLFormElement>): void {
        event.preventDefault();
        onSubmit();
    }

    return (
        <form onSubmit={handleFormSubmit} className="flex flex-col gap-8 p-8">
            <div className="grid grid-cols-1 gap-8 md:grid-cols-2">
                <PremiumField
                    label="Public Company Name"
                    error={errors.public_company_name}
                    required
                    value={data.public_company_name}
                    onChange={(event) => onChange('public_company_name', event.target.value)}
                    placeholder="e.g. Aqua Public Claims"
                />

                <PremiumField
                    label="Email Address"
                    error={errors.email}
                    type="email"
                    value={data.email}
                    onChange={(event) => onChange('email', event.target.value)}
                    placeholder="e.g. claims@publiccompany.com"
                />

                <PremiumField
                    label="Phone Number"
                    error={errors.phone}
                    value={data.phone}
                    onChange={(event) => onChange('phone', event.target.value)}
                    placeholder="(555) 123-4567"
                />

                <PremiumField
                    label="Website URL"
                    error={errors.website}
                    type="url"
                    value={data.website}
                    onChange={(event) => onChange('website', event.target.value)}
                    placeholder="https://www.publiccompany.com"
                />

                <div className="md:col-span-2">
                    <PremiumField
                        label="Physical Address"
                        error={errors.address}
                        value={data.address}
                        onChange={(event) => onChange('address', event.target.value)}
                        inputRef={addressInputRef}
                        autoComplete="street-address"
                        placeholder="Start typing a USA address"
                    />
                </div>

                <div className="md:col-span-2">
                    <PremiumField
                        label="Address 2 / Unit"
                        error={errors.unit}
                        value={data.unit}
                        onChange={(event) => onChange('unit', event.target.value)}
                        inputRef={unitInputRef}
                        autoComplete="address-line2"
                        placeholder="Apartment, suite, unit, building, floor"
                    />
                </div>

                <div className="md:col-span-2">
                    <p className="text-xs" style={{ color: 'var(--text-muted)' }}>
                        {errorMessage ?? (isReady ? 'Autocomplete limited to USA addresses. Address 2 / Unit remains manual.' : isLoading ? 'Loading Google Maps autocomplete...' : 'Google Maps autocomplete is preparing...')}
                    </p>
                </div>
            </div>

            <div className="flex items-center justify-end gap-3 border-t pt-6" style={{ borderColor: 'var(--border-subtle)' }}>
                <button
                    type="button"
                    onClick={onCancel}
                    className="btn-ghost flex items-center gap-2 px-6 py-2.5 text-sm font-bold"
                >
                    <X size={18} />
                    Cancel
                </button>
                <button
                    type="submit"
                    disabled={isSubmitting}
                    className="btn-primary flex items-center gap-2 px-8 py-2.5 text-sm font-bold disabled:opacity-50"
                >
                    {isSubmitting ? (
                        <div
                            className="h-5 w-5 animate-spin rounded-full border-b-2"
                            style={{ borderColor: 'var(--text-primary)' }}
                        />
                    ) : submitLabel.startsWith('Update') ? (
                        <Save size={18} />
                    ) : (
                        <Plus size={18} />
                    )}
                    <span>{submitLabel}</span>
                </button>
            </div>
        </form>
    );
}
