import * as React from 'react';
import { Head, router } from '@inertiajs/react';
import { User } from 'lucide-react';
import { useAuthContext } from '@/modules/auth/context/AuthContext';
import { useCreateCustomer } from '@/modules/customers/hooks/useCustomerMutations';
import type { CustomerFormData } from '@/modules/customers/types';
import AppLayout from '@/pages/layouts/AppLayout';
import CustomerForm from './components/CustomerForm';

export default function CustomerCreatePage(): React.JSX.Element {
    const { user } = useAuthContext();
    const userId: number = (user as { id?: number } | null)?.id ?? 0;
    const createCustomer = useCreateCustomer();

    async function handleSubmit(data: CustomerFormData): Promise<void> {
        await createCustomer.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Customer" />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex items-start gap-4">
                        <div
                            className="flex h-14 w-14 items-center justify-center rounded-2xl"
                            style={{
                                background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                                color: 'var(--accent-primary)',
                            }}
                        >
                            <User size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1
                                className="text-3xl font-extrabold"
                                style={{ color: 'var(--text-primary)' }}
                            >
                                Create customer
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Add a new customer record to your CRM.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <CustomerForm
                            onSubmit={handleSubmit}
                            isSubmitting={createCustomer.isPending}
                            onCancel={() => router.visit('/customers')}
                            userId={userId}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
