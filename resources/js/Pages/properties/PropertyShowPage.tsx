import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { ArrowLeft, Building2, MapPin, Pencil, Users } from 'lucide-react';
import type { CustomerPropertyRole, Property, PropertyCustomer } from '@/modules/properties/types';
import AppLayout from '@/pages/layouts/AppLayout';

interface PropertyShowPageProps extends PageProps {
    property: Property;
}

const ROLE_STYLES: Record<
    CustomerPropertyRole,
    { label: string; color: string; bg: string }
> = {
    owner: {
        label: 'Owner',
        color: 'var(--accent-primary)',
        bg: 'color-mix(in srgb, var(--accent-primary) 15%, transparent)',
    },
    'co-owner': {
        label: 'Co-Owner',
        color: 'var(--accent-secondary)',
        bg: 'color-mix(in srgb, var(--accent-secondary) 15%, transparent)',
    },
    'additional-signer': {
        label: 'Additional Signer',
        color: 'var(--accent-warning)',
        bg: 'color-mix(in srgb, var(--accent-warning) 15%, transparent)',
    },
};

function RoleBadge({ role }: { role: CustomerPropertyRole }): React.JSX.Element {
    const styles = ROLE_STYLES[role];

    return (
        <span
            className="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold"
            style={{ color: styles.color, background: styles.bg }}
        >
            {styles.label}
        </span>
    );
}

function CustomerRelationshipsTable({
    customers,
}: {
    customers: PropertyCustomer[];
}): React.JSX.Element {
    if (customers.length === 0) {
        return (
            <div
                className="flex flex-col items-center justify-center gap-3 py-10 text-center"
                style={{ color: 'var(--text-muted)' }}
            >
                <Users size={32} style={{ opacity: 0.4 }} />
                <p className="text-sm font-medium">No customers linked to this property.</p>
            </div>
        );
    }

    return (
        <div className="overflow-x-auto">
            <table className="w-full min-w-[480px] text-sm">
                <thead>
                    <tr style={{ borderBottom: '1px solid var(--border-subtle)' }}>
                        <th
                            className="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"
                            style={{ color: 'var(--text-disabled)' }}
                        >
                            Customer
                        </th>
                        <th
                            className="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-wider"
                            style={{ color: 'var(--text-disabled)' }}
                        >
                            Email
                        </th>
                        <th
                            className="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider"
                            style={{ color: 'var(--text-disabled)' }}
                        >
                            Role
                        </th>
                        <th
                            className="px-4 py-3 text-center text-[11px] font-semibold uppercase tracking-wider"
                            style={{ color: 'var(--text-disabled)' }}
                        >
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {customers.map((customer) => {
                        const fullName = customer.last_name
                            ? `${customer.name} ${customer.last_name}`
                            : customer.name;

                        return (
                            <tr
                                key={customer.uuid}
                                style={{ borderBottom: '1px solid var(--border-subtle)' }}
                            >
                                <td className="px-4 py-3">
                                    <span
                                        className="font-semibold"
                                        style={{ color: 'var(--text-primary)' }}
                                    >
                                        {fullName}
                                    </span>
                                </td>
                                <td className="px-4 py-3">
                                    <span style={{ color: 'var(--text-secondary)' }}>
                                        {customer.email}
                                    </span>
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <RoleBadge role={customer.role} />
                                </td>
                                <td className="px-4 py-3 text-center">
                                    <Link
                                        href={`/customers/${customer.uuid}`}
                                        className="btn-ghost inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold"
                                        title={`View ${fullName}`}
                                        aria-label={`View ${fullName}`}
                                    >
                                        View
                                    </Link>
                                </td>
                            </tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
}

export default function PropertyShowPage(): React.JSX.Element {
    const { property } = usePage<PropertyShowPageProps>().props;
    const customers = property.customers ?? [];

    const fullAddress = [
        property.property_address,
        property.property_address_2,
        property.property_city,
        property.property_state,
        property.property_postal_code,
        property.property_country,
    ]
        .filter(Boolean)
        .join(', ');

    return (
        <>
            <Head title={property.property_address} />
            <AppLayout>
                <div className="mx-auto flex max-w-5xl flex-col gap-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href="/properties"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                        >
                            <ArrowLeft size={16} />
                            <span>Back to properties</span>
                        </Link>

                        {property.deleted_at === null ? (
                            <Link
                                href={`/properties/${property.uuid}/edit`}
                                className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                            >
                                <Pencil size={16} />
                                <span>Edit property</span>
                            </Link>
                        ) : null}
                    </div>

                    <div className="card overflow-hidden p-0">
                        <div
                            className="flex items-start gap-4 border-b px-6 py-6"
                            style={{ borderColor: 'var(--border-default)' }}
                        >
                            <div
                                className="flex h-14 w-14 flex-shrink-0 items-center justify-center rounded-2xl"
                                style={{
                                    background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                                    color: 'var(--accent-primary)',
                                }}
                            >
                                <Building2 size={24} />
                            </div>
                            <div className="min-w-0 space-y-1">
                                <h1
                                    className="truncate text-2xl font-extrabold"
                                    style={{ color: 'var(--text-primary)' }}
                                >
                                    {property.property_address}
                                </h1>
                                <p
                                    className="inline-flex items-center gap-1.5 text-sm"
                                    style={{ color: 'var(--text-muted)' }}
                                >
                                    <MapPin size={14} />
                                    <span className="truncate">{fullAddress}</span>
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-6 px-6 py-6 md:grid-cols-2 lg:grid-cols-3">
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
                                            property.deleted_at === null
                                                ? 'var(--accent-success)'
                                                : 'var(--accent-error)',
                                    }}
                                >
                                    {property.deleted_at === null ? 'Active' : 'Deleted'}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    Address Line 2
                                </p>
                                <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    {property.property_address_2 ?? '—'}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    City
                                </p>
                                <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    {property.property_city ?? '—'}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    State
                                </p>
                                <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    {property.property_state ?? '—'}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    Postal Code
                                </p>
                                <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    {property.property_postal_code ?? '—'}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    Country
                                </p>
                                <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    {property.property_country ?? '—'}
                                </p>
                            </div>

                            {property.property_latitude !== null ||
                            property.property_longitude !== null ? (
                                <div className="space-y-2 md:col-span-2 lg:col-span-3">
                                    <p
                                        className="text-xs font-semibold uppercase tracking-[1.5px]"
                                        style={{ color: 'var(--text-disabled)' }}
                                    >
                                        Coordinates
                                    </p>
                                    <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                        {property.property_latitude ?? '—'},{' '}
                                        {property.property_longitude ?? '—'}
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
                                    {new Date(property.created_at).toLocaleString()}
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
                                    {new Date(property.updated_at).toLocaleString()}
                                </p>
                            </div>

                            {property.deleted_at !== null ? (
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
                                        {new Date(property.deleted_at).toLocaleString()}
                                    </p>
                                </div>
                            ) : null}
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <div
                            className="flex items-center gap-3 border-b px-6 py-4"
                            style={{ borderColor: 'var(--border-default)' }}
                        >
                            <Users size={18} style={{ color: 'var(--accent-primary)' }} />
                            <div>
                                <h2
                                    className="text-base font-bold"
                                    style={{ color: 'var(--text-primary)' }}
                                >
                                    Linked Customers
                                </h2>
                                <p className="text-xs" style={{ color: 'var(--text-muted)' }}>
                                    {customers.length}{' '}
                                    {customers.length === 1 ? 'customer' : 'customers'} associated
                                </p>
                            </div>
                        </div>

                        <CustomerRelationshipsTable customers={customers} />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
