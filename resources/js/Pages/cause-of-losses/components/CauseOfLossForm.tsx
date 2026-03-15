import * as React from "react";
import type { CauseOfLossFormData } from "@/modules/cause-of-losses/types";

interface CauseOfLossFormProps {
    initialData?: Partial<CauseOfLossFormData>;
    onSubmit: (data: CauseOfLossFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function CauseOfLossForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: CauseOfLossFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<CauseOfLossFormData>({
        cause_loss_name: initialData?.cause_loss_name ?? "",
        description: initialData?.description ?? "",
        severity: initialData?.severity ?? "low",
    });
    const [errors, setErrors] = React.useState<
        Partial<Record<keyof CauseOfLossFormData, string>>
    >({});

    function handleChange(
        event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>,
    ): void {
        const { name, value } = event.target;
        const field = name as keyof CauseOfLossFormData;

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
        const nextErrors: Partial<Record<keyof CauseOfLossFormData, string>> = {};

        if (formData.cause_loss_name.trim() === "") {
            nextErrors.cause_loss_name = "Cause of loss name is required.";
        }

        if (![
            "low",
            "medium",
            "high",
        ].includes(formData.severity)) {
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
            cause_loss_name: formData.cause_loss_name.trim(),
            description: formData.description.trim(),
        });
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6 p-6 md:p-8">
            <div className="grid gap-6 md:grid-cols-2">
                <div className="space-y-2 md:col-span-2">
                    <label
                        htmlFor="cause_loss_name"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-secondary)" }}
                    >
                        Cause of loss name
                    </label>
                    <input
                        id="cause_loss_name"
                        name="cause_loss_name"
                        type="text"
                        value={formData.cause_loss_name}
                        onChange={handleChange}
                        placeholder="Enter cause of loss name"
                        className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: `1px solid ${errors.cause_loss_name ? "var(--accent-error)" : "var(--input-border)"}`,
                            fontFamily: "var(--font-sans)",
                        }}
                    />
                    {errors.cause_loss_name ? (
                        <p className="text-xs" style={{ color: "var(--accent-error)" }}>
                            {errors.cause_loss_name}
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
                    {isSubmitting ? "Saving..." : "Save cause of loss"}
                </button>
            </div>
        </form>
    );
}
