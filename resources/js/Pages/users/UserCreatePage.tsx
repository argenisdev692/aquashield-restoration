import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { formatUsPhoneInput } from '@/common/helpers/phone';
import { containsDigits, sanitizePersonNameInput } from '@/common/helpers/personName';
import {
  getUserAvailabilityErrorMessage,
  shouldCheckUserFieldAvailability,
  useUserFieldAvailability,
} from '@/modules/users/hooks/useUserFieldAvailability';
import { UserAddressFields } from '@/modules/users/components/UserAddressFields';
import AppLayout from '@/pages/layouts/AppLayout';
import { useUserMutations } from '@/modules/users/hooks/useUserMutations';
import { PremiumField } from '@/shadcn/PremiumField';
import type { CreateUserPayload } from '@/types/users';
import { ArrowLeft, Save } from 'lucide-react';

interface UserCreatePageProps {
  roles: string[];
}

function getNameFieldError(field: 'name' | 'last_name'): string {
  return field === 'name'
    ? 'The first name field must not contain numbers.'
    : 'The last name field must not contain numbers.';
}

function formatRoleLabel(role: string): string {
  return role
    .toLowerCase()
    .split('_')
    .map((segment) => segment.charAt(0).toUpperCase() + segment.slice(1))
    .join(' ');
}

export default function UserCreatePage({ roles }: UserCreatePageProps): React.JSX.Element {
  const availableRoles = roles.length > 0 ? roles : ['USER'];
  const [form, setForm] = React.useState<CreateUserPayload>({
    name: '',
    last_name: '',
    email: '',
    username: '',
    phone: '',
    address: '',
    address_2: '',
    city: '',
    state: '',
    country: '',
    zip_code: '',
    role: availableRoles[0] ?? 'USER',
  });
  
  const [errors, setErrors] = React.useState<Record<string, string>>({});
  const [availabilityErrors, setAvailabilityErrors] = React.useState<Record<string, string>>({});
  const { createUser } = useUserMutations();
  const emailAvailability = useUserFieldAvailability({ field: 'email', value: form.email, scope: 'admin' });
  const usernameAvailability = useUserFieldAvailability({ field: 'username', value: form.username ?? '', scope: 'admin' });
  const phoneAvailability = useUserFieldAvailability({ field: 'phone', value: form.phone ?? '', scope: 'admin' });

  React.useEffect(() => {
    if (!shouldCheckUserFieldAvailability('email', form.email) || emailAvailability.isFetching) {
      setAvailabilityErrors((prev) => ({ ...prev, email: '' }));
      return;
    }

    setAvailabilityErrors((prev) => ({
      ...prev,
      email: emailAvailability.data?.available === false ? getUserAvailabilityErrorMessage('email') : '',
    }));
  }, [emailAvailability.data?.available, emailAvailability.isFetching, form.email]);

  React.useEffect(() => {
    const usernameValue = form.username ?? '';

    if (!shouldCheckUserFieldAvailability('username', usernameValue) || usernameAvailability.isFetching) {
      setAvailabilityErrors((prev) => ({ ...prev, username: '' }));
      return;
    }

    setAvailabilityErrors((prev) => ({
      ...prev,
      username: usernameAvailability.data?.available === false ? getUserAvailabilityErrorMessage('username') : '',
    }));
  }, [form.username, usernameAvailability.data?.available, usernameAvailability.isFetching]);

  React.useEffect(() => {
    const phoneValue = form.phone ?? '';

    if (!shouldCheckUserFieldAvailability('phone', phoneValue) || phoneAvailability.isFetching) {
      setAvailabilityErrors((prev) => ({ ...prev, phone: '' }));
      return;
    }

    setAvailabilityErrors((prev) => ({
      ...prev,
      phone: phoneAvailability.data?.available === false ? getUserAvailabilityErrorMessage('phone') : '',
    }));
  }, [form.phone, phoneAvailability.data?.available, phoneAvailability.isFetching]);

  function getFieldError(field: keyof CreateUserPayload): string | undefined {
    const directError = errors[field];

    if (typeof directError === 'string' && directError.length > 0) {
      return directError;
    }

    const availabilityError = availabilityErrors[field];

    return typeof availabilityError === 'string' && availabilityError.length > 0 ? availabilityError : undefined;
  }

  function handleChange(e: React.ChangeEvent<HTMLInputElement>): void {
    const { name, value } = e.target;
    const isPersonNameField = name === 'name' || name === 'last_name';
    const nextValue = name === 'phone'
      ? formatUsPhoneInput(value)
      : isPersonNameField
        ? sanitizePersonNameInput(value)
        : value;

    setForm((prev) => ({ ...prev, [name]: nextValue }));
    setErrors((prev) => ({
      ...prev,
      [name]: isPersonNameField && containsDigits(value)
        ? getNameFieldError(name)
        : '',
    }));
  }

  const handleAddressAutofill = React.useCallback((value: Pick<CreateUserPayload, 'address' | 'city' | 'state' | 'country' | 'zip_code'>): void => {
    setForm((prev) => ({
      ...prev,
      address: value.address ?? '',
      city: value.city ?? '',
      state: value.state ?? '',
      country: value.country ?? '',
      zip_code: value.zip_code ?? '',
    }));
    setErrors((prev) => ({
      ...prev,
      address: '',
      city: '',
      state: '',
      country: '',
      zip_code: '',
    }));
  }, []);

  function validate(): boolean {
    const nextErrors: Record<string, string> = {};

    if (!form.name.trim()) {
      nextErrors.name = 'First name is required.';
    }

    if (!form.last_name.trim()) {
      nextErrors.last_name = 'Last name is required.';
    }

    if (!form.email.trim()) {
      nextErrors.email = 'Email is required.';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
      nextErrors.email = 'Invalid email format.';
    }

    if (!form.role.trim()) {
      nextErrors.role = 'Role is required.';
    }

    if (availabilityErrors.email) {
      nextErrors.email = availabilityErrors.email;
    }

    if (availabilityErrors.username) {
      nextErrors.username = availabilityErrors.username;
    }

    if (availabilityErrors.phone) {
      nextErrors.phone = availabilityErrors.phone;
    }

    setErrors(nextErrors);

    return Object.keys(nextErrors).length === 0;
  }

  async function handleSubmit(e: React.FormEvent): Promise<void> {
    e.preventDefault();

    if (!validate()) {
      return;
    }
    
    createUser.mutate(form, {
      onSuccess: () => {
        window.setTimeout(() => {
          router.visit('/users');
        }, 180);
      },
      onError: (err: unknown) => {
        const response = (err as { response?: { data?: { errors?: Record<string, string[]> } } }).response;
        if (response?.data?.errors) {
          const serverErrors: Record<string, string> = {};
          for (const [key, msgs] of Object.entries(response.data.errors)) {
            serverErrors[key] = msgs[0] ?? '';
          }
          setErrors(serverErrors);
        }
      }
    });
  }

  return (
    <AppLayout>
      <Head title="Create Platform User" />
      <form onSubmit={(e) => void handleSubmit(e)} className="max-w-4xl mx-auto flex flex-col gap-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
        
        {/* ── Header ── */}
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-4">
            <Link
              href="/users"
              aria-label="Back to users"
              className="flex h-10 w-10 items-center justify-center rounded-xl bg-(--bg-card) border border-(--border-default) text-(--text-muted) hover:bg-(--bg-hover) hover:text-(--accent-primary) transition-all shadow-sm"
            >
              <ArrowLeft size={20} />
            </Link>
            <div>
              <h1 className="text-2xl font-bold tracking-tight text-(--text-primary)">New User Account</h1>
              <p className="text-sm text-(--text-muted)">Register a new member in the platform</p>
            </div>
          </div>

          <button
            type="submit"
            disabled={createUser.isPending}
            className="btn-modern btn-modern-primary flex items-center gap-2 px-6 py-2.5 shadow-lg hover:shadow-xl transition-all disabled:opacity-50"
          >
            {createUser.isPending ? (
              <span className="animate-pulse">Creating...</span>
            ) : (
              <>
                <Save size={18} />
                <span className="font-bold">Save User</span>
              </>
            )}
          </button>
        </div>

        {/* ── Form Body ── */}
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div className="lg:col-span-2 space-y-6">
                <div className="card-modern p-8 space-y-8 shadow-xl border border-(--border-default)">
                    <div className="flex items-center gap-3">
                        <div className="h-8 w-1 bg-(--accent-primary) rounded-full" />
                        <h2 className="text-lg font-bold text-(--text-primary)">Identity & Access</h2>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <PremiumField 
                            label="First Name" 
                            name="name" 
                            value={form.name} 
                            onChange={handleChange} 
                            required 
                            error={getFieldError('name')} 
                            placeholder="John"
                        />
                        <PremiumField 
                            label="Last Name" 
                            name="last_name" 
                            value={form.last_name} 
                            onChange={handleChange} 
                            required 
                            error={getFieldError('last_name')} 
                            placeholder="Doe"
                        />
                        <div className="md:col-span-2">
                            <PremiumField 
                                label="Email Address" 
                                name="email" 
                                type="email"
                                value={form.email} 
                                onChange={handleChange} 
                                required 
                                error={getFieldError('email')} 
                                placeholder="john.doe@example.com"
                            />
                        </div>
                        <PremiumField 
                            label="System Username" 
                            name="username" 
                            value={form.username || ''} 
                            onChange={handleChange} 
                            error={getFieldError('username')} 
                            placeholder="johndoe"
                        />
                        <PremiumField 
                            label="Phone Number" 
                            name="phone" 
                            value={form.phone || ''} 
                            onChange={handleChange} 
                            error={getFieldError('phone')} 
                            placeholder="(555) 000-0000"
                        />
                    </div>

                    <div className="flex items-center gap-3 pt-2">
                        <div className="h-8 w-1 bg-(--accent-primary) rounded-full" />
                        <h2 className="text-lg font-bold text-(--text-primary)">Address</h2>
                    </div>

                    <UserAddressFields
                      form={form}
                      errors={errors}
                      onChange={handleChange}
                      onAddressAutofill={handleAddressAutofill}
                      variant="premium"
                    />
                </div>
            </div>

            <div className="space-y-6">
                <div className="card-modern p-6 bg-(--bg-surface) border border-(--border-subtle)">
                    <h3 className="text-sm font-bold uppercase tracking-widest text-(--text-muted) mb-4">Account Config</h3>
                    
                    <div className="space-y-4">
                        <div className="p-4 rounded-xl bg-(--bg-card) border border-(--border-default) shadow-inner">
                            <p className="text-xs text-(--text-muted) leading-relaxed">
                                Users created via admin panel will receive an email to set up their password and finalize their profile.
                            </p>
                        </div>

                        <div className="flex flex-col gap-2">
                             <label className="text-[11px] font-bold uppercase tracking-widest text-(--text-secondary)">Assign Role</label>
                             <select 
                                name="role"
                                value={form.role}
                                onChange={(e) => {
                                  setForm((prev) => ({ ...prev, role: e.target.value }));
                                  setErrors((prev) => ({ ...prev, role: '' }));
                                }}
                                className="w-full rounded-xl px-4 py-3 bg-(--bg-card) border border-(--border-default) text-sm outline-none focus:ring-2 focus:ring-(--accent-primary) transition-all"
                             >
                                 {availableRoles.map((role) => (
                                   <option key={role} value={role}>
                                     {formatRoleLabel(role)}
                                   </option>
                                 ))}
                             </select>
                             {errors.role ? (
                               <span className="text-[11px] font-medium text-(--accent-error)">
                                 {errors.role}
                               </span>
                             ) : null}
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </form>
    </AppLayout>
  );
}
