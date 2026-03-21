import { Head, Link } from '@inertiajs/react';
import { Building2, Calendar, ChevronLeft, Globe, Mail, Pencil, Phone } from 'lucide-react';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import type { PublicCompany } from '@/modules/public-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';
import { formatDate, formatDateShort } from '@/utils/dateFormatter';

interface Props {
    publicCompany: { data: PublicCompany };
}

export default function PublicCompanyShowPage({ publicCompany }: Props): React.JSX.Element {
    const company = publicCompany.data;
    const fullAddress = [company.address, company.unit || company.address_2]
        .filter((segment): segment is string => typeof segment === 'string' && segment.length > 0)
        .join(', ');

    return (
        <>
            <Head title={company.public_company_name} />
            <AppLayout>
                <div className="mx-auto max-w-5xl space-y-8">
                    <div className="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href="/public-companies"
                            prefetch
                            className="flex items-center gap-2 text-sm font-bold"
                            style={{ color: 'var(--text-muted)' }}
                        >
                            <ChevronLeft size={18} />
                            Back to Companies
                        </Link>

                        <PermissionGuard permissions={['UPDATE_PUBLIC_COMPANY']}>
                            <Link
                                href={`/public-companies/${company.uuid}/edit`}
                                prefetch
                                className="btn-ghost flex items-center gap-2 px-6 py-2.5 font-bold"
                            >
                                <Pencil size={18} />
                                Edit Company
                            </Link>
                        </PermissionGuard>
                    </div>

                    <div className="overflow-hidden rounded-3xl border shadow-xl" style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}>
                        <div
                            className="border-b p-10"
                            style={{
                                borderColor: 'var(--border-subtle)',
                                background: 'color-mix(in srgb, var(--accent-primary) 10%, transparent)',
                            }}
                        >
                            <div className="flex items-center gap-3">
                                <div
                                    className="flex h-12 w-12 items-center justify-center rounded-2xl"
                                    style={{
                                        background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                                        color: 'var(--accent-primary)',
                                    }}
                                >
                                    <Building2 size={24} />
                                </div>
                                <div>
                                    <h1 className="text-4xl font-black tracking-tight" style={{ color: 'var(--text-primary)' }}>
                                        {company.public_company_name}
                                    </h1>
                                    <p className="mt-2 flex items-center gap-2 font-medium" style={{ color: 'var(--text-muted)' }}>
                                        <Calendar size={16} />
                                        Tracking since {formatDateShort(company.created_at)}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-12 p-10 md:grid-cols-2">
                            <section className="space-y-4">
                                <h2 className="text-xs font-black uppercase tracking-widest" style={{ color: 'var(--text-disabled)' }}>
                                    Contact Information
                                </h2>

                                <div className="space-y-4">
                                    <div className="flex items-center gap-4">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-2xl border" style={{ borderColor: 'var(--border-default)', color: 'var(--text-muted)' }}>
                                            <Phone size={20} />
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold" style={{ color: 'var(--text-primary)' }}>{company.phone ?? 'N/A'}</p>
                                            <p className="text-xs" style={{ color: 'var(--text-disabled)' }}>Primary Phone</p>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-4">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-2xl border" style={{ borderColor: 'var(--border-default)', color: 'var(--text-muted)' }}>
                                            <Mail size={20} />
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold" style={{ color: 'var(--text-primary)' }}>{company.email ?? 'N/A'}</p>
                                            <p className="text-xs" style={{ color: 'var(--text-disabled)' }}>Contact Email</p>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-4">
                                        <div className="flex h-12 w-12 items-center justify-center rounded-2xl border" style={{ borderColor: 'var(--border-default)', color: 'var(--text-muted)' }}>
                                            <Globe size={20} />
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold" style={{ color: 'var(--text-primary)' }}>
                                                {company.website ? (
                                                    <a href={company.website} target="_blank" rel="noreferrer" className="hover:underline" style={{ color: 'var(--accent-primary)' }}>
                                                        {company.website.replace(/^https?:\/\//, '')}
                                                    </a>
                                                ) : 'N/A'}
                                            </p>
                                            <p className="text-xs" style={{ color: 'var(--text-disabled)' }}>Website</p>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <section className="space-y-4">
                                <h2 className="text-xs font-black uppercase tracking-widest" style={{ color: 'var(--text-disabled)' }}>
                                    Address and Status
                                </h2>

                                <div className="rounded-2xl border p-5" style={{ borderColor: 'var(--border-default)' }}>
                                    <p className="text-sm font-bold leading-relaxed" style={{ color: 'var(--text-primary)' }}>
                                        {fullAddress || 'No address provided'}
                                    </p>
                                    <p className="mt-2 text-xs" style={{ color: 'var(--text-disabled)' }}>Registered Address</p>
                                </div>

                                <div className="rounded-2xl border p-5" style={{ borderColor: company.deleted_at ? 'var(--deleted-row-border)' : 'var(--border-default)', background: company.deleted_at ? 'var(--deleted-row-bg)' : 'transparent' }}>
                                    <p className="text-sm font-semibold" style={{ color: company.deleted_at ? 'var(--accent-error)' : 'var(--accent-success)' }}>
                                        {company.deleted_at ? 'Archived' : 'Active'}
                                    </p>
                                    <p className="mt-2 text-sm" style={{ color: 'var(--text-muted)' }}>
                                        Created {formatDate(company.created_at)}
                                    </p>
                                    <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                        Updated {formatDate(company.updated_at)}
                                    </p>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
