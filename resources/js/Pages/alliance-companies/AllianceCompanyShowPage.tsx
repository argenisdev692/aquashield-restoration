import { Head, Link, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { ArrowLeft, Building2, Globe, Mail, MapPin, Pencil, Phone } from 'lucide-react';
import type { AllianceCompany } from '@/modules/alliance-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';

interface AllianceCompanyShowPageProps extends PageProps {
    allianceCompany: AllianceCompany;
}

export default function AllianceCompanyShowPage(): React.JSX.Element {
    const { allianceCompany } = usePage<AllianceCompanyShowPageProps>().props;

    return (
        <>
            <Head title={allianceCompany.alliance_company_name} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href="/alliance-companies"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                        >
                            <ArrowLeft size={16} />
                            <span>Back to alliance companies</span>
                        </Link>

                        {allianceCompany.deleted_at === null ? (
                            <Link
                                href={`/alliance-companies/${allianceCompany.uuid}/edit`}
                                className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                            >
                                <Pencil size={16} />
                                <span>Edit alliance company</span>
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
                                    {allianceCompany.alliance_company_name}
                                </h1>
                                <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                    Alliance company details
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-6 px-6 py-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-disabled)' }}>
                                    Status
                                </p>
                                <p
                                    className="text-base font-semibold"
                                    style={{
                                        color: allianceCompany.deleted_at === null
                                            ? 'var(--accent-success)'
                                            : 'var(--accent-error)',
                                    }}
                                >
                                    {allianceCompany.deleted_at === null ? 'Active' : 'Deleted'}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-disabled)' }}>
                                    Owner user ID
                                </p>
                                <p className="text-base font-semibold" style={{ color: 'var(--text-primary)' }}>
                                    {allianceCompany.user_id}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-disabled)' }}>
                                    Phone
                                </p>
                                <p className="inline-flex items-center gap-2 text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    <Phone size={16} />
                                    <span>{allianceCompany.phone ?? '—'}</span>
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-disabled)' }}>
                                    Email
                                </p>
                                <p className="inline-flex items-center gap-2 text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    <Mail size={16} />
                                    <span>{allianceCompany.email ?? '—'}</span>
                                </p>
                            </div>

                            <div className="space-y-2 md:col-span-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-disabled)' }}>
                                    Address
                                </p>
                                <p className="inline-flex items-start gap-2 text-sm leading-7" style={{ color: 'var(--text-secondary)' }}>
                                    <MapPin size={16} className="mt-1 shrink-0" />
                                    <span>{allianceCompany.address ?? '—'}</span>
                                </p>
                            </div>

                            <div className="space-y-2 md:col-span-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-disabled)' }}>
                                    Website
                                </p>
                                {allianceCompany.website === null ? (
                                    <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                        —
                                    </p>
                                ) : (
                                    <a
                                        href={allianceCompany.website}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="inline-flex items-center gap-2 text-sm font-semibold hover:underline"
                                        style={{ color: 'var(--accent-primary)' }}
                                    >
                                        <Globe size={16} />
                                        <span>{allianceCompany.website}</span>
                                    </a>
                                )}
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-disabled)' }}>
                                    Created at
                                </p>
                                <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    {new Date(allianceCompany.created_at).toLocaleString()}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-disabled)' }}>
                                    Updated at
                                </p>
                                <p className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                                    {new Date(allianceCompany.updated_at).toLocaleString()}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
