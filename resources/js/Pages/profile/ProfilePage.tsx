import * as React from 'react';
import { Head, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Pages/layouts/AppLayout';
import { AuthInput } from '@/Pages/auth/components/AuthInput';
import { PasswordStrengthBar } from '@/Pages/auth/components/PasswordStrengthBar';
import { validateProfile, validateNewPassword } from '@/modules/auth/helpers/validators';
import type { AuthUser, FormStatus, ProfilePageProps } from '@/types/auth';

/** ── Eye Toggle ── */
function EyeToggle({ show, onToggle }: { show: boolean; onToggle: () => void }): React.JSX.Element {
  return (
    <button
      type="button"
      onClick={onToggle}
      className="transition-colors"
      style={{ color: 'var(--text-muted)' }}
      aria-label={show ? 'Hide password' : 'Show password'}
    >
      {show ? (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94" />
          <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19" />
          <line x1="1" y1="1" x2="23" y2="23" />
        </svg>
      ) : (
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
          <circle cx="12" cy="12" r="3" />
        </svg>
      )}
    </button>
  );
}

/** ── Section wrapper ── */
function Section({
  title,
  description,
  children,
}: {
  title: string;
  description: string;
  children: React.ReactNode;
}): React.JSX.Element {
  return (
    <div
      className="rounded-xl p-6"
      style={{
        background: 'var(--bg-card)',
        border: '1px solid var(--border-default)',
      }}
    >
      <h3
        className="text-lg font-bold"
        style={{ color: 'var(--text-primary)' }}
      >
        {title}
      </h3>
      <p
        className="mt-1 mb-6 text-sm"
        style={{ color: 'var(--text-muted)' }}
      >
        {description}
      </p>
      {children}
    </div>
  );
}

export default function ProfilePage(): React.JSX.Element {
  const { auth, flash, errors: serverErrors } = usePage<ProfilePageProps>().props;
  const user: AuthUser = auth.user;

  // ── Profile form state ──
  const [profileStatus, setProfileStatus] = React.useState<FormStatus>('idle');
  const [profileErrors, setProfileErrors] = React.useState<Record<string, string>>({});
  const [profileData, setProfileData] = React.useState({
    name: user.name ?? '',
    last_name: user.last_name ?? '',
    username: user.username ?? '',
    email: user.email ?? '',
    phone: user.phone ?? '',
    date_of_birth: user.date_of_birth ?? '',
    address: user.address ?? '',
    zip_code: user.zip_code ?? '',
    city: user.city ?? '',
    state: user.state ?? '',
    country: user.country ?? '',
    gender: user.gender ?? '',
  });

  // ── Password form state ──
  const [passwordStatus, setPasswordStatus] = React.useState<FormStatus>('idle');
  const [passwordErrors, setPasswordErrors] = React.useState<Record<string, string>>({});
  const [currentPassword, setCurrentPassword] = React.useState('');
  const [newPassword, setNewPassword] = React.useState('');
  const [confirmPassword, setConfirmPassword] = React.useState('');
  const [showCurrent, setShowCurrent] = React.useState(false);
  const [showNew, setShowNew] = React.useState(false);
  const [showConfirm, setShowConfirm] = React.useState(false);

  function updateField(field: keyof typeof profileData, value: string): void {
    setProfileData((prev) => ({ ...prev, [field]: value }));
  }

  /** ── Save Profile ── */
  function handleProfileSubmit(e: React.FormEvent): void {
    e.preventDefault();
    const validation = validateProfile({
      name: profileData.name,
      email: profileData.email,
      phone: profileData.phone || null,
    });
    if (!validation.valid) {
      setProfileErrors(validation.errors);
      return;
    }
    setProfileErrors({});
    setProfileStatus('loading');

    router.put(
      '/user/profile-information',
      profileData,
      {
        onSuccess: () => setProfileStatus('success'),
        onError: (errs) => {
          setProfileStatus('error');
          setProfileErrors(errs);
        },
        onFinish: () => {
          setTimeout(() => setProfileStatus('idle'), 3000);
        },
      },
    );
  }

  /** ── Change Password ── */
  function handlePasswordSubmit(e: React.FormEvent): void {
    e.preventDefault();
    const validation = validateNewPassword(newPassword, confirmPassword);
    if (!validation.valid) {
      setPasswordErrors(validation.errors);
      return;
    }
    if (!currentPassword) {
      setPasswordErrors({ current_password: 'Current password is required' });
      return;
    }
    setPasswordErrors({});
    setPasswordStatus('loading');

    router.put(
      '/user/password',
      {
        current_password: currentPassword,
        password: newPassword,
        password_confirmation: confirmPassword,
      },
      {
        onSuccess: () => {
          setPasswordStatus('success');
          setCurrentPassword('');
          setNewPassword('');
          setConfirmPassword('');
        },
        onError: (errs) => {
          setPasswordStatus('error');
          setPasswordErrors(errs);
        },
        onFinish: () => {
          setTimeout(() => setPasswordStatus('idle'), 3000);
        },
      },
    );
  }

  return (
    <>
      <Head title="Profile — AquaShield" />
      <AppLayout>
        <div className="mx-auto max-w-3xl space-y-6">
          {/* Page Header */}
          <div className="flex items-center gap-4">
            {/* Avatar */}
            <div
              className="flex h-16 w-16 items-center justify-center rounded-2xl text-xl font-bold"
              style={{
                background: 'linear-gradient(135deg, var(--color-aqua) 0%, var(--color-aqua-dark) 100%)',
                color: 'var(--color-white)',
                boxShadow: '0 8px 24px rgba(0, 181, 226, 0.25)',
              }}
            >
              {(user.name?.[0] ?? 'U').toUpperCase()}
              {(user.last_name?.[0] ?? '').toUpperCase()}
            </div>
            <div>
              <h1
                className="text-2xl font-bold"
                style={{ color: 'var(--text-primary)' }}
              >
                {user.name} {user.last_name ?? ''}
              </h1>
              <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                {user.email}
              </p>
              {/* Role badge (read-only) */}
              {user.roles && user.roles.length > 0 && (
                <div className="mt-1 flex gap-2">
                  {user.roles.map((role) => (
                    <span
                      key={role}
                      className="inline-block rounded-full px-3 py-0.5 text-xs font-semibold uppercase tracking-wider"
                      style={{
                        background: 'color-mix(in srgb, var(--color-aqua) 13%, transparent)',
                        color: 'var(--color-aqua)',
                        border: '1px solid color-mix(in srgb, var(--color-aqua) 27%, transparent)',
                      }}
                    >
                      {role}
                    </span>
                  ))}
                </div>
              )}
            </div>
          </div>

          {/* Flash messages */}
          {flash?.success && (
            <div
              className="flex items-center gap-2 rounded-lg px-4 py-3 text-sm"
              style={{
                background: 'rgba(34, 197, 94, 0.1)',
                border: '1px solid rgba(34, 197, 94, 0.25)',
                color: 'var(--accent-success)',
              }}
              role="status"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14" />
                <polyline points="22 4 12 14.01 9 11.01" />
              </svg>
              {flash.success}
            </div>
          )}

          {/* ══════════════════════════════════════════
              PERSONAL INFORMATION
              ══════════════════════════════════════════ */}
          <Section
            title="Personal Information"
            description="Update your personal details. Your role cannot be changed here."
          >
            <form onSubmit={handleProfileSubmit} className="space-y-4" noValidate>
              {/* Name row */}
              <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <AuthInput
                  label="First Name"
                  type="text"
                  value={profileData.name}
                  onChange={(e) => updateField('name', e.target.value)}
                  error={profileErrors.name ?? serverErrors.name}
                  disabled={profileStatus === 'loading'}
                />
                <AuthInput
                  label="Last Name"
                  type="text"
                  value={profileData.last_name}
                  onChange={(e) => updateField('last_name', e.target.value)}
                  error={profileErrors.last_name}
                  disabled={profileStatus === 'loading'}
                />
              </div>

              <AuthInput
                label="Username"
                type="text"
                value={profileData.username}
                onChange={(e) => updateField('username', e.target.value)}
                error={profileErrors.username ?? serverErrors.username}
                disabled={profileStatus === 'loading'}
              />

              <AuthInput
                label="Email"
                type="email"
                value={profileData.email}
                onChange={(e) => updateField('email', e.target.value)}
                error={profileErrors.email ?? serverErrors.email}
                disabled={profileStatus === 'loading'}
              />

              <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <AuthInput
                  label="Phone"
                  type="tel"
                  value={profileData.phone}
                  onChange={(e) => updateField('phone', e.target.value)}
                  error={profileErrors.phone ?? serverErrors.phone}
                  disabled={profileStatus === 'loading'}
                />
                <AuthInput
                  label="Date of Birth"
                  type="date"
                  value={profileData.date_of_birth}
                  onChange={(e) => updateField('date_of_birth', e.target.value)}
                  error={profileErrors.date_of_birth}
                  disabled={profileStatus === 'loading'}
                />
              </div>

              {/* Gender */}
              <div className="space-y-1.5">
                <label
                  className="block text-xs font-semibold uppercase tracking-wider"
                  style={{ color: 'var(--text-muted)' }}
                >
                  Gender
                </label>
                <select
                  value={profileData.gender}
                  onChange={(e) => updateField('gender', e.target.value)}
                  disabled={profileStatus === 'loading'}
                  className="h-11 w-full rounded-lg border px-4 text-sm transition-all duration-200 focus:outline-none"
                  style={{
                    background: 'var(--input-bg)',
                    borderColor: 'var(--input-border)',
                    color: 'var(--input-text)',
                    borderRadius: 'var(--input-radius)',
                    fontFamily: 'var(--font-sans)',
                  }}
                >
                  <option value="">Select...</option>
                  <option value="male">Male</option>
                  <option value="female">Female</option>
                  <option value="other">Other</option>
                  <option value="prefer_not_to_say">Prefer not to say</option>
                </select>
              </div>

              {/* Address section */}
              <AuthInput
                label="Address"
                type="text"
                value={profileData.address}
                onChange={(e) => updateField('address', e.target.value)}
                error={profileErrors.address}
                disabled={profileStatus === 'loading'}
              />

              <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
                <AuthInput
                  label="City"
                  type="text"
                  value={profileData.city}
                  onChange={(e) => updateField('city', e.target.value)}
                  error={profileErrors.city}
                  disabled={profileStatus === 'loading'}
                />
                <AuthInput
                  label="State"
                  type="text"
                  value={profileData.state}
                  onChange={(e) => updateField('state', e.target.value)}
                  error={profileErrors.state}
                  disabled={profileStatus === 'loading'}
                />
                <AuthInput
                  label="Zip Code"
                  type="text"
                  value={profileData.zip_code}
                  onChange={(e) => updateField('zip_code', e.target.value)}
                  error={profileErrors.zip_code}
                  disabled={profileStatus === 'loading'}
                />
                <AuthInput
                  label="Country"
                  type="text"
                  value={profileData.country}
                  onChange={(e) => updateField('country', e.target.value)}
                  error={profileErrors.country}
                  disabled={profileStatus === 'loading'}
                />
              </div>

              {/* Submit */}
              <div className="flex items-center gap-4 pt-2">
                <button
                  type="submit"
                  disabled={profileStatus === 'loading'}
                  className="flex h-10 items-center justify-center rounded-lg px-6 text-sm font-semibold transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-60"
                  style={{
                    background: 'linear-gradient(135deg, var(--color-aqua) 0%, var(--color-aqua-dark) 100%)',
                    color: 'var(--color-white)',
                    boxShadow: '0 4px 16px rgba(0, 181, 226, 0.2)',
                  }}
                >
                  {profileStatus === 'loading' ? (
                    <svg className="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" strokeDasharray="60" strokeLinecap="round" />
                    </svg>
                  ) : (
                    'Save Changes'
                  )}
                </button>

                {profileStatus === 'success' && (
                  <span className="text-sm font-medium" style={{ color: 'var(--accent-success)' }}>
                    ✓ Saved
                  </span>
                )}
              </div>
            </form>
          </Section>

          {/* ══════════════════════════════════════════
              CHANGE PASSWORD
              ══════════════════════════════════════════ */}
          <Section
            title="Change Password"
            description="Ensure your account is using a long, random password to stay secure."
          >
            <form onSubmit={handlePasswordSubmit} className="space-y-4" noValidate>
              <AuthInput
                label="Current Password"
                type={showCurrent ? 'text' : 'password'}
                placeholder="••••••••"
                value={currentPassword}
                onChange={(e) => setCurrentPassword(e.target.value)}
                error={passwordErrors.current_password ?? serverErrors.current_password}
                autoComplete="current-password"
                disabled={passwordStatus === 'loading'}
                rightElement={
                  <EyeToggle show={showCurrent} onToggle={() => setShowCurrent(!showCurrent)} />
                }
              />

              <div>
                <AuthInput
                  label="New Password"
                  type={showNew ? 'text' : 'password'}
                  placeholder="••••••••"
                  value={newPassword}
                  onChange={(e) => setNewPassword(e.target.value)}
                  error={passwordErrors.password ?? serverErrors.password}
                  autoComplete="new-password"
                  disabled={passwordStatus === 'loading'}
                  rightElement={
                    <EyeToggle show={showNew} onToggle={() => setShowNew(!showNew)} />
                  }
                />
                <PasswordStrengthBar password={newPassword} />
              </div>

              <AuthInput
                label="Confirm New Password"
                type={showConfirm ? 'text' : 'password'}
                placeholder="••••••••"
                value={confirmPassword}
                onChange={(e) => setConfirmPassword(e.target.value)}
                error={passwordErrors.password_confirmation}
                autoComplete="new-password"
                disabled={passwordStatus === 'loading'}
                rightElement={
                  <EyeToggle show={showConfirm} onToggle={() => setShowConfirm(!showConfirm)} />
                }
              />

              <div className="flex items-center gap-4 pt-2">
                <button
                  type="submit"
                  disabled={passwordStatus === 'loading'}
                  className="flex h-10 items-center justify-center rounded-lg px-6 text-sm font-semibold transition-all duration-200 disabled:cursor-not-allowed disabled:opacity-60"
                  style={{
                    background: 'linear-gradient(135deg, var(--color-aqua) 0%, var(--color-aqua-dark) 100%)',
                    color: 'var(--color-white)',
                    boxShadow: '0 4px 16px rgba(0, 181, 226, 0.2)',
                  }}
                >
                  {passwordStatus === 'loading' ? (
                    <svg className="h-5 w-5 animate-spin" viewBox="0 0 24 24" fill="none">
                      <circle cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="3" strokeDasharray="60" strokeLinecap="round" />
                    </svg>
                  ) : (
                    'Update Password'
                  )}
                </button>

                {passwordStatus === 'success' && (
                  <span className="text-sm font-medium" style={{ color: 'var(--accent-success)' }}>
                    ✓ Updated
                  </span>
                )}
              </div>
            </form>
          </Section>
        </div>
      </AppLayout>
    </>
  );
}
