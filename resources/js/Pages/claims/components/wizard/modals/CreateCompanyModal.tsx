import * as React from 'react';
import axios, { isAxiosError } from 'axios';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { Building2, Loader2, AlertCircle, ShieldCheck, Users, Handshake, Home } from 'lucide-react';
import { WizardModal } from './WizardModal';

// ─── Types ────────────────────────────────────────────────────────────────────

export type CompanyType = 'insurance' | 'public' | 'alliance' | 'mortgage';

interface CompanyConfig {
    title: string;
    subtitle: string;
    nameLabel: string;
    nameField: string;
    endpoint: string;
    fetchEndpoint: string;
    queryKey: string;
    icon: React.ReactNode;
    accentColor: string;
    hasUnit: boolean;
    hasAddress2: boolean;
    nameResponseField: string;
}

const COMPANY_CONFIGS: Record<CompanyType, CompanyConfig> = {
    insurance: {
        title: 'New Insurance Company',
        subtitle: 'Create a new insurance carrier and assign it to this claim',
        nameLabel: 'Insurance Company Name',
        nameField: 'insurance_company_name',
        nameResponseField: 'insurance_company_name',
        endpoint: '/insurance-companies/data/admin',
        fetchEndpoint: '/insurance-companies/data/admin',
        queryKey: 'insurance-companies',
        icon: <ShieldCheck size={16} />,
        accentColor: 'var(--accent-primary)',
        hasUnit: false,
        hasAddress2: true,
    },
    public: {
        title: 'New Public Company',
        subtitle: 'Create a new public company and assign it to this claim',
        nameLabel: 'Public Company Name',
        nameField: 'public_company_name',
        nameResponseField: 'public_company_name',
        endpoint: '/public-companies/data/admin',
        fetchEndpoint: '/public-companies/data/admin',
        queryKey: 'public-companies',
        icon: <Users size={16} />,
        accentColor: 'var(--accent-secondary)',
        hasUnit: true,
        hasAddress2: true,
    },
    alliance: {
        title: 'New Alliance Company',
        subtitle: 'Create a new alliance partner and assign it to this claim',
        nameLabel: 'Alliance Company Name',
        nameField: 'alliance_company_name',
        nameResponseField: 'alliance_company_name',
        endpoint: '/alliance-companies/data/admin',
        fetchEndpoint: '/alliance-companies/data/admin',
        queryKey: 'alliance-companies',
        icon: <Handshake size={16} />,
        accentColor: 'var(--accent-success)',
        hasUnit: false,
        hasAddress2: false,
    },
    mortgage: {
        title: 'New Mortgage Company',
        subtitle: 'Create a new mortgage lender and assign it to this claim',
        nameLabel: 'Mortgage Company Name',
        nameField: 'mortgage_company_name',
        nameResponseField: 'mortgage_company_name',
        endpoint: '/mortgage-companies/data/admin',
        fetchEndpoint: '/mortgage-companies/data/admin',
        queryKey: 'mortgage-companies',
        icon: <Home size={16} />,
        accentColor: 'var(--accent-warning)',
        hasUnit: false,
        hasAddress2: true,
    },
};

interface CreateStoreResponse {
    message: string;
    uuid: string;
}

interface FetchListResponse {
    data: Array<Record<string, unknown>>;
}

interface ValidationErrors {
    [key: string]: string | undefined;
}

export interface CreateCompanyModalProps {
    open: boolean;
    type: CompanyType;
    onClose: () => void;
    /** Called with the integer company_id + display name after successful creation */
    onCreated: (id: number, name: string) => void;
}

// ─── Shared styles ────────────────────────────────────────────────────────────

const inputStyle: React.CSSProperties = {
    width: '100%',
    height: 40,
    padding: '0 12px',
    background: 'var(--input-bg)',
    border: '1px solid var(--input-border)',
    borderRadius: 'var(--input-radius)',
    color: 'var(--text-primary)',
    fontSize: 13,
    fontFamily: 'var(--font-sans)',
    outline: 'none',
    transition: 'border-color 0.2s ease',
    boxSizing: 'border-box',
};

const labelStyle: React.CSSProperties = {
    display: 'block',
    fontSize: 11,
    fontWeight: 700,
    color: 'var(--text-secondary)',
    fontFamily: 'var(--font-sans)',
    textTransform: 'uppercase',
    letterSpacing: '0.08em',
    marginBottom: 5,
};

// ─── Component ────────────────────────────────────────────────────────────────

export function CreateCompanyModal({
    open,
    type,
    onClose,
    onCreated,
}: CreateCompanyModalProps): React.JSX.Element {
    const cfg = COMPANY_CONFIGS[type];
    const queryClient = useQueryClient();

    const [form, setForm] = React.useState({
        name: '',
        address: '',
        address2: '',
        unit: '',
        phone: '',
        email: '',
        website: '',
    });
    const [errors, setErrors] = React.useState<ValidationErrors>({});
    const [serverError, setServerError] = React.useState<string | null>(null);

    function resetForm(): void {
        setForm({ name: '', address: '', address2: '', unit: '', phone: '', email: '', website: '' });
        setErrors({});
        setServerError(null);
    }

    function handleClose(): void {
        resetForm();
        onClose();
    }

    const mutation = useMutation<CreateStoreResponse, Error, Record<string, string>>({
        mutationFn: async (payload) => {
            const { data } = await axios.post<CreateStoreResponse>(cfg.endpoint, payload);
            return data;
        },
        onSuccess: async (response) => {
            await queryClient.invalidateQueries({ queryKey: [cfg.queryKey] });

            const savedName = form.name.trim();

            try {
                const { data: listData } = await axios.get<FetchListResponse>(cfg.fetchEndpoint, {
                    params: { per_page: 200, status: 'active' },
                });

                const nameField = cfg.nameResponseField;
                const match = listData.data.find(
                    (item) =>
                        (item['uuid'] as string | undefined) === response.uuid ||
                        (item[nameField] as string | undefined)?.toLowerCase() === savedName.toLowerCase(),
                );

                const id = match
                    ? ((match['company_id'] as number | undefined) ?? (match['id'] as number | undefined) ?? 0)
                    : 0;

                resetForm();
                onCreated(id, savedName);
            } catch {
                resetForm();
                onCreated(0, savedName);
            }

            onClose();
        },
        onError: (error) => {
            if (isAxiosError<{ message?: string; errors?: ValidationErrors }>(error)) {
                const data = error.response?.data;
                if (data?.errors) {
                    setErrors(data.errors);
                } else {
                    setServerError(data?.message ?? `Failed to create ${type} company.`);
                }
            } else {
                setServerError('An unexpected error occurred.');
            }
        },
    });

    function validate(): boolean {
        const next: ValidationErrors = {};
        if (!form.name.trim()) next[cfg.nameField] = `${cfg.nameLabel} is required.`;
        setErrors(next);
        return Object.keys(next).length === 0;
    }

    function handleSubmit(e: React.FormEvent): void {
        e.preventDefault();
        setServerError(null);
        if (!validate()) return;

        const payload: Record<string, string> = {
            [cfg.nameField]: form.name.trim(),
        };

        if (form.address.trim()) payload['address'] = form.address.trim();
        if (cfg.hasAddress2 && form.address2.trim()) payload['address_2'] = form.address2.trim();
        if (cfg.hasUnit && form.unit.trim()) payload['unit'] = form.unit.trim();
        if (form.phone.trim()) payload['phone'] = form.phone.trim();
        if (form.email.trim()) payload['email'] = form.email.trim();
        if (form.website.trim()) payload['website'] = form.website.trim();

        mutation.mutate(payload);
    }

    function textField(
        id: keyof typeof form,
        label: string,
        required = false,
        placeholder = '',
        type_ = 'text',
    ): React.JSX.Element {
        const errKey = id === 'name' ? cfg.nameField : id === 'address2' ? 'address_2' : id;
        return (
            <div>
                <label htmlFor={`ccm-${String(id)}`} style={labelStyle}>
                    {label}{required && <span style={{ color: 'var(--accent-error)' }}> *</span>}
                </label>
                <input
                    id={`ccm-${String(id)}`}
                    type={type_}
                    value={form[id]}
                    onChange={(e) => setForm((p) => ({ ...p, [id]: e.target.value }))}
                    placeholder={placeholder}
                    style={{
                        ...inputStyle,
                        borderColor: errors[errKey] ? 'var(--accent-error)' : 'var(--input-border)',
                    }}
                />
                {errors[errKey] && (
                    <p style={{ margin: '3px 0 0', fontSize: 11, color: 'var(--accent-error)', fontFamily: 'var(--font-sans)' }}>
                        {errors[errKey]}
                    </p>
                )}
            </div>
        );
    }

    const namePlaceholder =
        type === 'insurance' ? 'State Farm Insurance'
        : type === 'public'  ? 'Public Adjusters LLC'
        : type === 'alliance' ? 'Alliance Partners Inc.'
        : 'First National Mortgage';

    return (
        <WizardModal
            open={open}
            onClose={handleClose}
            title={cfg.title}
            subtitle={cfg.subtitle}
            icon={cfg.icon}
            accentColor={cfg.accentColor}
            maxWidth={520}
        >
            <form onSubmit={handleSubmit} noValidate>
                <div style={{ display: 'flex', flexDirection: 'column', gap: 14 }}>
                    {serverError && (
                        <div
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 8,
                                padding: '9px 12px',
                                background: 'color-mix(in srgb, var(--accent-error) 10%, var(--bg-card))',
                                border: '1px solid color-mix(in srgb, var(--accent-error) 30%, transparent)',
                                borderRadius: 'var(--radius-md)',
                                fontSize: 12,
                                color: 'var(--accent-error)',
                                fontFamily: 'var(--font-sans)',
                            }}
                        >
                            <AlertCircle size={13} />
                            {serverError}
                        </div>
                    )}

                    {/* Name — always required */}
                    {textField('name', cfg.nameLabel, true, `e.g. ${namePlaceholder}`)}

                    {/* Address row */}
                    <div style={{
                        display: 'grid',
                        gridTemplateColumns: cfg.hasAddress2 || cfg.hasUnit ? '1fr 1fr' : '1fr',
                        gap: 12,
                    }}>
                        {textField('address', 'Address', false, '123 Main St')}
                        {cfg.hasAddress2 && textField('address2', 'Address 2', false, 'Suite 100')}
                        {cfg.hasUnit && textField('unit', 'Unit', false, 'Unit 4B')}
                    </div>

                    {/* Phone + Email */}
                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                        {textField('phone', 'Phone', false, '(555) 000-0000', 'tel')}
                        {textField('email', 'Email', false, 'info@company.com', 'email')}
                    </div>

                    {/* Website */}
                    {textField('website', 'Website', false, 'https://example.com', 'url')}

                    {/* Footer */}
                    <div
                        style={{
                            display: 'flex',
                            justifyContent: 'flex-end',
                            gap: 10,
                            paddingTop: 8,
                            borderTop: '1px solid var(--border-subtle)',
                            marginTop: 4,
                        }}
                    >
                        <button
                            type="button"
                            onClick={handleClose}
                            disabled={mutation.isPending}
                            style={{
                                padding: '8px 16px',
                                borderRadius: 'var(--radius-md)',
                                border: '1px solid var(--border-default)',
                                background: 'transparent',
                                color: 'var(--text-secondary)',
                                fontSize: 13,
                                fontFamily: 'var(--font-sans)',
                                cursor: mutation.isPending ? 'not-allowed' : 'pointer',
                                fontWeight: 500,
                            }}
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={mutation.isPending}
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 7,
                                padding: '8px 18px',
                                borderRadius: 'var(--radius-md)',
                                border: 'none',
                                background: mutation.isPending ? 'var(--text-disabled)' : cfg.accentColor,
                                color: '#fff',
                                fontSize: 13,
                                fontFamily: 'var(--font-sans)',
                                fontWeight: 700,
                                cursor: mutation.isPending ? 'not-allowed' : 'pointer',
                                transition: 'background 0.15s ease',
                            }}
                        >
                            {mutation.isPending ? (
                                <><Loader2 size={13} className="animate-spin" /> Creating...</>
                            ) : (
                                <><Building2 size={13} /> Create Company</>
                            )}
                        </button>
                    </div>
                </div>
            </form>
        </WizardModal>
    );
}
