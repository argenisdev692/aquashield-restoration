import * as React from "react";
import type { ContactSupportFormData } from "@/modules/contact-supports/types";

interface ContactSupportFormProps {
    initialData?: Partial<ContactSupportFormData>;
    onSubmit: (data: ContactSupportFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function ContactSupportForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: ContactSupportFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<ContactSupportFormData>({
        first_name: initialData?.first_name ?? "",
        last_name: initialData?.last_name ?? "",
        email: initialData?.email ?? "",
        phone: initialData?.phone ?? "",
        message: initialData?.message ?? "",
        sms_consent: initialData?.sms_consent ?? false,
        readed: initialData?.readed ?? false,
    });
    const [errors, setErrors] = React.useState<
        Partial<Record<keyof ContactSupportFormData, string>>
    >({});

    function handleTextChange(
        event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>,
    ): void {
        const { name, value } = event.target;
        const field = name as keyof ContactSupportFormData;

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

    function handleCheckboxChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const { name, checked } = event.target;
        const field = name as keyof ContactSupportFormData;

        setFormData((current) => ({
            ...current,
            [field]: checked,
        }));
    }

    function validate(): boolean {
        const nextErrors: Partial<Record<keyof ContactSupportFormData, string>> = {};

        if (formData.first_name.trim() === "") {
            nextErrors.first_name = "First name is required.";
        }

        if (formData.email.trim() === "") {
            nextErrors.email = "Email is required.";
        }

        if (formData.message.trim() === "") {
            nextErrors.message = "Message is required.";
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
            first_name: formData.first_name.trim(),
            last_name: formData.last_name.trim(),
            email: formData.email.trim(),
            phone: formData.phone.trim(),
            message: formData.message.trim(),
        });
    }

    function renderError(field: keyof ContactSupportFormData): React.JSX.Element | null {
        return errors[field] ? (
            <p className="text-xs" style={{ color: "var(--accent-error)" }}>
                {errors[field]}
            </p>
        ) : null;
    }

    function inputStyle(hasError: boolean): React.CSSProperties {
        return {
            background: "var(--input-bg)",
            color: "var(--input-text)",
            border: `1px solid ${hasError ? "var(--accent-error)" : "var(--input-border)"}`,
            fontFamily: "var(--font-sans)",
        };
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6 p-6 md:p-8">
            <div className="grid gap-6 md:grid-cols-2">
                <div className="space-y-2">
                    <label htmlFor="first_name" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>
                        First name
                    </label>
                    <input id="first_name" name="first_name" type="text" value={formData.first_name} onChange={handleTextChange} className="w-full rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(Boolean(errors.first_name))} />
                    {renderError("first_name")}
                </div>

                <div className="space-y-2">
                    <label htmlFor="last_name" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>
                        Last name
                    </label>
                    <input id="last_name" name="last_name" type="text" value={formData.last_name} onChange={handleTextChange} className="w-full rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(false)} />
                </div>

                <div className="space-y-2">
                    <label htmlFor="email" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>
                        Email
                    </label>
                    <input id="email" name="email" type="email" value={formData.email} onChange={handleTextChange} className="w-full rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(Boolean(errors.email))} />
                    {renderError("email")}
                </div>

                <div className="space-y-2">
                    <label htmlFor="phone" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>
                        Phone
                    </label>
                    <input id="phone" name="phone" type="text" value={formData.phone} onChange={handleTextChange} className="w-full rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(false)} />
                </div>
            </div>

            <div className="space-y-2">
                <label htmlFor="message" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>
                    Message
                </label>
                <textarea id="message" name="message" value={formData.message} onChange={handleTextChange} rows={6} className="w-full resize-none rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(Boolean(errors.message))} />
                {renderError("message")}
            </div>

            <div className="grid gap-4 md:grid-cols-2">
                <label className="flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold" style={{ borderColor: "var(--border-default)", color: "var(--text-secondary)" }}>
                    <input type="checkbox" name="sms_consent" checked={formData.sms_consent} onChange={handleCheckboxChange} className="h-4 w-4" style={{ accentColor: "var(--accent-primary)" }} />
                    SMS consent granted
                </label>
                <label className="flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold" style={{ borderColor: "var(--border-default)", color: "var(--text-secondary)" }}>
                    <input type="checkbox" name="readed" checked={formData.readed} onChange={handleCheckboxChange} className="h-4 w-4" style={{ accentColor: "var(--accent-primary)" }} />
                    Mark as read
                </label>
            </div>

            <div className="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                <button type="button" onClick={onCancel} disabled={isSubmitting} className="btn-ghost px-5 py-3 text-sm font-semibold">
                    Cancel
                </button>
                <button type="submit" disabled={isSubmitting} className="btn-primary px-5 py-3 text-sm font-semibold">
                    {isSubmitting ? "Saving..." : "Save contact support"}
                </button>
            </div>
        </form>
    );
}
