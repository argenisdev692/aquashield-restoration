import { useForm } from '@inertiajs/react';
import { PublicCompany } from '@/modules/public-companies/types';
import { PremiumField } from '@/shadcn/PremiumField';
import { Plus, Save, X } from 'lucide-react';

interface PublicCompanyFormProps {
    initialData?: PublicCompany;
    onSubmit: (data: Partial<PublicCompany>) => void;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function PublicCompanyForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: PublicCompanyFormProps) {
    const { data, setData, errors } = useForm({
        public_company_name: initialData?.public_company_name || '',
        address: initialData?.address || '',
        phone: initialData?.phone || '',
        email: initialData?.email || '',
        website: initialData?.website || '',
        unit: initialData?.unit || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSubmit(data);
    };

    return (
        <form onSubmit={handleSubmit} className="flex flex-col gap-8 p-8 animate-in slide-in-from-bottom-4 duration-500">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                <PremiumField
                    label="Public Company Name"
                    error={errors.public_company_name}
                    required
                    value={data.public_company_name}
                    onChange={(e) => setData('public_company_name', e.target.value)}
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

                <PremiumField
                    label="Unit"
                    error={errors.unit}
                    value={data.unit || ''}
                    onChange={(e) => setData('unit', e.target.value)}
                    placeholder="e.g. Unit 4B"
                />

                <div className="md:col-span-2">
                    <PremiumField
                        label="Physical Address"
                        error={errors.address}
                        isTextArea
                        value={data.address || ''}
                        onChange={(e) => (setData as any)('address', e.target.value)}
                        placeholder="Street, City, State, Zip..."
                    />
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
