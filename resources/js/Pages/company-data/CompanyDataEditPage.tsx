import * as React from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { ArrowLeft, Building2, Download, MapPin, Save, Share2, Trash2 } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useCompanyData } from '@/modules/company-data/hooks/useCompanyData';
import { useCompanyDataMutations } from '@/modules/company-data/hooks/useCompanyDataMutations';
import CompanySignaturePad from '@/modules/company-data/components/CompanySignaturePad';
import { PremiumField } from '@/shadcn/PremiumField';
import type { UpdateCompanyDataDTO } from '@/types/api';
import type { AuthPageProps } from '@/types/auth';

function parseNullableNumber(value: string): number | null {
  const parsed = Number.parseFloat(value);
  return Number.isFinite(parsed) ? parsed : null;
}

export default function CompanyDataEditPage(): React.JSX.Element {
  const { props } = usePage<AuthPageProps & { companyId?: string }>();
  const companyUuid = props.companyId;
  const { data: company, isPending } = useCompanyData(companyUuid);
  const { updateCompanyData } = useCompanyDataMutations();

  const [form, setForm] = React.useState<UpdateCompanyDataDTO>({
    company_name: '',
    name: '',
    email: '',
    phone: '',
    address: '',
    website: '',
    facebook_link: '',
    instagram_link: '',
    linkedin_link: '',
    twitter_link: '',
  });
  const [signatureDataUrl, setSignatureDataUrl] = React.useState<string | null>(null);
  const [removeSignature, setRemoveSignature] = React.useState<boolean>(false);

  React.useEffect(() => {
    if (!company) {
      return;
    }

    setForm({
      company_name: company.company_name,
      name: company.name ?? '',
      email: company.email ?? '',
      phone: company.phone ?? '',
      address: company.address ?? '',
      website: company.website ?? '',
      facebook_link: company.facebook_link ?? '',
      instagram_link: company.instagram_link ?? '',
      linkedin_link: company.linkedin_link ?? '',
      twitter_link: company.twitter_link ?? '',
      latitude: company.latitude,
      longitude: company.longitude,
    });
    setSignatureDataUrl(null);
    setRemoveSignature(false);
  }, [company]);

  function handleChange(e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>): void {
    const { name, value } = e.target;
    const key = name as keyof UpdateCompanyDataDTO;
    setForm((prev) => ({ ...prev, [key]: value }));
  }

  function handleSubmit(e: React.FormEvent): void {
    e.preventDefault();

    const payload: UpdateCompanyDataDTO = {
      ...form,
      signature_data_url: signatureDataUrl,
      remove_signature: removeSignature,
    };

    updateCompanyData.mutate(
      { companyUuid, payload },
      {
        onSuccess: () => {
          if (companyUuid) {
            router.visit('/company-data');
          }
        },
      },
    );
  }

  if (isPending) {
    return (
      <AppLayout>
        <div className="flex h-[50vh] flex-col items-center justify-center gap-4">
          <div className="h-10 w-10 animate-spin rounded-full border-4 border-(--accent-primary) border-t-transparent" />
          <p className="text-sm font-medium text-(--text-secondary)">Loading Corporate Identity...</p>
        </div>
      </AppLayout>
    );
  }

  return (
    <AppLayout>
      <Head title={`Company Profile | ${company?.company_name ?? 'Edit'}`} />
      <div className="mx-auto flex max-w-5xl flex-col gap-8 pb-12">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-4">
            <Link
              href="/company-data"
              className="flex h-10 w-10 items-center justify-center rounded-xl border border-(--border-default) bg-(--bg-card) text-(--text-muted) transition-all hover:bg-(--bg-hover)"
            >
              <ArrowLeft size={20} />
            </Link>
            <div>
              <h1 className="text-3xl font-extrabold tracking-tight text-(--text-primary)">Corporate Profile</h1>
              <p className="text-sm font-medium text-(--text-muted)">
                Manage legal and contact information for{' '}
                <span className="text-(--accent-primary)">{company?.company_name}</span>
              </p>
            </div>
          </div>

          <button
            onClick={handleSubmit}
            disabled={updateCompanyData.isPending}
            className="btn-modern btn-modern-primary flex items-center gap-2 px-8 py-3 font-bold disabled:opacity-50"
          >
            {updateCompanyData.isPending ? 'Syncing...' : <><Save size={18} /> Save Identity</>}
          </button>
        </div>

        <div className="grid grid-cols-1 gap-8 lg:grid-cols-3">
          <div className="space-y-8 lg:col-span-2">
            <section className="card-modern space-y-8 border border-(--border-default) p-8">
              <div className="flex items-center gap-3">
                <Building2 className="text-(--accent-primary)" size={24} />
                <h2 className="text-xl font-bold text-(--text-primary)">Core Information</h2>
              </div>

              <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div className="md:col-span-2">
                  <PremiumField
                    label="Official Company Name"
                    name="company_name"
                    value={form.company_name}
                    onChange={handleChange}
                    required
                    placeholder="Acme Corporation S.A."
                  />
                </div>
                <PremiumField label="Legal Representative" name="name" value={form.name ?? ''} onChange={handleChange} placeholder="John Smith" />
                <PremiumField label="Business Email" name="email" type="email" value={form.email ?? ''} onChange={handleChange} placeholder="billing@acme.com" />
                <PremiumField label="Public Phone" name="phone" value={form.phone ?? ''} onChange={handleChange} placeholder="+1 800-ACME-CORP" />
                <div className="md:col-span-2">
                  <PremiumField
                    label="Primary Address"
                    name="address"
                    value={form.address ?? ''}
                    onChange={handleChange}
                    isTextArea
                    placeholder="123 Corporate Way, Silicon Valley, CA"
                  />
                </div>
              </div>
            </section>

            <section className="card-modern space-y-8 border border-(--border-default) p-8">
              <div className="flex items-center gap-3">
                <Share2 className="text-(--accent-primary)" size={24} />
                <h2 className="text-xl font-bold text-(--text-primary)">Social Media & Public Presence</h2>
              </div>

              <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
                <div className="md:col-span-2">
                  <PremiumField label="Official Website" name="website" type="url" value={form.website ?? ''} onChange={handleChange} placeholder="https://acme.com" />
                </div>
                <PremiumField label="LinkedIn" name="linkedin_link" value={form.linkedin_link ?? ''} onChange={handleChange} placeholder="linkedin.com/company/acme" />
                <PremiumField label="Instagram" name="instagram_link" value={form.instagram_link ?? ''} onChange={handleChange} placeholder="instagram.com/acme" />
                <PremiumField label="Twitter / X" name="twitter_link" value={form.twitter_link ?? ''} onChange={handleChange} placeholder="x.com/acme" />
                <PremiumField label="Facebook" name="facebook_link" value={form.facebook_link ?? ''} onChange={handleChange} placeholder="facebook.com/acme" />
              </div>
            </section>

            <section className="card-modern space-y-6 border border-(--border-default) p-8">
              <div className="flex items-center justify-between gap-3">
                <h2 className="text-xl font-bold text-(--text-primary)">Signature Management</h2>
                {company?.signature_url && !removeSignature && (
                  <div className="flex items-center gap-2">
                    <a
                      href={company.signature_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-2 rounded-lg border border-(--border-default) px-3 py-2 text-xs font-semibold text-(--text-secondary)"
                    >
                      <Download size={14} /> Download
                    </a>
                    <button
                      type="button"
                      onClick={() => {
                        setRemoveSignature(true);
                        setSignatureDataUrl(null);
                      }}
                      className="inline-flex items-center gap-2 rounded-lg border border-(--border-default) px-3 py-2 text-xs font-semibold text-(--accent-error)"
                    >
                      <Trash2 size={14} /> Remove
                    </button>
                  </div>
                )}
              </div>

              {company?.signature_url && !removeSignature && (
                <div>
                  <p className="mb-2 text-xs font-semibold uppercase tracking-wider text-(--text-secondary)">Current Signature</p>
                  <img
                    src={company.signature_url}
                    alt="Current company signature"
                    className="h-24 w-full rounded-lg object-contain"
                    style={{ border: '1px solid var(--border-default)', background: 'var(--color-white)' }}
                  />
                </div>
              )}

              {removeSignature && (
                <p className="text-xs font-semibold uppercase tracking-wider text-(--accent-warning)">
                  Signature will be removed when you save.
                </p>
              )}

              <CompanySignaturePad
                value={signatureDataUrl}
                onChange={(next) => {
                  setSignatureDataUrl(next);
                  if (next) {
                    setRemoveSignature(false);
                  }
                }}
                disabled={updateCompanyData.isPending}
              />
            </section>
          </div>

          <div className="space-y-8">
            <section className="card-modern space-y-6 border border-(--border-subtle) bg-(--bg-surface) p-6">
              <div className="mb-2 flex items-center gap-3">
                <MapPin className="text-(--accent-primary)" size={20} />
                <h3 className="text-sm font-bold uppercase tracking-widest text-(--text-muted)">Geolocation</h3>
              </div>

              <div className="space-y-4">
                <PremiumField
                  label="Latitude"
                  name="latitude"
                  type="number"
                  step="any"
                  value={form.latitude?.toString() ?? ''}
                  onChange={(e) => setForm((p) => ({ ...p, latitude: parseNullableNumber(e.target.value) }))}
                />
                <PremiumField
                  label="Longitude"
                  name="longitude"
                  type="number"
                  step="any"
                  value={form.longitude?.toString() ?? ''}
                  onChange={(e) => setForm((p) => ({ ...p, longitude: parseNullableNumber(e.target.value) }))}
                />
              </div>
            </section>

            <section className="card-modern space-y-4 border border-(--border-subtle) bg-(--bg-surface) p-6">
              <h3 className="text-sm font-bold uppercase tracking-widest text-(--text-muted)">Status</h3>
              <div className="flex items-center justify-between rounded-xl border border-(--border-default) bg-(--bg-card) px-4 py-3">
                <span className="text-sm font-medium text-(--text-primary)">Public Visibility</span>
                <div className={`h-2.5 w-2.5 rounded-full ${!company?.deleted_at ? 'bg-(--accent-success)' : 'bg-(--accent-warning)'}`} />
              </div>
            </section>
          </div>
        </div>
      </div>
    </AppLayout>
  );
}
