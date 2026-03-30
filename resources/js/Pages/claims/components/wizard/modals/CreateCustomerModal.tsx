import * as React from 'react';
import axios, { isAxiosError } from 'axios';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { UserPlus, Loader2, AlertCircle } from 'lucide-react';
import { usePage } from '@inertiajs/react';
import { WizardModal } from './WizardModal';
import type { AuthPageProps } from '@/types/auth';

interface CreateCustomerPayload {
    name: string;
    last_name: string;
    email: string;
    cell_phone: string;
    home_phone: string;
    occupation: string;
    user_id: number;
}

interface CreateCustomerStoreResponse {
    message: string;
    uuid: string;
}

interface CustomerListItemResponse {
    customer_id: number;
    uuid: string;
    name: string;
    last_name: string | null;
    email: string | null;
}

interface CustomerListResponse {
    data: CustomerListItemResponse[];
}

interface ValidationErrors {
    name?: string;
    last_name?: string;
    email?: string;
    cell_phone?: string;
    home_phone?: string;
    occupation?: string;
}

export interface CreateCustomerModalProps {
    open: boolean;
    onClose: () => void;
    /** Called with integer customer_id, uuid, and full name after successful creation */
    onCreated: (id: number, uuid: string, fullName: string) => void;
}

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

export function CreateCustomerModal({ open, onClose, onCreated }: CreateCustomerModalProps): React.JSX.Element {
    const { auth } = usePage().props as unknown as AuthPageProps;
    const queryClient = useQueryClient();

    const [form, setForm] = React.useState({
        name: '',
        last_name: '',
        email: '',
        cell_phone: '',
        home_phone: '',
        occupation: '',
    });
    const [errors, setErrors] = React.useState<ValidationErrors>({});
    const [serverError, setServerError] = React.useState<string | null>(null);

    function resetForm(): void {
        setForm({ name: '', last_name: '', email: '', cell_phone: '', home_phone: '', occupation: '' });
        setErrors({});
        setServerError(null);
    }

    function handleClose(): void {
        resetForm();
        onClose();
    }

    const mutation = useMutation<CreateCustomerStoreResponse, Error, CreateCustomerPayload>({
        mutationFn: async (payload) => {
            const { data } = await axios.post<CreateCustomerStoreResponse>('/customers/data/admin', payload);
            return data;
        },
        onSuccess: async (response) => {
            await queryClient.invalidateQueries({ queryKey: ['customers'] });

            const fullName = [form.name, form.last_name].filter(Boolean).join(' ');

            try {
                const { data: listData } = await axios.get<CustomerListResponse>(
                    '/customers/data/admin',
                    { params: { search: form.email, per_page: 10, status: 'active' } },
                );
                const match = listData.data.find((c) => c.uuid === response.uuid);
                const id = match?.customer_id ?? 0;
                resetForm();
                onCreated(id, response.uuid, fullName);
            } catch {
                resetForm();
                onCreated(0, response.uuid, fullName);
            }

            onClose();
        },
        onError: (error) => {
            if (isAxiosError<{ message?: string; errors?: ValidationErrors }>(error)) {
                const data = error.response?.data;
                if (data?.errors) {
                    setErrors(data.errors);
                } else {
                    setServerError(data?.message ?? 'Failed to create customer.');
                }
            } else {
                setServerError('An unexpected error occurred.');
            }
        },
    });

    function validate(): boolean {
        const next: ValidationErrors = {};
        if (!form.name.trim()) next.name = 'First name is required.';
        if (!form.email.trim()) {
            next.email = 'Email is required.';
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
            next.email = 'Enter a valid email address.';
        }
        setErrors(next);
        return Object.keys(next).length === 0;
    }

    function handleSubmit(e: React.FormEvent): void {
        e.preventDefault();
        setServerError(null);
        if (!validate()) return;
        mutation.mutate({
            ...form,
            user_id: auth.user?.id ?? 1,
        });
    }

    function textField(
        id: keyof typeof form,
        label: string,
        type = 'text',
        required = false,
        placeholder = '',
    ): React.JSX.Element {
        return (
            <div>
                <label htmlFor={`ccm-${id}`} style={labelStyle}>
                    {label}{required && <span style={{ color: 'var(--accent-error)' }}> *</span>}
                </label>
                <input
                    id={`ccm-${id}`}
                    type={type}
                    value={form[id]}
                    onChange={(e) => setForm((p) => ({ ...p, [id]: e.target.value }))}
                    placeholder={placeholder}
                    style={{
                        ...inputStyle,
                        borderColor: errors[id as keyof ValidationErrors]
                            ? 'var(--accent-error)'
                            : 'var(--input-border)',
                    }}
                />
                {errors[id as keyof ValidationErrors] && (
                    <p style={{ margin: '3px 0 0', fontSize: 11, color: 'var(--accent-error)', fontFamily: 'var(--font-sans)' }}>
                        {errors[id as keyof ValidationErrors]}
                    </p>
                )}
            </div>
        );
    }

    return (
        <WizardModal
            open={open}
            onClose={handleClose}
            title="New Customer"
            subtitle="Create a new customer and assign them to this claim"
            icon={<UserPlus size={16} />}
            accentColor="var(--accent-primary)"
            maxWidth={500}
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

                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                        {textField('name', 'First Name', 'text', true, 'John')}
                        {textField('last_name', 'Last Name', 'text', false, 'Doe')}
                    </div>

                    {textField('email', 'Email', 'email', true, 'john@example.com')}

                    <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 12 }}>
                        {textField('cell_phone', 'Cell Phone', 'tel', false, '(555) 000-0000')}
                        {textField('home_phone', 'Home Phone', 'tel', false, '(555) 000-0001')}
                    </div>

                    {textField('occupation', 'Occupation', 'text', false, 'e.g. Homeowner')}

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
                                background: mutation.isPending ? 'var(--text-disabled)' : 'var(--accent-primary)',
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
                                <><UserPlus size={13} /> Create Customer</>
                            )}
                        </button>
                    </div>
                </div>
            </form>
        </WizardModal>
    );
}
