import * as React from "react";
import type { EmailDataFormData } from "@/modules/email-data/types";

interface EmailDataFormProps {
    initialData?: Partial<EmailDataFormData>;
    onSubmit: (data: EmailDataFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function EmailDataForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: EmailDataFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<EmailDataFormData>({
        description: initialData?.description ?? "",
        email: initialData?.email ?? "",
        phone: initialData?.phone ?? "",
        type: initialData?.type ?? "",
    });
    const [errors, setErrors] = React.useState<
        Partial<Record<keyof EmailDataFormData, string>>
    >({});

    function handleChange(
        event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>,
    ): void {
        const { name, value } = event.target;
        const field = name as keyof EmailDataFormData;

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
        const nextErrors: Partial<Record<keyof EmailDataFormData, string>> = {};
        const normalizedEmail = formData.email.trim().toLowerCase();
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (normalizedEmail === "") {
            nextErrors.email = "Email is required.";
        } else if (!emailPattern.test(normalizedEmail)) {
            nextErrors.email = "Email format is invalid.";
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
            description: formData.description.trim(),
            email: formData.email.trim().toLowerCase(),
            phone: formData.phone.trim(),
            type: formData.type.trim(),
        });
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6 p-6 md:p-8">
            <div className="grid gap-6 md:grid-cols-2">
                <div className="space-y-2 md:col-span-2">
                    <label
                        htmlFor="email"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-secondary)" }}
                    >
                        Email
                    </label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value={formData.email}
                        onChange={handleChange}
                        placeholder="name@company.com"
                        className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: `1px solid ${errors.email ? "var(--accent-error)" : "var(--input-border)"}`,
                            fontFamily: "var(--font-sans)",
                        }}
                    />
                    {errors.email ? (
                        <p className="text-xs" style={{ color: "var(--accent-error)" }}>
                            {errors.email}
                        </p>
                    ) : null}
                </div>

                <div className="space-y-2">
                    <label
                        htmlFor="type"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-secondary)" }}
                    >
                        Type
                    </label>
                    <input
                        id="type"
                        name="type"
                        type="text"
                        value={formData.type}
                        onChange={handleChange}
                        placeholder="Admin, Info, Collections..."
                        className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: "1px solid var(--input-border)",
                            fontFamily: "var(--font-sans)",
                        }}
                    />
                </div>

                <div className="space-y-2">
                    <label
                        htmlFor="phone"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-secondary)" }}
                    >
                        Phone
                    </label>
                    <input
                        id="phone"
                        name="phone"
                        type="text"
                        value={formData.phone}
                        onChange={handleChange}
                        placeholder="+1 555 123 4567"
                        className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: "1px solid var(--input-border)",
                            fontFamily: "var(--font-sans)",
                        }}
                    />
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
                    placeholder="Add context for this inbox or contact point"
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
                    {isSubmitting ? "Saving..." : "Save email data"}
                </button>
            </div>
        </form>
    );
}
