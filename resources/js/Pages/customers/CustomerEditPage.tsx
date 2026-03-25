import * as React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { User } from 'lucide-react';
import { useUpdateCustomer } from '@/modules/customers/hooks/useCustomerMutations';
import type { Customer, CustomerFormData } from '@/modules/customers/types';
import AppLayout from '@/pages/layouts/AppLayout';
import CustomerForm from './components/CustomerForm';

interface CustomerEditPageProps extends PageProps {
    customer: Customer;
}

export default function CustomerEditPage(): React.JSX.Element {
    const { customer } = usePage<CustomerEditPageProps>().props;
    const updateCustomer = useUpdateCustomer();

    async function handleSubmit(data: CustomerFormData): Promise<void> {
        await updateCustomer.mutateAsync({ uuid: customer.uuid, data });
    }

    const fullName = customer.last_name
        ? `${customer.name} ${customer.last_name}`
        : customer.name;

    return (
        <>
            <Head title={`Edit ${fullName}`} />
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
                                Edit customer
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Update the current customer information.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <CustomerForm
                            initialData={customer}
                            onSubmit={handleSubmit}
                            isSubmitting={updateCustomer.isPending}
                            onCancel={() => router.visit('/customers')}
                            userId={customer.user_id}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
