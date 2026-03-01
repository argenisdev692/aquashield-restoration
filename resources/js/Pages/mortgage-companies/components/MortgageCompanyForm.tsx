import { useForm } from '@inertiajs/react';
import type { MortgageCompanyDetail } from '@/types/api';
import { PremiumField } from '@/shadcn/PremiumField';
import { Plus, Save, X } from 'lucide-react';

interface MortgageCompanyFormProps {
    initialData?: MortgageCompanyDetail;
    onSubmit: (data: Partial<MortgageCompanyDetail>) => void;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function MortgageCompanyForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: MortgageCompanyFormProps) {
    const { data, setData, errors } = useForm({
        mortgageCompanyName: initialData?.mortgageCompanyName || '',
        address: initialData?.address || '',
        phone: initialData?.phone || '',
        email: initialData?.email || '',
        website: initialData?.website || '',
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        onSubmit(data);
    };

    return (
        <form onSubmit={handleSubmit} className="flex flex-col gap-8 p-8 animate-in slide-in-from-bottom-4 duration-500">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-8">
                <PremiumField
                    label="Mortgage Company Name"
                    error={errors.mortgageCompanyName}
                    required
                    value={data.mortgageCompanyName}
                    onChange={(e) => setData('mortgageCompanyName', e.target.value)}
                    placeholder="e.g. Wells Fargo Home Mortgage"
                />

                <PremiumField
                    label="Email Address"
                    error={errors.email}
                    type="email"
                    value={data.email || ''}
                    onChange={(e) => setData('email', e.target.value)}
                    placeholder="e.g. loans@wellsfargo.com"
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
                    placeholder="https://www.wellsfargo.com"
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

            <div 
                className="flex items-center justify-end gap-3 pt-6"
                style={{ borderTop: '1px solid var(--border-subtle)' }}
            >
                <button
                    type="button"
                    onClick={onCancel}
                    className="px-6 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2"
                    style={{
                        color: 'var(--text-muted)',
                        fontFamily: 'var(--font-sans)',
                    }}
                    onMouseEnter={(e) => {
                        (e.currentTarget as HTMLButtonElement).style.background = 'var(--bg-hover)';
                    }}
                    onMouseLeave={(e) => {
                        (e.currentTarget as HTMLButtonElement).style.background = 'transparent';
                    }}
                >
                    <X size={18} />
                    Cancel
                </button>
                <button
                    type="submit"
                    disabled={isSubmitting}
                    className="font-bold py-2.5 px-8 rounded-xl hover:scale-[1.03] active:scale-[0.97] disabled:opacity-50 transition-all flex items-center gap-2 shadow-lg"
                    style={{
                        background: 'var(--accent-primary)',
                        color: '#ffffff',
                        fontFamily: 'var(--font-sans)',
                        boxShadow: '0 10px 40px color-mix(in srgb, var(--blue-500) 20%, transparent)',
                    }}
                >
                    {isSubmitting ? (
                        <div 
                            className="h-5 w-5 animate-spin rounded-full"
                            style={{ border: '2px solid transparent', borderTopColor: '#ffffff' }}
                        />
                    ) : (
                        initialData ? <Save size={18} /> : <Plus size={18} />
                    )}
                    <span>{initialData ? 'Update Company' : 'Create Company'}</span>
                </button>
            </div>
        </form>
    );
}
