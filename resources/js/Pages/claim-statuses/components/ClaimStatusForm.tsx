import * as React from "react";
import type { ClaimStatusFormData } from "@/modules/claim-statuses/types";

interface ClaimStatusFormProps {
    initialData?: Partial<ClaimStatusFormData>;
    onSubmit: (data: ClaimStatusFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

export default function ClaimStatusForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: ClaimStatusFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<ClaimStatusFormData>({
        claim_status_name: initialData?.claim_status_name ?? "",
        background_color: initialData?.background_color ?? "#3B82F6",
    });
    const [errors, setErrors] = React.useState<
        Partial<Record<keyof ClaimStatusFormData, string>>
    >({});

    function handleChange(
        event: React.ChangeEvent<HTMLInputElement>,
    ): void {
        const { name, value } = event.target;
        const field = name as keyof ClaimStatusFormData;

        setFormData((current) => ({ ...current, [field]: value }));

        if (errors[field]) {
            setErrors((current) => ({ ...current, [field]: undefined }));
        }
    }

    function validate(): boolean {
        const nextErrors: Partial<Record<keyof ClaimStatusFormData, string>> =
            {};

        if (formData.claim_status_name.trim() === "") {
            nextErrors.claim_status_name = "Status name is required.";
        }

        if (
            formData.background_color &&
            !/^#[0-9A-Fa-f]{6}$/.test(formData.background_color)
        ) {
            nextErrors.background_color =
                "Must be a valid hex color (e.g. #FF5733).";
        }

        setErrors(nextErrors);

        return Object.keys(nextErrors).length === 0;
    }

    async function handleSubmit(
        event: React.FormEvent<HTMLFormElement>,
    ): Promise<void> {
        event.preventDefault();

        if (!validate()) {
            return;
        }

        await onSubmit({
            claim_status_name: formData.claim_status_name.trim(),
            background_color: formData.background_color || "",
        });
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6 p-6 md:p-8">
            <div className="grid gap-6 md:grid-cols-2">
                <div className="space-y-2 md:col-span-2">
                    <label
                        htmlFor="claim_status_name"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-secondary)" }}
                    >
                        Status name
                    </label>
                    <input
                        id="claim_status_name"
                        name="claim_status_name"
                        type="text"
                        value={formData.claim_status_name}
                        onChange={handleChange}
                        placeholder="e.g. Open, In Progress, Closed"
                        className="w-full rounded-xl px-4 py-3 text-sm outline-none"
                        style={{
                            background: "var(--input-bg)",
                            color: "var(--input-text)",
                            border: `1px solid ${errors.claim_status_name ? "var(--accent-error)" : "var(--input-border)"}`,
                            fontFamily: "var(--font-sans)",
                        }}
                    />
                    {errors.claim_status_name ? (
                        <p
                            className="text-xs"
                            style={{ color: "var(--accent-error)" }}
                        >
                            {errors.claim_status_name}
                        </p>
                    ) : null}
                </div>

                <div className="space-y-2">
                    <label
                        htmlFor="background_color"
                        className="block text-sm font-semibold"
                        style={{ color: "var(--text-secondary)" }}
                    >
                        Background color
                    </label>
                    <div className="flex items-center gap-3">
                        <input
                            id="background_color_picker"
                            type="color"
                            value={formData.background_color || "#3B82F6"}
                            onChange={(e) => {
                                setFormData((current) => ({
                                    ...current,
                                    background_color: e.target.value.toUpperCase(),
                                }));
                                if (errors.background_color) {
                                    setErrors((current) => ({
                                        ...current,
                                        background_color: undefined,
                                    }));
                                }
                            }}
                            className="h-10 w-14 cursor-pointer rounded-lg border-0 p-0.5"
                            style={{
                                background: "var(--input-bg)",
                                border: "1px solid var(--input-border)",
                            }}
                            aria-label="Pick background color"
                        />
                        <input
                            id="background_color"
                            name="background_color"
                            type="text"
                            value={formData.background_color}
                            onChange={handleChange}
                            placeholder="#3B82F6"
                            maxLength={7}
                            className="flex-1 rounded-xl px-4 py-3 font-mono text-sm uppercase outline-none"
                            style={{
                                background: "var(--input-bg)",
                                color: "var(--input-text)",
                                border: `1px solid ${errors.background_color ? "var(--accent-error)" : "var(--input-border)"}`,
                                fontFamily: "var(--font-sans)",
                            }}
                        />
                    </div>
                    {errors.background_color ? (
                        <p
                            className="text-xs"
                            style={{ color: "var(--accent-error)" }}
                        >
                            {errors.background_color}
                        </p>
                    ) : null}
                </div>

                <div className="space-y-2">
                    <p
                        className="text-sm font-semibold"
                        style={{ color: "var(--text-secondary)" }}
                    >
                        Preview
                    </p>
                    <div
                        className="flex h-10 items-center justify-center rounded-xl px-4 text-sm font-semibold"
                        style={{
                            background: formData.background_color || "var(--bg-surface)",
                            color: "#ffffff",
                            border: "1px solid var(--border-default)",
                        }}
                    >
                        {formData.claim_status_name || "Status Preview"}
                    </div>
                </div>
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
                    {isSubmitting ? "Saving..." : "Save claim status"}
                </button>
            </div>
        </form>
    );
}
