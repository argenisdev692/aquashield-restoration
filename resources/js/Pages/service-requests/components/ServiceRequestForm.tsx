import * as React from "react";
import type { ServiceRequestFormData } from "@/modules/service-requests/types";

interface ServiceRequestFormProps {
    initialData?: Partial<ServiceRequestFormData>;
    onSubmit: (data: ServiceRequestFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function ServiceRequestForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: ServiceRequestFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<ServiceRequestFormData>({
        requested_service: initialData?.requested_service ?? "",
    });
    const [errors, setErrors] = React.useState<Partial<Record<keyof ServiceRequestFormData, string>>>({});

    function handleChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const { name, value } = event.target;
        const field = name as keyof ServiceRequestFormData;

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
        const nextErrors: Partial<Record<keyof ServiceRequestFormData, string>> = {};

        if (formData.requested_service.trim() === "") {
            nextErrors.requested_service = "Requested service is required.";
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
            requested_service: formData.requested_service.trim(),
        });
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6 p-6 md:p-8">
            <div className="space-y-2">
                <label htmlFor="requested_service" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>
                    Requested service
                </label>
                <input
                    id="requested_service"
                    name="requested_service"
                    type="text"
                    value={formData.requested_service}
                    onChange={handleChange}
                    placeholder="Enter requested service"
                    className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                    style={{
                        background: "var(--input-bg)",
                        color: "var(--input-text)",
                        border: `1px solid ${errors.requested_service ? "var(--accent-error)" : "var(--input-border)"}`,
                        fontFamily: "var(--font-sans)",
                    }}
                />
                {errors.requested_service ? (
                    <p className="text-xs" style={{ color: "var(--accent-error)" }}>
                        {errors.requested_service}
                    </p>
                ) : null}
            </div>

            <div className="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                <button type="button" onClick={onCancel} disabled={isSubmitting} className="btn-ghost px-5 py-3 text-sm font-semibold">
                    Cancel
                </button>
                <button type="submit" disabled={isSubmitting} className="btn-primary px-5 py-3 text-sm font-semibold">
                    {isSubmitting ? "Saving..." : "Save service request"}
                </button>
            </div>
        </form>
    );
}
