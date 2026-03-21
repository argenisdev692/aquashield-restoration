import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { formatDateShort } from '@/utils/dateFormatter';
import type { PageProps } from '@inertiajs/core';
import { ArrowLeft, Building2, Globe, Mail, MapPin, Pencil, Phone } from 'lucide-react';
import type { MortgageCompany } from '@/modules/mortgage-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';

interface MortgageCompanyShowPageProps extends PageProps {
    mortgageCompany: MortgageCompany;
}

export default function MortgageCompanyShowPage(): React.JSX.Element {
    const { mortgageCompany } = usePage<MortgageCompanyShowPageProps>().props;

    return (
        <>
            <Head title={mortgageCompany.mortgage_company_name} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href="/mortgage-companies"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                        >
                            <ArrowLeft size={16} />
                            <span>Back to mortgage companies</span>
                        </Link>

                        {mortgageCompany.deleted_at === null ? (
                            <Link
                                href={`/mortgage-companies/${mortgageCompany.uuid}/edit`}
                                className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                            >
                                <Pencil size={16} />
                                <span>Edit mortgage company</span>
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
                                <Building2 size={24} />
                            </div>
                            <div className="space-y-1">
                                <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                    {mortgageCompany.mortgage_company_name}
                                </h1>
                                <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                    Mortgage company details
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-6 px-6 py-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-secondary)' }}>
                                    Status
                                </p>
                                <p
                                    className="text-base font-semibold"
                                    style={{
                                        color: mortgageCompany.deleted_at === null
                                            ? 'var(--accent-success)'
                                            : 'var(--accent-error)',
                                    }}
                                >
                                    {mortgageCompany.deleted_at === null ? 'Active' : 'Suspended'}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-secondary)' }}>
                                    Owner user ID
                                </p>
                                <p className="text-base font-semibold" style={{ color: 'var(--text-primary)' }}>
                                    {mortgageCompany.user_id}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-secondary)' }}>
                                    Email
                                </p>
                                {mortgageCompany.email !== null ? (
                                    <a
                                        href={`mailto:${mortgageCompany.email}`}
                                        className="inline-flex items-center gap-2 text-sm font-semibold hover:underline"
                                        style={{ color: 'var(--accent-primary)' }}
                                    >
                                        <Mail size={16} />
                                        <span>{mortgageCompany.email}</span>
                                    </a>
                                ) : (
                                    <p className="text-sm" style={{ color: 'var(--text-muted)' }}>—</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-secondary)' }}>
                                    Phone
                                </p>
                                {mortgageCompany.phone !== null ? (
                                    <a
                                        href={`tel:${mortgageCompany.phone}`}
                                        className="inline-flex items-center gap-2 text-sm font-semibold hover:underline"
                                        style={{ color: 'var(--accent-primary)' }}
                                    >
                                        <Phone size={16} />
                                        <span>{mortgageCompany.phone}</span>
                                    </a>
                                ) : (
                                    <p className="text-sm" style={{ color: 'var(--text-muted)' }}>—</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-secondary)' }}>
                                    Address
                                </p>
                                {mortgageCompany.address !== null ? (
                                    <p className="inline-flex items-start gap-2 text-sm" style={{ color: 'var(--text-secondary)' }}>
                                        <MapPin size={16} className="mt-0.5 shrink-0" />
                                        <span>
                                            {mortgageCompany.address}
                                            {mortgageCompany.address_2 !== null ? `, ${mortgageCompany.address_2}` : ''}
                                        </span>
                                    </p>
                                ) : (
                                    <p className="text-sm" style={{ color: 'var(--text-muted)' }}>—</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-secondary)' }}>
                                    Website
                                </p>
                                {mortgageCompany.website !== null ? (
                                    <a
                                        href={mortgageCompany.website}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="inline-flex items-center gap-2 text-sm font-semibold hover:underline"
                                        style={{ color: 'var(--accent-primary)' }}
                                    >
                                        <Globe size={16} />
                                        <span>{mortgageCompany.website}</span>
                                    </a>
                                ) : (
                                    <p className="text-sm" style={{ color: 'var(--text-muted)' }}>—</p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-secondary)' }}>
                                    Created at
                                </p>
                                <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    {formatDateShort(mortgageCompany.created_at)}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-secondary)' }}>
                                    Updated at
                                </p>
                                <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    {formatDateShort(mortgageCompany.updated_at)}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
