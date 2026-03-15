import * as React from "react";
import type { CategoryProductFormData } from "@/modules/category-products/types";

interface CategoryProductFormProps {
    initialData?: Partial<CategoryProductFormData>;
    onSubmit: (data: CategoryProductFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function CategoryProductForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: CategoryProductFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<CategoryProductFormData>({
        category_product_name: initialData?.category_product_name ?? "",
    });
    const [errors, setErrors] = React.useState<
        Partial<Record<keyof CategoryProductFormData, string>>
    >({});

    function handleChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const { value } = event.target;

        setFormData({ category_product_name: value });

        if (errors.category_product_name) {
            setErrors({ category_product_name: undefined });
        }
    }

    function validate(): boolean {
        const nextErrors: Partial<Record<keyof CategoryProductFormData, string>> = {};

        if (formData.category_product_name.trim() === "") {
            nextErrors.category_product_name = "Category product name is required.";
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
            category_product_name: formData.category_product_name.trim(),
        });
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6 p-6 md:p-8">
            <div className="space-y-2">
                <label
                    htmlFor="category_product_name"
                    className="block text-sm font-semibold"
                    style={{ color: "var(--text-secondary)" }}
                >
                    Category product name
                </label>
                <input
                    id="category_product_name"
                    name="category_product_name"
                    type="text"
                    value={formData.category_product_name}
                    onChange={handleChange}
                    placeholder="Enter category product name"
                    className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                    style={{
                        background: "var(--input-bg)",
                        color: "var(--input-text)",
                        border: `1px solid ${errors.category_product_name ? "var(--accent-error)" : "var(--input-border)"}`,
                        fontFamily: "var(--font-sans)",
                    }}
                />
                {errors.category_product_name ? (
                    <p className="text-xs" style={{ color: "var(--accent-error)" }}>
                        {errors.category_product_name}
                    </p>
                ) : null}
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
                    {isSubmitting ? "Saving..." : "Save category product"}
                </button>
            </div>
        </form>
    );
}
