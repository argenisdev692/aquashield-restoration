import * as React from 'react';
import type { ServiceCategoryFormData } from '@/modules/service-categories/types';

interface ServiceCategoryFormProps {
    initialData?: ServiceCategoryFormData;
    onSubmit: (data: ServiceCategoryFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function ServiceCategoryForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: ServiceCategoryFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<ServiceCategoryFormData>({
        category: initialData?.category ?? '',
        type: initialData?.type ?? '',
    });
    const [errors, setErrors] = React.useState<Partial<Record<keyof ServiceCategoryFormData, string>>>({});

    function handleChange(event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>): void {
        const { name, value } = event.target;
        setFormData((prev) => ({ ...prev, [name]: value }));
        setErrors((prev) => ({ ...prev, [name]: undefined }));
    }

    function validate(): boolean {
        const nextErrors: Partial<Record<keyof ServiceCategoryFormData, string>> = {};

        if (!formData.category.trim()) {
            nextErrors.category = 'Category name is required.';
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
            category: formData.category.trim(),
            type: formData.type.trim(),
        });
    }

    return (
        <form onSubmit={handleSubmit} className="flex flex-col gap-6 p-6" noValidate>
            <div className="space-y-2">
                <label
                    htmlFor="category"
                    className="block text-sm font-semibold"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    Category Name <span style={{ color: 'var(--accent-error)' }}>*</span>
                </label>
                <input
                    id="category"
                    name="category"
                    type="text"
                    value={formData.category}
                    onChange={handleChange}
                    placeholder="e.g. Water Damage"
                    className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                    style={{
                        background: 'var(--input-bg)',
                        color: 'var(--input-text)',
                        border: errors.category
                            ? '1px solid var(--accent-error)'
                            : '1px solid var(--input-border)',
                        fontFamily: 'var(--font-sans)',
                    }}
                />
                {errors.category ? (
                    <p className="text-xs font-medium" style={{ color: 'var(--accent-error)' }}>
                        {errors.category}
                    </p>
                ) : null}
            </div>

            <div className="space-y-2">
                <label
                    htmlFor="type"
                    className="block text-sm font-semibold"
                    style={{ color: 'var(--text-secondary)' }}
                >
                    Type
                </label>
                <input
                    id="type"
                    name="type"
                    type="text"
                    value={formData.type}
                    onChange={handleChange}
                    placeholder="e.g. Residential"
                    className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                    style={{
                        background: 'var(--input-bg)',
                        color: 'var(--input-text)',
                        border: '1px solid var(--input-border)',
                        fontFamily: 'var(--font-sans)',
                    }}
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
                    {isSubmitting ? 'Saving...' : 'Save service category'}
                </button>
            </div>
        </form>
    );
}
