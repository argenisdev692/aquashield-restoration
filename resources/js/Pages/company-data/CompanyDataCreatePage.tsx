import * as React from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useCompanyDataMutations } from '@/modules/company-data/hooks/useCompanyDataMutations';
import type { CreateCompanyDataDTO } from '@/modules/company-data/types';
import { type UserAddressAutocompleteValue, useGoogleMapsAddressAutocomplete } from '@/modules/users/hooks/useGoogleMapsAddressAutocomplete';
import CompanySignaturePad from '@/modules/company-data/components/CompanySignaturePad';
import type { AuthPageProps } from '@/types/auth';

// ══════════════════════════════════════════════════════════════
// Icons
// ══════════════════════════════════════════════════════════════
const ic = {
  w: 16, h: 16, viewBox: '0 0 24 24', fill: 'none',
  stroke: 'currentColor', strokeWidth: 2,
  strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const,
};
const IconArrowLeft = () => <svg {...ic}><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>;
const IconSave = () => <svg {...ic}><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>;

function buildFullUsAddress(value: UserAddressAutocompleteValue): string {
  return [value.address, value.city, value.state, value.zip_code, value.country]
    .map((segment) => segment.trim())
    .filter((segment) => segment.length > 0)
    .join(', ');
}

// ══════════════════════════════════════════════════════════════
// CompanyDataCreatePage
// ══════════════════════════════════════════════════════════════
export default function CompanyDataCreatePage(): React.JSX.Element {
  const { createCompanyData: createMutation } = useCompanyDataMutations();
  const { props } = usePage<AuthPageProps>();
  const currentUserUuid = props.auth.user?.uuid ?? '';
  const [formData, setFormData] = React.useState<CreateCompanyDataDTO>({
    user_uuid: currentUserUuid,
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
  const [address2, setAddress2] = React.useState<string>('');
  const addressInputRef = React.useRef<HTMLInputElement | null>(null);
  const address2InputRef = React.useRef<HTMLInputElement | null>(null);

  const handleChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
  };

  const handleAddressSelected = React.useCallback((value: UserAddressAutocompleteValue): void => {
    setFormData((prev) => ({ ...prev, address: buildFullUsAddress(value) }));
    setAddress2('');
    window.setTimeout(() => {
      address2InputRef.current?.focus();
    }, 0);
  }, []);

  const { isLoading, isReady, errorMessage } = useGoogleMapsAddressAutocomplete({
    inputRef: addressInputRef,
    onAddressSelected: handleAddressSelected,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    createMutation.mutate(
      {
        ...formData,
        address_2: address2.trim().length > 0 ? address2.trim() : null,
        signature_data_url: signatureDataUrl,
      },
      {
        onSuccess: () => {
          router.visit('/company-data');
        },
      },
    );
  };

  return (
    <AppLayout>
      <Head title="Create Company Profile" />
      <div className="mx-auto max-w-[800px] px-4 sm:px-0">
        
        {/* ── Header ── */}
        <div className="mb-6 flex items-center justify-between">
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
              <h1 className="text-xl font-bold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                New Company Profile
              </h1>
              <p className="text-xs mt-1" style={{ color: 'var(--text-muted)' }}>
                Enter the details below to register a new corporate entity.
              </p>
            </div>
          </div>
          <button
            onClick={handleSubmit}
            disabled={createMutation.isPending}
            className="btn-primary inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold disabled:opacity-50"
          >
            {createMutation.isPending ? 'Saving...' : <><IconSave /> Save Profile</>}
          </button>
        </div>

        {/* ── Form Card ── */}
        <div className="card p-5 sm:p-8">
          <form onSubmit={handleSubmit} className="space-y-8">
            
            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
              {/* Company Name */}
              <div>
                <label className="input-label" htmlFor="company_name">Company Name *</label>
                <input
                  id="company_name"
                  name="company_name"
                  type="text"
                  required
                  value={formData.company_name}
                  onChange={handleChange}
                  className="input"
                  placeholder="e.g. Acme Corp"
                />
              </div>

              {/* Representative Name */}
              <div>
                <label className="input-label" htmlFor="name">Representative Name</label>
                <input
                  id="name"
                  name="name"
                  type="text"
                  value={formData.name || ''}
                  onChange={handleChange}
                  className="input"
                  placeholder="e.g. Jane Doe"
                />
              </div>

              {/* Email */}
              <div>
                <label className="input-label" htmlFor="email">Contact Email</label>
                <input
                  id="email"
                  name="email"
                  type="email"
                  value={formData.email || ''}
                  onChange={handleChange}
                  className="input"
                  placeholder="contact@acmecorp.com"
                />
              </div>

              {/* Phone */}
              <div>
                <label className="input-label" htmlFor="phone">Phone Number</label>
                <input
                  id="phone"
                  name="phone"
                  type="text"
                  value={formData.phone || ''}
                  onChange={handleChange}
                  className="input"
                  placeholder="+1 (555) 000-0000"
                />
              </div>
              
              {/* Website */}
              <div className="md:col-span-2">
                <label className="input-label" htmlFor="website">Website URL</label>
                <input
                  id="website"
                  name="website"
                  type="url"
                  value={formData.website || ''}
                  onChange={handleChange}
                  className="input"
                  placeholder="https://www.acmecorp.com"
                />
              </div>

              {/* Address */}
              <div className="md:col-span-2">
                <label className="input-label" htmlFor="address">Address</label>
                <input
                  id="address"
                  name="address"
                  value={formData.address || ''}
                  onChange={handleChange}
                  ref={addressInputRef}
                  autoComplete="street-address"
                  className="input"
                  placeholder="Start typing a USA address"
                />
              </div>

              <div className="md:col-span-2">
                <label className="input-label" htmlFor="address_2">Address 2</label>
                <input
                  id="address_2"
                  name="address_2"
                  type="text"
                  value={address2}
                  onChange={(e) => setAddress2(e.target.value)}
                  ref={address2InputRef}
                  autoComplete="address-line2"
                  className="input"
                  placeholder="Apartment, suite, unit, building, floor"
                />
              </div>

              <div className="md:col-span-2">
                <p className="text-xs" style={{ color: 'var(--text-muted)' }}>
                  {errorMessage ?? (isReady ? 'Autocomplete limited to USA addresses. Address 2 remains manual.' : isLoading ? 'Loading Google Maps autocomplete...' : 'Google Maps autocomplete is preparing...')}
                </p>
              </div>
            </div>

            <hr style={{ borderColor: 'var(--border-subtle)', margin: '24px 0' }} />

            <h3 className="mb-4 text-sm font-semibold" style={{ color: 'var(--text-secondary)' }}>
              Social Links
            </h3>

            <div className="grid grid-cols-1 gap-6 md:grid-cols-2">
              <div>
                <label className="input-label" htmlFor="linkedin_link">LinkedIn</label>
                <input
                  id="linkedin_link"
                  name="linkedin_link"
                  type="url"
                  value={formData.linkedin_link || ''}
                  onChange={handleChange}
                  className="input"
                  placeholder="https://linkedin.com/company/acmecorp"
                />
              </div>
              <div>
                <label className="input-label" htmlFor="twitter_link">Twitter (X)</label>
                <input
                  id="twitter_link"
                  name="twitter_link"
                  type="url"
                  value={formData.twitter_link || ''}
                  onChange={handleChange}
                  className="input"
                  placeholder="https://twitter.com/acmecorp"
                />
              </div>
              <div>
                <label className="input-label" htmlFor="facebook_link">Facebook</label>
                <input
                  id="facebook_link"
                  name="facebook_link"
                  type="url"
                  value={formData.facebook_link || ''}
                  onChange={handleChange}
                  className="input"
                  placeholder="https://facebook.com/acmecorp"
                />
              </div>
              <div>
                <label className="input-label" htmlFor="instagram_link">Instagram</label>
                <input
                  id="instagram_link"
                  name="instagram_link"
                  type="url"
                  value={formData.instagram_link || ''}
                  onChange={handleChange}
                  className="input"
                  placeholder="https://instagram.com/acmecorp"
                />
              </div>
            </div>

            <hr style={{ borderColor: 'var(--border-subtle)', margin: '24px 0' }} />

            <CompanySignaturePad value={signatureDataUrl} onChange={setSignatureDataUrl} disabled={createMutation.isPending} />

          </form>
        </div>
      </div>
    </AppLayout>
  );
}
