import * as React from 'react';
import { Head, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { formatUsPhoneInput } from '@/common/helpers/phone';
import { containsDigits, sanitizePersonNameInput } from '@/common/helpers/personName';
import {
  getUserAvailabilityErrorMessage,
  shouldCheckUserFieldAvailability,
  useUserFieldAvailability,
} from '@/modules/users/hooks/useUserFieldAvailability';
import { UserAddressFields } from '@/modules/users/components/UserAddressFields';
import type { ProfilePageProps } from '@/types/auth';
import { sileo } from 'sileo';
import { Globe, LockKeyhole, Smartphone, User } from 'lucide-react';
import ProfileAvatarUploader from './ProfileAvatarUploader';

type ProfileFormState = {
  name: string;
  last_name: string;
  email: string;
  username: string;
  phone: string;
  address: string;
  address_2: string;
  city: string;
  state: string;
  country: string;
  zip_code: string;
};

type ProfileFieldProps = {
  label: string;
  name: string;
  value: string;
  type?: React.HTMLInputTypeAttribute;
  inputMode?: React.HTMLAttributes<HTMLInputElement>['inputMode'];
  maxLength?: number;
  placeholder?: string;
  autoComplete?: string;
  error?: string;
  disabled?: boolean;
  onChange: (event: React.ChangeEvent<HTMLInputElement>) => void;
};

type PasswordFormState = {
  current_password: string;
  password: string;
  password_confirmation: string;
};

type AvatarFormState = {
  _method: 'put';
  name: string;
  last_name: string;
  email: string;
  username: string;
  phone: string;
  address: string;
  address_2: string;
  city: string;
  state: string;
  country: string;
  zip_code: string;
  avatar: File | null;
  remove_avatar: boolean;
};

function getFirstError(errors: Record<string, string | undefined>): string | null {
  return Object.values(errors).find((value): value is string => typeof value === 'string' && value.length > 0) ?? null;
}

function getProfileNameError(field: 'name' | 'last_name'): string {
  return field === 'name'
    ? 'The first name field must not contain numbers.'
    : 'The last name field must not contain numbers.';
}

function ProfileField({
  label,
  name,
  value,
  type = 'text',
  inputMode,
  maxLength,
  placeholder,
  autoComplete,
  error,
  disabled = false,
  onChange,
}: ProfileFieldProps): React.JSX.Element {
  return (
    <label className="flex flex-col gap-2">
      <span className="text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-secondary)' }}>
        {label}
      </span>
      <input
        name={name}
        type={type}
        value={value}
        onChange={onChange}
        inputMode={inputMode}
        maxLength={maxLength}
        placeholder={placeholder}
        autoComplete={autoComplete}
        disabled={disabled}
        className="h-11 rounded-xl px-4 text-sm transition-all duration-200"
        style={{
          background: 'var(--bg-card)',
          border: error ? '1px solid var(--accent-error)' : '1px solid var(--border-default)',
          color: 'var(--text-primary)',
          fontFamily: 'var(--font-sans)',
        }}
      />
      {error ? (
        <span className="text-xs font-medium" style={{ color: 'var(--accent-error)' }}>
          {error}
        </span>
      ) : null}
    </label>
  );
}

export default function ProfilePage(): React.JSX.Element {
  const { auth } = usePage<ProfilePageProps>().props;
  const user = auth.user;
  const fullName = [user.name, user.last_name].filter(Boolean).join(' ');
  const initials = `${user.name[0] ?? ''}${user.last_name?.[0] ?? ''}`.toUpperCase() || 'U';
  const roleLabel = user.roles[0] ?? 'Member';

  const profileForm = useForm<ProfileFormState>({
    name: user.name,
    last_name: user.last_name ?? '',
    email: user.email,
    username: user.username || '',
    phone: formatUsPhoneInput(user.phone || ''),
    address: user.address ?? '',
    address_2: user.address_2 ?? '',
    city: user.city ?? '',
    state: user.state ?? '',
    country: user.country ?? '',
    zip_code: user.zip_code ?? '',
  });

  const passwordForm = useForm<PasswordFormState>({
    current_password: '',
    password: '',
    password_confirmation: '',
  });
  const avatarForm = useForm<AvatarFormState>({
    _method: 'put',
    name: user.name,
    last_name: user.last_name ?? '',
    email: user.email,
    username: user.username ?? '',
    phone: formatUsPhoneInput(user.phone ?? ''),
    address: user.address ?? '',
    address_2: user.address_2 ?? '',
    city: user.city ?? '',
    state: user.state ?? '',
    country: user.country ?? '',
    zip_code: user.zip_code ?? '',
    avatar: null,
    remove_avatar: false,
  });
  const [availabilityErrors, setAvailabilityErrors] = React.useState<Record<string, string>>({});
  const emailAvailability = useUserFieldAvailability({ field: 'email', value: profileForm.data.email, scope: 'profile', ignoreUuid: user.uuid });
  const usernameAvailability = useUserFieldAvailability({ field: 'username', value: profileForm.data.username, scope: 'profile', ignoreUuid: user.uuid });
  const phoneAvailability = useUserFieldAvailability({ field: 'phone', value: profileForm.data.phone, scope: 'profile', ignoreUuid: user.uuid });

  React.useEffect(() => {
    if (!shouldCheckUserFieldAvailability('email', profileForm.data.email) || emailAvailability.isFetching) {
      setAvailabilityErrors((prev) => ({ ...prev, email: '' }));
      return;
    }

    setAvailabilityErrors((prev) => ({
      ...prev,
      email: emailAvailability.data?.available === false ? getUserAvailabilityErrorMessage('email') : '',
    }));
  }, [emailAvailability.data?.available, emailAvailability.isFetching, profileForm.data.email]);

  React.useEffect(() => {
    if (!shouldCheckUserFieldAvailability('username', profileForm.data.username) || usernameAvailability.isFetching) {
      setAvailabilityErrors((prev) => ({ ...prev, username: '' }));
      return;
    }

    setAvailabilityErrors((prev) => ({
      ...prev,
      username: usernameAvailability.data?.available === false ? getUserAvailabilityErrorMessage('username') : '',
    }));
  }, [profileForm.data.username, usernameAvailability.data?.available, usernameAvailability.isFetching]);

  React.useEffect(() => {
    if (!shouldCheckUserFieldAvailability('phone', profileForm.data.phone) || phoneAvailability.isFetching) {
      setAvailabilityErrors((prev) => ({ ...prev, phone: '' }));
      return;
    }

    setAvailabilityErrors((prev) => ({
      ...prev,
      phone: phoneAvailability.data?.available === false ? getUserAvailabilityErrorMessage('phone') : '',
    }));
  }, [phoneAvailability.data?.available, phoneAvailability.isFetching, profileForm.data.phone]);

  function getProfileFieldError(field: keyof ProfileFormState): string | undefined {
    const directError = profileForm.errors[field];

    if (typeof directError === 'string' && directError.length > 0) {
      return directError;
    }

    const availabilityError = availabilityErrors[field];

    return typeof availabilityError === 'string' && availabilityError.length > 0 ? availabilityError : undefined;
  }

  const handleProfileNameChange = (field: 'name' | 'last_name', value: string): void => {
    const sanitizedValue = sanitizePersonNameInput(value);

    profileForm.setData(field, sanitizedValue);

    if (containsDigits(value)) {
      profileForm.setError(field, getProfileNameError(field));
      return;
    }

    profileForm.clearErrors(field);
  };

  const handleProfileInputChange = React.useCallback((event: React.ChangeEvent<HTMLInputElement>): void => {
    const { name, value } = event.target;
    const field = name as keyof ProfileFormState;

    profileForm.setData(field, value);
    profileForm.clearErrors(field);
  }, [profileForm]);

  const handleProfileAddressAutofill = React.useCallback((value: Pick<Partial<ProfileFormState>, 'address' | 'city' | 'state' | 'country' | 'zip_code'>): void => {
    profileForm.setData((previous) => ({
      ...previous,
      address: value.address ?? '',
      city: value.city ?? '',
      state: value.state ?? '',
      country: value.country ?? '',
      zip_code: value.zip_code ?? '',
    }));
    profileForm.clearErrors('address', 'city', 'state', 'country', 'zip_code');
  }, [profileForm]);

  const handleProfileSubmit = (event: React.FormEvent<HTMLFormElement>): void => {
    event.preventDefault();

    if (availabilityErrors.email || availabilityErrors.username || availabilityErrors.phone) {
      sileo.error({ title: getFirstError(availabilityErrors) ?? 'Please fix the highlighted fields.' });
      return;
    }

    profileForm.clearErrors();

    profileForm.put('/user/profile-information', {
      preserveScroll: true,
      onSuccess: () => {
        sileo.success({ title: 'Profile updated successfully' });
      },
      onError: (errors) => {
        sileo.error({ title: getFirstError(errors) ?? 'Failed to update profile' });
      },
    });
  };

  const handlePasswordSubmit = (event: React.FormEvent<HTMLFormElement>): void => {
    event.preventDefault();

    passwordForm.put('/user/password', {
      preserveScroll: true,
      onSuccess: () => {
        passwordForm.reset();
        passwordForm.clearErrors();
        sileo.success({ title: 'Password updated successfully' });
      },
      onError: (errors) => {
        sileo.error({ title: getFirstError(errors) ?? 'Failed to update password' });
      },
    });
  };

  const handleAvatarUpload = React.useCallback((file: File): Promise<void> => (
    new Promise((resolve, reject) => {
      avatarForm.setData((previous) => ({
        ...previous,
        ...profileForm.data,
        avatar: file,
        remove_avatar: false,
      }));

      avatarForm.post('/user/profile-information', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
          avatarForm.reset('avatar');
          avatarForm.setData('_method', 'put');
          avatarForm.setData('remove_avatar', false);
          sileo.success({ title: 'Avatar updated successfully' });
          resolve();
        },
        onError: (errors) => {
          const message = getFirstError(errors) ?? 'Failed to update avatar';
          sileo.error({ title: message });
          reject(new Error(message));
        },
        onFinish: () => {
          avatarForm.setData('avatar', null);
        },
      });
    })
  ), [avatarForm, profileForm.data]);

  const handleAvatarRemove = React.useCallback((): Promise<void> => (
    new Promise((resolve, reject) => {
      avatarForm.setData((previous) => ({
        ...previous,
        ...profileForm.data,
        avatar: null,
        remove_avatar: true,
      }));

      avatarForm.post('/user/profile-information', {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
          avatarForm.reset('avatar');
          avatarForm.setData('_method', 'put');
          avatarForm.setData('remove_avatar', false);
          sileo.success({ title: 'Avatar removed successfully' });
          resolve();
        },
        onError: (errors) => {
          const message = getFirstError(errors) ?? 'Failed to remove avatar';
          sileo.error({ title: message });
          reject(new Error(message));
        },
      });
    })
  ), [avatarForm, profileForm.data]);

  return (
    <AppLayout>
      <Head title="My Profile" />
      <div className="mx-auto max-w-5xl space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-300">
        <div className="flex flex-col gap-5 rounded-3xl p-5 sm:flex-row sm:items-center" style={{ background: 'var(--bg-surface)', border: '1px solid var(--border-default)' }}>
          <div className="relative">
            {user.profile_photo_path ? (
              <img
                src={user.profile_photo_path}
                alt={fullName}
                className="h-24 w-24 rounded-2xl object-cover shadow-2xl transition-transform duration-300"
                style={{ border: '2px solid var(--accent-primary)' }}
              />
            ) : (
              <div
                className="flex h-24 w-24 items-center justify-center rounded-2xl shadow-2xl transition-transform duration-300"
                style={{ background: 'var(--grad-primary)', color: 'var(--color-white)' }}
              >
                <span className="text-3xl font-black">{initials}</span>
              </div>
            )}
            <div
              className="absolute -bottom-1 -right-1 h-4 w-4 rounded-full"
              style={{ background: 'var(--accent-success)', border: '2px solid var(--bg-app)' }}
            />
          </div>
          <div className="min-w-0 flex-1">
            <h1 className="text-3xl font-black tracking-tight" style={{ color: 'var(--text-primary)' }}>{fullName}</h1>
            <p className="font-medium" style={{ color: 'var(--text-muted)' }}>@{user.username || 'user'} • {roleLabel}</p>
            <div className="mt-3 flex flex-wrap gap-2">
              <span
                className="rounded-md px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider"
                style={{
                  background: 'color-mix(in srgb, var(--accent-primary) 15%, transparent)',
                  color: 'var(--accent-primary)',
                  border: '1px solid color-mix(in srgb, var(--accent-primary) 24%, transparent)',
                }}
              >
                Standard Account
              </span>
            </div>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div className="md:col-span-2 space-y-8">
            <form onSubmit={handleProfileSubmit} className="card p-8 space-y-8 shadow-xl relative overflow-hidden">
              <div className="absolute top-0 right-0 p-4 opacity-5">
                <User size={120} style={{ color: 'var(--text-primary)' }} />
              </div>
              <div className="flex items-center gap-3 relative z-10">
                <div
                  className="rounded-lg p-2"
                  style={{
                    background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                    color: 'var(--accent-primary)',
                  }}
                >
                  <User size={20} />
                </div>
                <h2 className="text-lg font-bold" style={{ color: 'var(--text-primary)' }}>Account Settings</h2>
              </div>

              <div className="grid grid-cols-1 sm:grid-cols-2 gap-6 relative z-10">
                <ProfileField
                  label="First Name"
                  name="name"
                  value={profileForm.data.name}
                  onChange={(event) => handleProfileNameChange('name', event.target.value)}
                  placeholder="John"
                  autoComplete="given-name"
                  error={getProfileFieldError('name')}
                  disabled={profileForm.processing}
                />
                <ProfileField
                  label="Last Name"
                  name="last_name"
                  value={profileForm.data.last_name}
                  onChange={(event) => handleProfileNameChange('last_name', event.target.value)}
                  placeholder="Doe"
                  autoComplete="family-name"
                  error={getProfileFieldError('last_name')}
                  disabled={profileForm.processing}
                />
                <div className="sm:col-span-2">
                  <ProfileField
                    label="Primary Email"
                    name="email"
                    type="email"
                    value={profileForm.data.email}
                    onChange={(event) => {
                      profileForm.setData('email', event.target.value);
                      profileForm.clearErrors('email');
                    }}
                    placeholder="john@example.com"
                    autoComplete="email"
                    error={getProfileFieldError('email')}
                    disabled={profileForm.processing}
                  />
                </div>
                <ProfileField
                  label="Public Username"
                  name="username"
                  value={profileForm.data.username}
                  onChange={(event) => {
                    profileForm.setData('username', event.target.value);
                    profileForm.clearErrors('username');
                  }}
                  placeholder="jdoe88"
                  autoComplete="username"
                  error={getProfileFieldError('username')}
                  disabled={profileForm.processing}
                />
                <ProfileField
                  label="Contact Phone"
                  name="phone"
                  type="tel"
                  inputMode="numeric"
                  maxLength={14}
                  value={profileForm.data.phone}
                  onChange={(event) => {
                    profileForm.setData('phone', formatUsPhoneInput(event.target.value));
                    profileForm.clearErrors('phone');
                  }}
                  placeholder="(555) 000-0000"
                  autoComplete="tel"
                  error={getProfileFieldError('phone')}
                  disabled={profileForm.processing}
                />
                <div className="sm:col-span-2 pt-2">
                  <h3 className="text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-secondary)' }}>
                    Address
                  </h3>
                </div>
                <div className="sm:col-span-2">
                  <UserAddressFields
                    form={profileForm.data}
                    errors={profileForm.errors}
                    onChange={handleProfileInputChange}
                    onAddressAutofill={handleProfileAddressAutofill}
                    variant="premium"
                  />
                </div>
              </div>

              <div className="pt-4 flex justify-end relative z-10">
                <button className="btn-modern btn-modern-primary px-8 py-2.5 font-bold shadow-lg disabled:opacity-60" type="submit" disabled={profileForm.processing}>
                  {profileForm.processing ? 'Saving...' : 'Save Profile'}
                </button>
              </div>
            </form>
          </div>

          <div className="space-y-6">
            <ProfileAvatarUploader
              name={user.name}
              lastName={user.last_name}
              photoUrl={user.profile_photo_path}
              isUploading={avatarForm.processing}
              uploadProgress={avatarForm.progress?.percentage ?? null}
              error={avatarForm.errors.avatar}
              onUpload={handleAvatarUpload}
              onRemove={handleAvatarRemove}
            />

            <form onSubmit={handlePasswordSubmit} className="card p-6 space-y-4 shadow-sm" style={{ background: 'var(--bg-surface)', borderColor: 'var(--border-subtle)' }}>
              <div className="mb-2 flex items-center gap-2" style={{ color: 'var(--text-muted)' }}>
                <LockKeyhole size={16} />
                <h3 className="text-xs font-bold uppercase tracking-widest">Security</h3>
              </div>
              <ProfileField
                label="Current Password"
                name="current_password"
                type="password"
                value={passwordForm.data.current_password}
                onChange={(event) => passwordForm.setData('current_password', event.target.value)}
                placeholder="••••••••"
                autoComplete="current-password"
                error={passwordForm.errors.current_password}
                disabled={passwordForm.processing}
              />
              <ProfileField
                label="New Password"
                name="password"
                type="password"
                value={passwordForm.data.password}
                onChange={(event) => passwordForm.setData('password', event.target.value)}
                placeholder="••••••••"
                autoComplete="new-password"
                error={passwordForm.errors.password}
                disabled={passwordForm.processing}
              />
              <ProfileField
                label="Confirm Password"
                name="password_confirmation"
                type="password"
                value={passwordForm.data.password_confirmation}
                onChange={(event) => passwordForm.setData('password_confirmation', event.target.value)}
                placeholder="••••••••"
                autoComplete="new-password"
                error={passwordForm.errors.password_confirmation}
                disabled={passwordForm.processing}
              />
              <button className="btn-modern btn-modern-primary w-full py-2.5 font-bold shadow-lg disabled:opacity-60" type="submit" disabled={passwordForm.processing}>
                {passwordForm.processing ? 'Updating...' : 'Update Password'}
              </button>
            </form>

            <section className="card p-6 space-y-4 shadow-sm" style={{ background: 'var(--bg-surface)', borderColor: 'var(--border-subtle)' }}>
              <div className="mb-2 flex items-center gap-2" style={{ color: 'var(--text-muted)' }}>
                <Smartphone size={16} />
                <h3 className="text-xs font-bold uppercase tracking-widest">Connect</h3>
              </div>
              <p className="text-xs leading-relaxed" style={{ color: 'var(--text-secondary)' }}>
                Link your phone number to enable Two-Factor Authentication and receive instant notifications.
              </p>
              <button className="btn-ghost w-full py-2.5 text-xs font-bold shadow-sm" type="button">
                Verify Phone
              </button>
            </section>

            <section className="card p-6 space-y-4 shadow-sm" style={{ background: 'var(--bg-surface)', borderColor: 'var(--border-subtle)' }}>
              <div className="mb-2 flex items-center gap-2" style={{ color: 'var(--text-muted)' }}>
                <Globe size={16} />
                <h3 className="text-xs font-bold uppercase tracking-widest">Region</h3>
              </div>
              <div className="flex items-center justify-between">
                <span className="text-xs font-medium" style={{ color: 'var(--text-secondary)' }}>Language</span>
                <span className="text-[11px] font-bold" style={{ color: 'var(--accent-primary)' }}>English (US)</span>
              </div>
            </section>
          </div>

        </div>
      </div>
    </AppLayout>
  );
}
