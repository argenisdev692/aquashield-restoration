import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import axios from 'axios';
import AppLayout from '@/pages/layouts/AppLayout';
import { useRoleMutations } from '@/modules/roles/hooks/useRoleMutations';
import type { RoleDetail, UpdateRolePayload } from '@/modules/roles/types';
import { ArrowLeft, Save } from 'lucide-react';

interface RoleEditPageProps {
  role: RoleDetail;
}

function normalizeRoleName(value: string): string {
  return value.replace(/\s{2,}/g, ' ').trimStart();
}

function getValidationMessage(error: unknown, field: string): string {
  if (!axios.isAxiosError(error)) {
    return '';
  }

  const errors = error.response?.data as { errors?: Record<string, string[]> } | undefined;
  return errors?.errors?.[field]?.[0] ?? '';
}

export default function RoleEditPage({ role }: RoleEditPageProps): React.JSX.Element {
  const [form, setForm] = React.useState<UpdateRolePayload>({ name: role.name });
  const [errors, setErrors] = React.useState<Record<string, string>>({});
  const { updateRole } = useRoleMutations();

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();
    setErrors({});

    try {
      await updateRole.mutateAsync({
        uuid: role.uuid,
        payload: { name: form.name.trim() },
      });
      router.visit('/roles');
    } catch (error) {
      setErrors({
        name: getValidationMessage(error, 'name'),
      });
    }
  }

  return (
    <>
      <Head title={`Edit ${role.name}`} />
      <AppLayout>
        <div className="mx-auto flex w-full max-w-3xl flex-col gap-6 animate-in fade-in duration-300">
          <div className="flex items-center justify-between gap-4">
            <div>
              <h1 className="text-3xl font-extrabold tracking-tight text-(--text-primary)">Edit Role</h1>
              <p className="mt-1 text-sm text-(--text-muted)">Update the role name. Permission mapping stays in the Permissions module.</p>
            </div>
            <Link
              href="/roles"
              className="inline-flex items-center gap-2 rounded-xl border border-(--border-default) px-4 py-2 text-sm font-semibold text-(--text-secondary) transition-all hover:bg-(--bg-hover)"
            >
              <ArrowLeft size={16} />
              Back
            </Link>
          </div>

          <form onSubmit={handleSubmit} className="card-modern flex flex-col gap-6 rounded-3xl border border-(--border-default) p-6 shadow-xl">
            <div className="grid gap-4 sm:grid-cols-2">
              <div className="flex flex-col gap-2 sm:col-span-2">
                <label htmlFor="role-name" className="text-sm font-semibold text-(--text-primary)">
                  Role name
                </label>
                <input
                  id="role-name"
                  type="text"
                  value={form.name}
                  onChange={(event) => {
                    const value = normalizeRoleName(event.target.value);
                    setForm({ name: value });
                    setErrors((previous) => ({ ...previous, name: '' }));
                  }}
                  className="rounded-2xl border border-(--border-default) bg-(--bg-card) px-4 py-3 text-sm text-(--text-primary) outline-none transition-all focus:border-(--accent-primary)"
                />
                {errors.name ? <p className="text-xs font-medium text-(--accent-error)">{errors.name}</p> : null}
              </div>

              <div className="flex flex-col gap-2">
                <span className="text-sm font-semibold text-(--text-primary)">Guard</span>
                <div className="rounded-2xl border border-(--border-default) bg-(--bg-subtle) px-4 py-3 text-sm text-(--text-muted)">
                  {role.guard_name}
                </div>
              </div>

              <div className="flex flex-col gap-2">
                <span className="text-sm font-semibold text-(--text-primary)">Permissions linked</span>
                <div className="rounded-2xl border border-(--border-default) bg-(--bg-subtle) px-4 py-3 text-sm text-(--text-muted)">
                  {role.permissions_count}
                </div>
              </div>
            </div>

            <div className="flex items-center justify-end gap-3">
              <Link
                href="/roles"
                className="rounded-xl border border-(--border-default) px-4 py-2 text-sm font-semibold text-(--text-secondary) transition-all hover:bg-(--bg-hover)"
              >
                Cancel
              </Link>
              <button
                type="submit"
                disabled={updateRole.isPending}
                className="btn-modern btn-modern-primary inline-flex items-center gap-2 px-4 py-2 disabled:cursor-not-allowed disabled:opacity-60"
              >
                <Save size={16} />
                <span>{updateRole.isPending ? 'Saving...' : 'Save Changes'}</span>
              </button>
            </div>
          </form>
        </div>
      </AppLayout>
    </>
  );
}
