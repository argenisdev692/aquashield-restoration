import * as React from "react";
import type { TypeDamageFormData } from "@/modules/type-damages/types";

interface TypeDamageFormProps {
    initialData?: Partial<TypeDamageFormData>;
    onSubmit: (data: TypeDamageFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function TypeDamageForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: TypeDamageFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<TypeDamageFormData>({
        type_damage_name: initialData?.type_damage_name ?? "",
        description: initialData?.description ?? "",
        severity: initialData?.severity ?? "low",
    });
    const [errors, setErrors] = React.useState<
        Partial<Record<keyof TypeDamageFormData, string>>
    >({});

    function handleChange(
        event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>,
    ): void {
        const { name, value } = event.target;
        const field = name as keyof TypeDamageFormData;

        setFormData((current) => ({
            ...current,
            [field]: value,
        }));

        if (errors[field]) {
            setErrors((current) => ({
                ...current,
                [field]: undefined,
            }));
        }
    }

    function validate(): boolean {
        const nextErrors: Partial<Record<keyof TypeDamageFormData, string>> = {};

        if (formData.type_damage_name.trim() === "") {
            nextErrors.type_damage_name = "Type damage name is required.";
        }

        if (!["low", "medium", "high"].includes(formData.severity)) {
            nextErrors.severity = "Severity is invalid.";
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
            ...formData,
            type_damage_name: formData.type_damage_name.trim(),
            description: formData.description.trim(),
        });
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6 p-6 md:p-8">
            <div className="grid gap-6 md:grid-cols-2">
                <div className="space-y-2 md:col-span-2">
                    <label
                        htmlFor="type_damage_name"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-secondary)" }}
                    >
                        Type damage name
                    </label>
                    <input
                        id="type_damage_name"
                        name="type_damage_name"
                        type="text"
                        value={formData.type_damage_name}
                        onChange={handleChange}
                        placeholder="Enter type damage name"
                        className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: `1px solid ${errors.type_damage_name ? "var(--accent-error)" : "var(--input-border)"}`,
                            fontFamily: "var(--font-sans)",
                        }}
                    />
                    {errors.type_damage_name ? (
                        <p className="text-xs" style={{ color: "var(--accent-error)" }}>
                            {errors.type_damage_name}
                        </p>
                    ) : null}
                </div>

                <div className="space-y-2">
                    <label
                        htmlFor="severity"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-secondary)" }}
                    >
                        Severity
                    </label>
                    <select
                        id="severity"
                        name="severity"
                        value={formData.severity}
                        onChange={handleChange}
                        className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: `1px solid ${errors.severity ? "var(--accent-error)" : "var(--input-border)"}`,
                            fontFamily: "var(--font-sans)",
                        }}
                    >
                        <option value="low">Low</option>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                    </select>
                    {errors.severity ? (
                        <p className="text-xs" style={{ color: "var(--accent-error)" }}>
                            {errors.severity}
                        </p>
                    ) : null}
                </div>
            </div>

            <div className="space-y-2">
                <label
                    htmlFor="description"
                    className="block text-sm font-semibold"
                    style={{ color: "var(--text-secondary)" }}
                >
                    Description
                </label>
                <textarea
                    id="description"
                    name="description"
                    value={formData.description}
                    onChange={handleChange}
                    rows={5}
                    placeholder="Add an optional description"
                    className="w-full resize-none rounded-xl px-4 py-3 text-sm outline-none"
                    style={{
                        background: "var(--input-bg)",
                        color: "var(--input-text)",
                        border: "1px solid var(--input-border)",
                        fontFamily: "var(--font-sans)",
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
                    {isSubmitting ? "Saving..." : "Save type damage"}
                </button>
            </div>
        </form>
    );
}
