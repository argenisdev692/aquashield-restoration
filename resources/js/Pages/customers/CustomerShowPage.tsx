import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { ArrowLeft, Briefcase, Mail, Pencil, Phone, User } from 'lucide-react';
import type { Customer } from '@/modules/customers/types';
import AppLayout from '@/pages/layouts/AppLayout';

interface CustomerShowPageProps extends PageProps {
    customer: Customer;
}

export default function CustomerShowPage(): React.JSX.Element {
    const { customer } = usePage<CustomerShowPageProps>().props;

    const fullName = customer.last_name
        ? `${customer.name} ${customer.last_name}`
        : customer.name;

    return (
        <>
            <Head title={fullName} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href="/customers"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                        >
                            <ArrowLeft size={16} />
                            <span>Back to customers</span>
                        </Link>

                        {customer.deleted_at === null ? (
                            <Link
                                href={`/customers/${customer.uuid}/edit`}
                                className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                            >
                                <Pencil size={16} />
                                <span>Edit customer</span>
                            </Link>
                        ) : null}
                    </div>

                    <div className="card overflow-hidden p-0">
                        <div
                            className="flex items-start gap-4 border-b px-6 py-6"
                            style={{ borderColor: 'var(--border-default)' }}
                        >
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
                                <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                    {fullName}
                                </h1>
                                <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                    Customer details
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-6 px-6 py-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    Status
                                </p>
                                <p
                                    className="text-base font-semibold"
                                    style={{
                                        color:
                                            customer.deleted_at === null
                                                ? 'var(--accent-success)'
                                                : 'var(--accent-error)',
                                    }}
                                >
                                    {customer.deleted_at === null ? 'Active' : 'Deleted'}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    Email Address
                                </p>
                                <p
                                    className="inline-flex items-center gap-2 text-sm"
                                    style={{ color: 'var(--text-secondary)' }}
                                >
                                    <Mail size={16} />
                                    <span>{customer.email}</span>
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    Cell Phone
                                </p>
                                <p
                                    className="inline-flex items-center gap-2 text-sm"
                                    style={{ color: 'var(--text-secondary)' }}
                                >
                                    <Phone size={16} />
                                    <span>{customer.cell_phone ?? '—'}</span>
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    Home Phone
                                </p>
                                <p
                                    className="inline-flex items-center gap-2 text-sm"
                                    style={{ color: 'var(--text-secondary)' }}
                                >
                                    <Phone size={16} />
                                    <span>{customer.home_phone ?? '—'}</span>
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    Occupation
                                </p>
                                <p
                                    className="inline-flex items-center gap-2 text-sm"
                                    style={{ color: 'var(--text-secondary)' }}
                                >
                                    <Briefcase size={16} />
                                    <span>{customer.occupation ?? '—'}</span>
                                </p>
                            </div>

                            {customer.user_name !== null ? (
                                <div className="space-y-2">
                                    <p
                                        className="text-xs font-semibold uppercase tracking-[1.5px]"
                                        style={{ color: 'var(--text-disabled)' }}
                                    >
                                        Assigned To
                                    </p>
                                    <p
                                        className="text-sm font-semibold"
                                        style={{ color: 'var(--text-primary)' }}
                                    >
                                        {customer.user_name}
                                    </p>
                                </div>
                            ) : null}

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    Created At
                                </p>
                                <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    {new Date(customer.created_at).toLocaleString()}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    Updated At
                                </p>
                                <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    {new Date(customer.updated_at).toLocaleString()}
                                </p>
                            </div>

                            {customer.deleted_at !== null ? (
                                <div className="space-y-2">
                                    <p
                                        className="text-xs font-semibold uppercase tracking-[1.5px]"
                                        style={{ color: 'var(--text-disabled)' }}
                                    >
                                        Deleted At
                                    </p>
                                    <p
                                        className="text-sm font-semibold"
                                        style={{ color: 'var(--accent-error)' }}
                                    >
                                        {new Date(customer.deleted_at).toLocaleString()}
                                    </p>
                                </div>
                            ) : null}
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
