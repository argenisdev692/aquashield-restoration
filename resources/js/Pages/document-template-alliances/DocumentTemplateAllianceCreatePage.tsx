import * as React from "react";
import { Head, Link, router } from "@inertiajs/react";
import { ArrowLeft } from "lucide-react";
import { sileo } from "sileo";
import { useCreateDocumentTemplateAlliance } from "@/modules/document-template-alliances/hooks/useDocumentTemplateAllianceMutations";
import type { DocumentTemplateAllianceFormData } from "@/modules/document-template-alliances/types";
import AppLayout from "@/pages/layouts/AppLayout";
import DocumentTemplateAllianceForm from "./components/DocumentTemplateAllianceForm";

const EMPTY_FORM: DocumentTemplateAllianceFormData = {
    template_name_alliance: "",
    template_description_alliance: "",
    template_type_alliance: "",
    template_path_alliance: null,
    alliance_company_id: "",
};

export default function DocumentTemplateAllianceCreatePage(): React.JSX.Element {
    const [formData, setFormData] = React.useState<DocumentTemplateAllianceFormData>(EMPTY_FORM);
    const [errors, setErrors] = React.useState<Partial<Record<keyof DocumentTemplateAllianceFormData, string>>>({});
    const createMutation = useCreateDocumentTemplateAlliance();

    function handleChange(field: keyof DocumentTemplateAllianceFormData, value: string | File | null): void {
        setFormData((prev) => ({ ...prev, [field]: value }));
        setErrors((prev) => ({ ...prev, [field]: undefined }));
    }

    function validate(): boolean {
        const newErrors: Partial<Record<keyof DocumentTemplateAllianceFormData, string>> = {};

        if (!formData.template_name_alliance.trim()) {
            newErrors.template_name_alliance = "Template name is required.";
        }
        if (!formData.template_type_alliance) {
            newErrors.template_type_alliance = "Template type is required.";
        }
        if (!formData.alliance_company_id) {
            newErrors.alliance_company_id = "Alliance company ID is required.";
        }
        if (formData.template_path_alliance === null) {
            newErrors.template_path_alliance = "Template file is required.";
        }

        setErrors(newErrors);

        return Object.keys(newErrors).length === 0;
    }

    async function handleSubmit(event: React.FormEvent): Promise<void> {
        event.preventDefault();

        if (!validate()) return;

        const data = new FormData();
        data.append("template_name_alliance", formData.template_name_alliance);
        data.append("template_description_alliance", formData.template_description_alliance);
        data.append("template_type_alliance", formData.template_type_alliance);
        data.append("alliance_company_id", formData.alliance_company_id);

        if (formData.template_path_alliance) {
            data.append("template_path_alliance", formData.template_path_alliance);
        }

        try {
            await createMutation.mutateAsync(data);
            sileo.success({ title: "Template uploaded successfully." });
            router.visit("/document-template-alliances");
        } catch (error: unknown) {
            sileo.error({ title: "Failed to upload template. Please try again." });
            if (error instanceof Error) {
                setErrors({ template_name_alliance: error.message });
            }
        }
    }

    return (
        <>
            <Head title="New Document Template Alliance" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/document-template-alliances"
                            className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0"
                            aria-label="Back to list"
                        >
                            <ArrowLeft size={16} />
                        </Link>
                        <div>
                            <h1
                                className="text-2xl font-extrabold tracking-tight"
                                style={{ color: "var(--text-primary)" }}
                            >
                                New Document Template Alliance
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Upload a new alliance document template
                            </p>
                        </div>
                    </div>

                    <form onSubmit={(e) => { void handleSubmit(e); }} className="card" style={{ fontFamily: "var(--font-sans)" }}>
                        <DocumentTemplateAllianceForm
                            formData={formData}
                            onChange={handleChange}
                            errors={errors}
                        />

                        <div className="mt-8 flex items-center justify-end gap-3">
                            <Link
                                href="/document-template-alliances"
                                className="btn-ghost rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={createMutation.isPending}
                                className="btn-primary rounded-xl px-6 py-3 text-sm font-semibold disabled:opacity-60"
                            >
                                {createMutation.isPending ? "Saving…" : "Save Template"}
                            </button>
                        </div>
                    </form>
                </div>
            </AppLayout>
        </>
    );
}
