import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { AllianceCompany } from '@/modules/alliance-companies/types';
import { Pencil, ChevronLeft, Calendar, Phone, Mail, Globe, MapPin, ShieldEllipsis } from 'lucide-react';

interface Props {
    AllianceCompany: { data: AllianceCompany };
}

export default function AllianceCompanyShowPage({ AllianceCompany }: Props) {
    const company = AllianceCompany.data;

    return (
        <>
            <Head title={company.alliance_company_name} />
            <AppLayout>
                <div className="max-w-5xl mx-auto space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    <div className="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href="/alliance-companies"
                            className="flex items-center gap-2 text-sm font-bold text-(--text-muted) hover:text-(--accent-primary) transition-colors"
                        >
                            <ChevronLeft size={18} />
                            Back to Carriers
                        </Link>
                        <Link
                            href={`/alliance-companies/${company.uuid}/edit`}
                            className="bg-(--bg-card) border border-(--border-default) text-(--text-primary) font-bold py-2.5 px-6 rounded-xl hover:bg-(--bg-hover) hover:border-(--border-hover) transition-all flex items-center gap-2 shadow-sm"
                        >
                            <Pencil size={18} />
                            Edit Carrier
                        </Link>
                    </div>

                    <div className="rounded-3xl border border-(--border-default) bg-(--bg-card) shadow-2xl overflow-hidden">
                        <div className="bg-linear-to-r from-(--accent-primary)/20 to-transparent p-10 border-b border-(--border-subtle)">
                            <h1 className="text-4xl font-black tracking-tight text-(--text-primary)">
                                {company.alliance_company_name}
                            </h1>
                            <p className="mt-2 text-(--text-muted) font-medium flex items-center gap-2">
                                <Calendar size={16} />
                                Tracking since {new Date(company.created_at).toLocaleDateString()}
                            </p>
                        </div>

                        <div className="p-10 grid grid-cols-1 md:grid-cols-2 gap-12">
                            <div className="space-y-8">
                                <section className="space-y-4">
                                    <h3 className="text-xs font-black uppercase tracking-widest text-(--text-disabled)">
                                        Contact Information
                                    </h3>
                                    <div className="space-y-4">
                                        <div className="flex items-center gap-4 group">
                                            <div className="p-3 rounded-2xl bg-(--bg-app) border border-(--border-default) text-(--text-muted) group-hover:text-(--accent-primary) transition-colors">
                                                <Phone size={20} />
                                            </div>
                                            <div>
                                                <p className="text-sm font-bold text-(--text-primary)">{company.phone || 'N/A'}</p>
                                                <p className="text-xs text-(--text-disabled)">Primary Phone</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-4 group">
                                            <div className="p-3 rounded-2xl bg-(--bg-app) border border-(--border-default) text-(--text-muted) group-hover:text-(--accent-primary) transition-colors">
                                                <Mail size={20} />
                                            </div>
                                            <div>
                                                <p className="text-sm font-bold text-(--text-primary)">{company.email || 'N/A'}</p>
                                                <p className="text-xs text-(--text-disabled)">Claims Email</p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-4 group">
                                            <div className="p-3 rounded-2xl bg-(--bg-app) border border-(--border-default) text-(--text-muted) group-hover:text-(--accent-primary) transition-colors">
                                                <Globe size={20} />
                                            </div>
                                            <div>
                                                <p className="text-sm font-bold text-(--text-primary)">
                                                    {company.website ? (
                                                        <a href={company.website} target="_blank" className="hover:underline text-(--accent-primary)">
                                                            {company.website.replace(/^https?:\/\//, '')}
                                                        </a>
                                                    ) : 'N/A'}
                                                </p>
                                                <p className="text-xs text-(--text-disabled)">Carrier Portal</p>
                                            </div>
                                        </div>
                                    </div>
                                </section>
                            </div>

                            <div className="space-y-8">
                                <section className="space-y-4">
                                    <h3 className="text-xs font-black uppercase tracking-widest text-(--text-disabled)">
                                        Headquarters
                                    </h3>
                                    <div className="flex items-start gap-4">
                                        <div className="p-3 rounded-2xl bg-(--bg-app) border border-(--border-default) text-(--text-muted)">
                                            <MapPin size={20} />
                                        </div>
                                        <div>
                                            <p className="text-sm font-bold text-(--text-primary) leading-relaxed">
                                                {company.address || 'No address provided'}
                                            </p>
                                        </div>
                                    </div>
                                </section>

                                {company.deleted_at && (
                                    <div className="p-6 rounded-2xl bg-(--accent-error)/5 border border-(--accent-error)/20 text-(--accent-error) flex items-center gap-4">
                                        <div 
                                            className="p-2 rounded-lg" 
                                            style={{ background: 'color-mix(in srgb, var(--accent-error) 10%, transparent)' }}
                                        >
                                            <ShieldEllipsis size={20} />
                                        </div>
                                        <div className="text-xs font-bold leading-tight">
                                            This carrier is currently ARCHIVED.
                                            Restore it to resume active management.
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
