import * as React from 'react';
import type { ProjectTypeFormData, ServiceCategoryOption } from '@/modules/project-types/types';

interface ProjectTypeFormProps {
    initialData?: ProjectTypeFormData;
    serviceCategories: ServiceCategoryOption[];
    onSubmit: (data: ProjectTypeFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function ProjectTypeForm({
    initialData,
    serviceCategories,
    onSubmit,
    isSubmitting,
    onCancel,
}: ProjectTypeFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<ProjectTypeFormData>({
        title: initialData?.title ?? '',
        description: initialData?.description ?? '',
        status: initialData?.status ?? 'active',
        service_category_uuid: initialData?.service_category_uuid ?? '',
    });
    const [errors, setErrors] = React.useState<Partial<Record<keyof ProjectTypeFormData, string>>>({});

    function handleChange(
        event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>,
    ): void {
        const { name, value } = event.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
        setErrors((prev) => ({ ...prev, [name]: undefined }));
    }

    function validate(): boolean {
        const nextErrors: Partial<Record<keyof ProjectTypeFormData, string>> = {};

        if (!formData.title.trim()) {
            nextErrors.title = 'Title is required.';
        }

        if (!formData.service_category_uuid) {
            nextErrors.service_category_uuid = 'Service category is required.';
        }

        setErrors(nextErrors);

        return Object.keys(nextErrors).length === 0;
    }

    async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
        event.preventDefault();

        if (!validate()) {
            return;
        }

        await onSubmit({
            title: formData.title.trim(),
            description: formData.description.trim(),
            status: formData.status,
            service_category_uuid: formData.service_category_uuid,
        });
    }

    const inputStyle = (hasError: boolean): React.CSSProperties => ({
        background: 'var(--input-bg)',
        color: 'var(--input-text)',
        border: hasError ? '1px solid var(--accent-error)' : '1px solid var(--input-border)',
        fontFamily: 'var(--font-sans)',
    });

    return (
        <form onSubmit={handleSubmit} className="flex flex-col gap-6 p-6" noValidate>
            <div className="space-y-2">
                <label
                    htmlFor="title"
                    className="block text-sm font-semibold"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    Title <span style={{ color: 'var(--accent-error)' }}>*</span>
                </label>
                <input
                    id="title"
                    name="title"
                    type="text"
                    value={formData.title}
                    onChange={handleChange}
                    placeholder="e.g. Roof Repair"
                    className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                    style={inputStyle(Boolean(errors.title))}
                />
                {errors.title ? (
                    <p className="text-xs font-medium" style={{ color: 'var(--accent-error)' }}>
                        {errors.title}
                    </p>
                ) : null}
            </div>

            <div className="space-y-2">
                <label
                    htmlFor="service_category_uuid"
                    className="block text-sm font-semibold"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    Service Category <span style={{ color: 'var(--accent-error)' }}>*</span>
                </label>
                <select
                    id="service_category_uuid"
                    name="service_category_uuid"
                    value={formData.service_category_uuid}
                    onChange={handleChange}
                    className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                    style={inputStyle(Boolean(errors.service_category_uuid))}
                >
                    <option value="">Select a service category…</option>
                    {serviceCategories.map((sc) => (
                        <option key={sc.uuid} value={sc.uuid}>
                            {sc.category}{sc.type ? ` — ${sc.type}` : ''}
                        </option>
                    ))}
                </select>
                {errors.service_category_uuid ? (
                    <p className="text-xs font-medium" style={{ color: 'var(--accent-error)' }}>
                        {errors.service_category_uuid}
                    </p>
                ) : null}
            </div>

            <div className="space-y-2">
                <label
                    htmlFor="status"
                    className="block text-sm font-semibold"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    Status
                </label>
                <select
                    id="status"
                    name="status"
                    value={formData.status}
                    onChange={handleChange}
                    className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                    style={inputStyle(false)}
                >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div className="space-y-2">
                <label
                    htmlFor="description"
                    className="block text-sm font-semibold"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    Description
                </label>
                <textarea
                    id="description"
                    name="description"
                    value={formData.description}
                    onChange={handleChange}
                    rows={4}
                    placeholder="Optional description…"
                    className="w-full resize-y rounded-xl px-4 py-3 text-sm outline-none"
                    style={inputStyle(false)}
                />
            </div>

            <div className="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    onClick={onCancel}
                    disabled={isSubmitting}
                    className="btn-ghost px-5 py-3 text-sm font-semibold"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    disabled={isSubmitting}
                    className="btn-primary px-5 py-3 text-sm font-semibold"
                >
                    {isSubmitting ? 'Saving...' : 'Save project type'}
                </button>
            </div>
        </form>
    );
}
