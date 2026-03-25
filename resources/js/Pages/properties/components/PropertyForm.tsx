import * as React from 'react';
import { Plus, Save } from 'lucide-react';
import { useGoogleMapsAddressAutocomplete } from '@/modules/users/hooks/useGoogleMapsAddressAutocomplete';
import { PremiumField } from '@/shadcn/PremiumField';
import type { Property, PropertyFormData } from '@/modules/properties/types';

interface PropertyFormProps {
    initialData?: Property;
    onSubmit: (data: PropertyFormData) => Promise<void> | void;
    isSubmitting: boolean;
    onCancel: () => void;
}

interface PropertyFormState {
    property_address: string;
    property_address_2: string;
    property_state: string;
    property_city: string;
    property_postal_code: string;
    property_country: string;
    property_latitude: string;
    property_longitude: string;
}

function normalizeOptional(value: string): string | null {
    const trimmed = value.trim();
    return trimmed === '' ? null : trimmed;
}

export default function PropertyForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: PropertyFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<PropertyFormState>({
        property_address: initialData?.property_address ?? '',
        property_address_2: initialData?.property_address_2 ?? '',
        property_state: initialData?.property_state ?? '',
        property_city: initialData?.property_city ?? '',
        property_postal_code: initialData?.property_postal_code ?? '',
        property_country: initialData?.property_country ?? '',
        property_latitude: initialData?.property_latitude ?? '',
        property_longitude: initialData?.property_longitude ?? '',
    });
    const [addressError, setAddressError] = React.useState<string>('');

    const addressInputRef = React.useRef<HTMLInputElement>(null);

    useGoogleMapsAddressAutocomplete({
        inputRef: addressInputRef,
        onAddressSelected: (value) => {
            setFormData((current) => ({
                ...current,
                property_address: value.address,
                property_city: value.city,
                property_state: value.state,
                property_postal_code: value.zip_code,
                property_country: value.country,
            }));
            if (addressError !== '') setAddressError('');
        },
    });

    async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
        event.preventDefault();

        if (formData.property_address.trim() === '') {
            setAddressError('Property address is required.');
            return;
        }

        const payload: PropertyFormData = {
            property_address: formData.property_address.trim(),
            property_address_2: normalizeOptional(formData.property_address_2),
            property_state: normalizeOptional(formData.property_state),
            property_city: normalizeOptional(formData.property_city),
            property_postal_code: normalizeOptional(formData.property_postal_code),
            property_country: normalizeOptional(formData.property_country),
            property_latitude: normalizeOptional(formData.property_latitude),
            property_longitude: normalizeOptional(formData.property_longitude),
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
                <div className="md:col-span-2">
                    <label
                        className="mb-1.5 block text-sm font-semibold"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        Property Address
                        <span className="ml-1" style={{ color: 'var(--accent-error)' }}>*</span>
                    </label>
                    <input
                        ref={addressInputRef}
                        type="text"
                        id="property-address-input"
                        value={formData.property_address}
                        onChange={(event) => {
                            setFormData((current) => ({
                                ...current,
                                property_address: event.target.value,
                            }));
                            if (addressError !== '') setAddressError('');
                        }}
                        placeholder="Start typing a USA address..."
                        className="w-full rounded-xl px-4 py-3 text-sm outline-none transition-all"
                        style={{
                            background: 'var(--bg-card)',
                            border: `1px solid ${addressError !== '' ? 'var(--accent-error)' : 'var(--border-default)'}`,
                            color: 'var(--text-primary)',
                            fontFamily: 'var(--font-sans)',
                        }}
                    />
                    {addressError !== '' ? (
                        <p className="mt-1 text-xs" style={{ color: 'var(--accent-error)' }}>
                            {addressError}
                        </p>
                    ) : null}
                </div>

                <div className="md:col-span-2">
                    <PremiumField
                        label="Address Line 2"
                        value={formData.property_address_2}
                        onChange={(event) =>
                            setFormData((current) => ({
                                ...current,
                                property_address_2: event.target.value,
                            }))
                        }
                        placeholder="Apt, Suite, Unit, etc."
                    />
                </div>

                <PremiumField
                    label="City"
                    value={formData.property_city}
                    onChange={(event) =>
                        setFormData((current) => ({
                            ...current,
                            property_city: event.target.value,
                        }))
                    }
                    placeholder="e.g. Miami"
                />

                <PremiumField
                    label="State"
                    value={formData.property_state}
                    onChange={(event) =>
                        setFormData((current) => ({
                            ...current,
                            property_state: event.target.value,
                        }))
                    }
                    placeholder="e.g. Florida"
                />

                <PremiumField
                    label="Postal Code"
                    value={formData.property_postal_code}
                    onChange={(event) =>
                        setFormData((current) => ({
                            ...current,
                            property_postal_code: event.target.value,
                        }))
                    }
                    placeholder="e.g. 33101"
                />

                <PremiumField
                    label="Country"
                    value={formData.property_country}
                    onChange={(event) =>
                        setFormData((current) => ({
                            ...current,
                            property_country: event.target.value,
                        }))
                    }
                    placeholder="e.g. USA"
                />
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
                              : 'Create property'}
                    </span>
                </button>
            </div>
        </form>
    );
}
