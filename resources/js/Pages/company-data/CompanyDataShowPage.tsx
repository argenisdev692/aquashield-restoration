import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import AppLayout from '@/pages/layouts/AppLayout';
import { useSingleCompanyData } from '@/modules/company-data/hooks/useCompanyData';
import { useCompanyDataMutations } from '@/modules/company-data/hooks/useCompanyDataMutations';
import CompanyDataStatusBadge from '@/modules/company-data/components/CompanyDataStatusBadge';
import type { PageProps } from '@inertiajs/core';

// ══════════════════════════════════════════════════════════════
// Icons
// ══════════════════════════════════════════════════════════════
const ic = {
  w: 16, h: 16, viewBox: '0 0 24 24', fill: 'none',
  stroke: 'currentColor', strokeWidth: 2,
  strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const,
};
const IconArrowLeft = () => <svg {...ic}><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>;
const IconEdit = () => <svg {...ic}><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>;
const IconMail = () => <svg {...ic}><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>;
const IconPhone = () => <svg {...ic}><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>;
const IconGlobe = () => <svg {...ic}><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>;
const IconMapPin = () => <svg {...ic}><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>;
const IconBuilding = () => <svg {...ic}><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>;
const IconTrash = () => <svg {...ic}><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>;
const IconDownload = () => <svg {...ic}><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>;

// ══════════════════════════════════════════════════════════════
// CompanyDataShowPage
// ══════════════════════════════════════════════════════════════
export default function CompanyDataShowPage(): React.JSX.Element {
  const { props } = usePage<PageProps & { companyId: string }>();
  const urlParts = window.location.pathname.split('/');
  const finalUuid = props.companyId || urlParts[urlParts.length - 1]; 

  const { data: company, isPending, isError } = useSingleCompanyData(finalUuid);
  const { updateCompanyData } = useCompanyDataMutations();

  function handleRemoveSignature(): void {
    if (!company) {
      return;
    }

    updateCompanyData.mutate({
      companyUuid: company.uuid,
      payload: {
        company_name: company.company_name,
        name: company.name,
        email: company.email,
        phone: company.phone,
        address: company.address,
        address_2: company.address_2,
        website: company.website,
        facebook_link: company.facebook_link,
        instagram_link: company.instagram_link,
        linkedin_link: company.linkedin_link,
        twitter_link: company.twitter_link,
        latitude: company.latitude,
        longitude: company.longitude,
        remove_signature: true,
      },
    });
  }

  if (isPending) {
    return (
      <AppLayout>
        <div className="flex h-[50vh] items-center justify-center">
          <p style={{ color: 'var(--text-muted)' }}>Loading company profile...</p>
        </div>
      </AppLayout>
    );
  }

  if (isError || !company) {
    return (
      <AppLayout>
        <div className="flex h-[50vh] items-center justify-center">
          <p style={{ color: 'var(--accent-error)' }}>Failed to load company profile.</p>
        </div>
      </AppLayout>
    );
  }

  return (
    <AppLayout>
      <Head title={`${company.company_name} Profile`} />
      <div className="mx-auto max-w-[900px]">
        
        {/* ── Header ── */}
        <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-4">
            <Link
              href="/company-data"
              aria-label="Back to company profiles"
              title="Back to company profiles"
              className="btn-ghost flex h-9 w-9 items-center justify-center"
              style={{ color: 'var(--text-muted)' }}
            >
              <IconArrowLeft />
            </Link>
            <div>
              <h1 className="text-2xl font-bold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                {company.company_name}
              </h1>
              <div className="mt-1 flex items-center gap-3">
                <CompanyDataStatusBadge status={company.deleted_at ? 'deleted' : 'active'} />
                <span className="text-xs" style={{ color: 'var(--text-muted)' }}>
                  ID: {company.uuid.substring(0, 8)}...
                </span>
                <span className="text-xs" style={{ color: 'var(--text-muted)' }}>
                  Registered: {new Date(company.created_at).toLocaleDateString()}
                </span>
              </div>
            </div>
          </div>
          <PermissionGuard permissions={['UPDATE_COMPANY_DATA']}>
            <Link
              href={`/company-data/${company.uuid}/edit`}
              className="btn-primary inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold"
            >
              <IconEdit /> Edit Profile
            </Link>
          </PermissionGuard>
        </div>

        {/* ── Grid Layout ── */}
        <div className="grid grid-cols-1 gap-6 md:grid-cols-3">
            
          {/* Main Info Column */}
          <div className="md:col-span-2 space-y-6">
            
            {/* Contact Details Card */}
            <div className="card p-6 shadow-md">
              <h2 className="mb-4 text-base font-semibold" style={{ color: 'var(--text-primary)' }}>
                Contact Information
              </h2>
              <div className="space-y-4">
                <div className="flex items-start gap-3">
                  <div className="pt-0.5" style={{ color: 'var(--text-muted)' }}>
                    <IconBuilding />
                  </div>
                  <div>
                    <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-secondary)' }}>
                      Representative Name
                    </p>
                    <p className="mt-1 text-sm" style={{ color: 'var(--text-secondary)' }}>
                      {company.name ?? 'Not specified'}
                    </p>
                  </div>
                </div>

                <div className="flex items-start gap-3">
                  <div className="pt-0.5" style={{ color: 'var(--text-muted)' }}>
                    <IconMail />
                  </div>
                  <div>
                    <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-secondary)' }}>
                      Email Address
                    </p>
                    <p className="mt-1 text-sm" style={{ color: 'var(--text-secondary)' }}>
                      {company.email ? (
                        <a href={`mailto:${company.email}`} className="hover:underline" style={{ color: 'var(--accent-info)' }}>
                           {company.email}
                        </a>
                      ) : 'Not specified'}
                    </p>
                  </div>
                </div>

                <div className="flex items-start gap-3">
                  <div className="pt-0.5" style={{ color: 'var(--text-muted)' }}>
                    <IconPhone />
                  </div>
                  <div>
                    <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-secondary)' }}>
                      Phone Number
                    </p>
                    <p className="mt-1 text-sm" style={{ color: 'var(--text-secondary)' }}>
                      {company.phone ? (
                        <a href={`tel:${company.phone}`} className="hover:underline" style={{ color: 'var(--accent-info)' }}>
                           {company.phone}
                        </a>
                      ) : 'Not specified'}
                    </p>
                  </div>
                </div>

                <div className="flex items-start gap-3">
                  <div className="pt-0.5" style={{ color: 'var(--text-muted)' }}>
                    <IconGlobe />
                  </div>
                  <div>
                    <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-secondary)' }}>
                      Website
                    </p>
                    <p className="mt-1 text-sm" style={{ color: 'var(--text-secondary)' }}>
                      {company.website ? (
                        <a href={company.website} target="_blank" rel="noopener noreferrer" className="hover:underline" style={{ color: 'var(--accent-info)' }}>
                           {company.website}
                        </a>
                      ) : 'Not specified'}
                    </p>
                  </div>
                </div>

                <div className="flex items-start gap-3">
                  <div className="pt-0.5" style={{ color: 'var(--text-muted)' }}>
                    <IconMapPin />
                  </div>
                  <div>
                    <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-secondary)' }}>
                      Address
                    </p>
                    <p className="mt-1 text-sm whitespace-pre-wrap leading-relaxed" style={{ color: 'var(--text-secondary)' }}>
                      {company.address ?? 'Not specified'}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            {/* Geographic Coordinates Card (if map is needed later) */}
            <div className="card p-6 shadow-md">
               <h2 className="mb-4 text-base font-semibold" style={{ color: 'var(--text-primary)' }}>
                Geographic Data
              </h2>
              <div className="grid grid-cols-2 gap-4">
                <div>
                   <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-secondary)' }}>Latitude</p>
                   <p className="mt-1 text-sm font-mono" style={{ color: 'var(--text-secondary)' }}>{company.latitude ?? '—'}</p>
                </div>
                <div>
                   <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-secondary)' }}>Longitude</p>
                   <p className="mt-1 text-sm font-mono" style={{ color: 'var(--text-secondary)' }}>{company.longitude ?? '—'}</p>
                </div>
              </div>
            </div>

          </div>

          {/* Social Links & Metadata Column */}
          <div className="space-y-6">
            <div className="card p-6">
              <h2 className="mb-4 text-base font-semibold" style={{ color: 'var(--text-primary)' }}>
                Signature
              </h2>
              {company.signature_url ? (
                <div className="space-y-3">
                  <img
                    src={company.signature_url}
                    alt="Company signature"
                    className="h-24 w-full rounded-lg object-contain"
                    style={{
                      border: '1px solid var(--border-default)',
                      background: 'var(--color-white)',
                    }}
                  />
                  <div className="flex flex-wrap items-center gap-2">
                    <a
                      href={company.signature_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="btn-ghost inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold"
                    >
                      <IconDownload /> Download
                    </a>
                    <PermissionGuard permissions={['DELETE_COMPANY_DATA']}>
                      <button
                        type="button"
                        onClick={handleRemoveSignature}
                        disabled={updateCompanyData.isPending}
                        className="btn-ghost inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold disabled:opacity-50"
                        style={{ color: 'var(--accent-error)' }}
                      >
                        <IconTrash /> Delete
                      </button>
                    </PermissionGuard>
                    <PermissionGuard permissions={['UPDATE_COMPANY_DATA']}>
                      <Link
                        href={`/company-data/${company.uuid}/edit`}
                        className="btn-ghost inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold"
                      >
                        <IconEdit /> Edit
                      </Link>
                    </PermissionGuard>
                  </div>
                </div>
              ) : (
                <div className="space-y-2">
                  <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                    No signature registered.
                  </p>
                  <PermissionGuard permissions={['UPDATE_COMPANY_DATA']}>
                    <Link
                      href={`/company-data/${company.uuid}/edit`}
                      className="btn-ghost inline-flex items-center gap-2 px-3 py-2 text-xs font-semibold"
                    >
                      <IconEdit /> Add Signature
                    </Link>
                  </PermissionGuard>
                </div>
              )}
            </div>

            <div className="card p-6">
              <h2 className="mb-4 text-base font-semibold" style={{ color: 'var(--text-primary)' }}>
                Social Profiles
              </h2>
              <div className="space-y-4">
                {[
                  { label: 'LinkedIn', url: company.linkedin_link },
                  { label: 'Twitter', url: company.twitter_link },
                  { label: 'Facebook', url: company.facebook_link },
                  { label: 'Instagram', url: company.instagram_link },
                ].map((social) => (
                  <div key={social.label}>
                    <p className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-secondary)' }}>
                      {social.label}
                    </p>
                    <p className="mt-1 text-sm truncate" style={{ color: 'var(--text-secondary)' }}>
                      {social.url ? (
                        <a href={social.url} target="_blank" rel="noopener noreferrer" className="hover:underline" style={{ color: 'var(--accent-info)' }}>
                           {new URL(social.url).hostname.replace('www.', '')}
                        </a>
                      ) : '—'}
                    </p>
                  </div>
                ))}
              </div>
            </div>

            <div className="card p-6">
               <h2 className="mb-4 text-base font-semibold" style={{ color: 'var(--text-primary)' }}>
                Metadata
              </h2>
              <div className="space-y-4 text-sm" style={{ color: 'var(--text-secondary)' }}>
                 <div className="flex justify-between border-b pb-2" style={{ borderColor: 'var(--border-subtle)' }}>
                     <span style={{ color: 'var(--text-secondary)' }}>Owner User UUID:</span>
                     <span className="font-mono">{company.user_uuid}</span>
                 </div>
                 <div className="flex justify-between border-b pb-2" style={{ borderColor: 'var(--border-subtle)' }}>
                     <span style={{ color: 'var(--text-secondary)' }}>Created At:</span>
                     <span>{new Date(company.created_at).toLocaleString()}</span>
                 </div>
                 <div className="flex justify-between border-b pb-2" style={{ borderColor: 'var(--border-subtle)' }}>
                     <span style={{ color: 'var(--text-secondary)' }}>Updated At:</span>
                     <span>{company.updated_at ? new Date(company.updated_at).toLocaleString() : 'Never'}</span>
                 </div>
              </div>
            </div>

          </div>

        </div>
      </div>
    </AppLayout>
  );
}
