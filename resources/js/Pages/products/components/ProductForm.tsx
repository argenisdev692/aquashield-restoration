import * as React from "react";
import type { ProductFormData, Category } from "@/modules/products/types";

interface ProductFormProps {
    initialData?: Partial<ProductFormData>;
    categories: Category[];
    onSubmit: (data: ProductFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function ProductForm({
    initialData,
    categories,
    onSubmit,
    isSubmitting,
    onCancel,
}: ProductFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<ProductFormData>({
        categoryId: initialData?.categoryId || "",
        name: initialData?.name || "",
        description: initialData?.description || "",
        price: initialData?.price || 0,
        unit: initialData?.unit || "",
        orderPosition: initialData?.orderPosition || 1,
    });

    const [errors, setErrors] = React.useState<Partial<Record<keyof ProductFormData, string>>>({});

    const handleChange = (
        e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>
    ) => {
        const { name, value } = e.target;
        setFormData((prev) => ({
            ...prev,
            [name]: name === "price" || name === "orderPosition" ? Number(value) : value,
        }));
        if (errors[name as keyof ProductFormData]) {
            setErrors((prev) => ({ ...prev, [name]: undefined }));
        }
    };

    const validate = (): boolean => {
        const newErrors: Partial<Record<keyof ProductFormData, string>> = {};

        if (!formData.categoryId) newErrors.categoryId = "Category is required";
        if (!formData.name.trim()) newErrors.name = "Product name is required";
        if (!formData.description.trim()) newErrors.description = "Description is required";
        if (formData.price <= 0) newErrors.price = "Price must be greater than 0";
        if (!formData.unit.trim()) newErrors.unit = "Unit is required";
        if (formData.orderPosition < 1) newErrors.orderPosition = "Order position must be at least 1";

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    };

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        if (!validate()) return;
        await onSubmit(formData);
    };

    return (
        <form onSubmit={handleSubmit} className="p-8 space-y-6">
            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div className="space-y-2">
                    <label
                        htmlFor="categoryId"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-primary)" }}
                    >
                        Category <span style={{ color: "var(--danger-primary)" }}>*</span>
                    </label>
                    <select
                        id="categoryId"
                        name="categoryId"
                        value={formData.categoryId}
                        onChange={handleChange}
                        className="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition-all"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: `1px solid ${errors.categoryId ? "var(--danger-primary)" : "var(--input-border)"}`,
                        }}
                    >
                        <option value="">Select a category</option>
                        {categories.map((cat) => (
                            <option key={cat.uuid} value={cat.uuid}>
                                {cat.category_product_name}
                            </option>
                        ))}
                    </select>
                    {errors.categoryId && (
                        <p className="text-xs" style={{ color: "var(--danger-primary)" }}>
                            {errors.categoryId}
                        </p>
                    )}
                </div>

                <div className="space-y-2">
                    <label
                        htmlFor="name"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-primary)" }}
                    >
                        Product Name <span style={{ color: "var(--danger-primary)" }}>*</span>
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value={formData.name}
                        onChange={handleChange}
                        placeholder="Enter product name"
                        className="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition-all"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: `1px solid ${errors.name ? "var(--danger-primary)" : "var(--input-border)"}`,
                        }}
                    />
                    {errors.name && (
                        <p className="text-xs" style={{ color: "var(--danger-primary)" }}>
                            {errors.name}
                        </p>
                    )}
                </div>
            </div>

            <div className="space-y-2">
                <label
                    htmlFor="description"
                    className="block text-sm font-semibold"
                    style={{ color: "var(--text-primary)" }}
                >
                    Description <span style={{ color: "var(--danger-primary)" }}>*</span>
                </label>
                <textarea
                    id="description"
                    name="description"
                    value={formData.description}
                    onChange={handleChange}
                    placeholder="Enter product description"
                    rows={4}
                    className="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition-all resize-none"
                    style={{
                        background: "var(--input-bg)",
                        color: "var(--input-text)",
                        border: `1px solid ${errors.description ? "var(--danger-primary)" : "var(--input-border)"}`,
                    }}
                />
                {errors.description && (
                    <p className="text-xs" style={{ color: "var(--danger-primary)" }}>
                        {errors.description}
                    </p>
                )}
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div className="space-y-2">
                    <label
                        htmlFor="price"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-primary)" }}
                    >
                        Price <span style={{ color: "var(--danger-primary)" }}>*</span>
                    </label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        value={formData.price}
                        onChange={handleChange}
                        step="0.01"
                        min="0"
                        placeholder="0.00"
                        className="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition-all"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: `1px solid ${errors.price ? "var(--danger-primary)" : "var(--input-border)"}`,
                        }}
                    />
                    {errors.price && (
                        <p className="text-xs" style={{ color: "var(--danger-primary)" }}>
                            {errors.price}
                        </p>
                    )}
                </div>

                <div className="space-y-2">
                    <label
                        htmlFor="unit"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-primary)" }}
                    >
                        Unit <span style={{ color: "var(--danger-primary)" }}>*</span>
                    </label>
                    <input
                        type="text"
                        id="unit"
                        name="unit"
                        value={formData.unit}
                        onChange={handleChange}
                        placeholder="e.g., unit, box, pack"
                        className="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition-all"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: `1px solid ${errors.unit ? "var(--danger-primary)" : "var(--input-border)"}`,
                        }}
                    />
                    {errors.unit && (
                        <p className="text-xs" style={{ color: "var(--danger-primary)" }}>
                            {errors.unit}
                        </p>
                    )}
                </div>

                <div className="space-y-2">
                    <label
                        htmlFor="orderPosition"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-primary)" }}
                    >
                        Order Position <span style={{ color: "var(--danger-primary)" }}>*</span>
                    </label>
                    <input
                        type="number"
                        id="orderPosition"
                        name="orderPosition"
                        value={formData.orderPosition}
                        onChange={handleChange}
                        min="1"
                        placeholder="1"
                        className="w-full px-4 py-2.5 rounded-xl text-sm outline-none transition-all"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: `1px solid ${errors.orderPosition ? "var(--danger-primary)" : "var(--input-border)"}`,
                        }}
                    />
                    {errors.orderPosition && (
                        <p className="text-xs" style={{ color: "var(--danger-primary)" }}>
                            {errors.orderPosition}
                        </p>
                    )}
                </div>
            </div>

            <div className="flex gap-4 pt-4">
                <button
                    type="submit"
                    disabled={isSubmitting}
                    className="flex-1 py-3 px-6 rounded-xl font-bold text-white transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                    style={{ background: "var(--accent-primary)" }}
                >
                    {isSubmitting ? "Saving..." : "Save Product"}
                </button>
                <button
                    type="button"
                    onClick={onCancel}
                    disabled={isSubmitting}
                    className="px-6 py-3 rounded-xl font-bold transition-all hover:scale-[1.02] active:scale-[0.98] disabled:opacity-50"
                    style={{
                        background: "var(--bg-subtle)",
                        color: "var(--text-primary)",
                        border: "1px solid var(--border-default)",
                    }}
                >
                    Cancel
                </button>
            </div>
        </form>
    );
}
