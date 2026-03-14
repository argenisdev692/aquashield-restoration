import * as React from 'react';
import { useForm } from '@inertiajs/react';
import { AllianceCompany } from '@/modules/alliance-companies/types';
import { type UserAddressAutocompleteValue, useGoogleMapsAddressAutocomplete } from '@/modules/users/hooks/useGoogleMapsAddressAutocomplete';
import { PremiumField } from '@/shadcn/PremiumField';
import { Plus, Save, X } from 'lucide-react';

interface AllianceCompanyFormProps {
    initialData?: AllianceCompany;
    onSubmit: (data: Partial<AllianceCompany>) => void;
    isSubmitting: boolean;
    onCancel: () => void;
}

function buildFullUsAddress(value: UserAddressAutocompleteValue): string {
    return [value.address, value.city, value.state, value.zip_code, value.country]
        .map((segment) => segment.trim())
        .filter((segment) => segment.length > 0)
        .join(', ');
}

export default function AllianceCompanyForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: AllianceCompanyFormProps) {
    const { data, setData, errors } = useForm({
        alliance_company_name: initialData?.alliance_company_name || '',
        address: initialData?.address || '',
        phone: initialData?.phone || '',
        email: initialData?.email || '',
        website: initialData?.website || '',
    });
    const [address2, setAddress2] = React.useState<string>('');
    const addressInputRef = React.useRef<HTMLInputElement | null>(null);
    const address2InputRef = React.useRef<HTMLInputElement | null>(null);

    const handleAddressSelected = React.useCallback((value: UserAddressAutocompleteValue): void => {
        setData('address', buildFullUsAddress(value));
        setAddress2('');
        window.setTimeout(() => {
            address2InputRef.current?.focus();
        }, 0);
    }, [setData]);

    const { isLoading, isReady, errorMessage } = useGoogleMapsAddressAutocomplete({
        inputRef: addressInputRef,
        onAddressSelected: handleAddressSelected,
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        const address = [data.address ?? '', address2]
            .map((segment) => segment.trim())
            .filter((segment) => segment.length > 0)
            .join(', ');

        onSubmit({
            ...data,
            address: address.length > 0 ? address : data.address,
        });
    };

    return (
        <form onSubmit={handleSubmit} className="flex flex-col gap-8 p-8 animate-in slide-in-from-bottom-4 duration-500">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                <PremiumField
                    label="Alliance Company Name"
                    error={errors.alliance_company_name}
                    required
                    value={data.alliance_company_name}
                    onChange={(e) => setData('alliance_company_name', e.target.value)}
                    placeholder="e.g. State Farm"
                />

                <PremiumField
                    label="Email Address"
                    error={errors.email}
                    type="email"
                    value={data.email || ''}
                    onChange={(e) => setData('email', e.target.value)}
                    placeholder="e.g. claims@statefarm.com"
                />

                <PremiumField
                    label="Phone Number"
                    error={errors.phone}
                    value={data.phone || ''}
                    onChange={(e) => setData('phone', e.target.value)}
                    placeholder="(555) 123-4567"
                />

                <PremiumField
                    label="Website URL"
                    error={errors.website}
                    type="url"
                    value={data.website || ''}
                    onChange={(e) => setData('website', e.target.value)}
                    placeholder="https://www.statefarm.com"
                />

                <div className="md:col-span-2">
                    <PremiumField
                        label="Physical Address"
                        error={errors.address}
                        value={data.address || ''}
                        onChange={(e) => setData('address', e.target.value)}
                        inputRef={addressInputRef}
                        autoComplete="street-address"
                        placeholder="Start typing a USA address"
                    />
                </div>

                <div className="md:col-span-2">
                    <PremiumField
                        label="Address 2"
                        value={address2}
                        onChange={(e) => setAddress2(e.target.value)}
                        inputRef={address2InputRef}
                        autoComplete="address-line2"
                        placeholder="Apartment, suite, unit, building, floor"
                    />
                </div>

                <div className="md:col-span-2">
                    <p className="text-xs text-(--text-muted)">
                        {errorMessage ?? (isReady ? 'Autocomplete limited to USA addresses. Address 2 remains manual.' : isLoading ? 'Loading Google Maps autocomplete...' : 'Google Maps autocomplete is preparing...')}
                    </p>
                </div>
            </div>

            <div className="flex items-center justify-end gap-3 pt-6 border-t border-(--border-subtle)">
                <button
                    type="button"
                    onClick={onCancel}
                    className="px-6 py-2.5 rounded-xl text-sm font-bold text-(--text-muted) hover:bg-(--bg-hover) transition-all flex items-center gap-2"
                >
                    <X size={18} />
                    Cancel
                </button>
                <button
                    type="submit"
                    disabled={isSubmitting}
                    className="bg-(--accent-primary) text-white font-bold py-2.5 px-8 rounded-xl hover:scale-[1.03] active:scale-[0.97] disabled:opacity-50 transition-all flex items-center gap-2 shadow-lg shadow-blue-500/20"
                >
                    {isSubmitting ? (
                        <div className="h-5 w-5 animate-spin rounded-full border-b-2 border-white" />
                    ) : (
                        initialData ? <Save size={18} /> : <Plus size={18} />
                    )}
                    <span>{initialData ? 'Update Company' : 'Create Company'}</span>
                </button>
            </div>
        </form>
    );
}
