import * as React from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { ArrowLeft } from "lucide-react";
import { useUpdateDocumentTemplateAlliance } from "@/modules/document-template-alliances/hooks/useDocumentTemplateAllianceMutations";
import type {
    DocumentTemplateAlliance,
    DocumentTemplateAllianceFormData,
} from "@/modules/document-template-alliances/types";
import AppLayout from "@/pages/layouts/AppLayout";
import DocumentTemplateAllianceForm from "./components/DocumentTemplateAllianceForm";

interface EditPageProps extends PageProps {
    documentTemplateAlliance: DocumentTemplateAlliance;
}

export default function DocumentTemplateAllianceEditPage(): React.JSX.Element {
    const { documentTemplateAlliance } = usePage<EditPageProps>().props;
    const t = documentTemplateAlliance;

    const [formData, setFormData] = React.useState<DocumentTemplateAllianceFormData>({
        template_name_alliance: t.template_name_alliance,
        template_description_alliance: t.template_description_alliance ?? "",
        template_type_alliance: t.template_type_alliance,
        template_path_alliance: null,
        alliance_company_id: String(t.alliance_company_id),
    });
    const [errors, setErrors] = React.useState<Partial<Record<keyof DocumentTemplateAllianceFormData, string>>>({});
    const updateMutation = useUpdateDocumentTemplateAlliance();

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
            await updateMutation.mutateAsync({ uuid: t.uuid, formData: data });
            router.visit(`/document-template-alliances/${t.uuid}`);
        } catch (error: unknown) {
            if (error instanceof Error) {
                setErrors({ template_name_alliance: error.message });
            }
        }
    }

    return (
        <>
            <Head title={`Edit — ${t.template_name_alliance}`} />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex items-center gap-3">
                        <Link
                            href={`/document-template-alliances/${t.uuid}`}
                            className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0"
                            aria-label="Back to detail"
                        >
                            <ArrowLeft size={16} />
                        </Link>
                        <div>
                            <h1
                                className="text-2xl font-extrabold tracking-tight"
                                style={{ color: "var(--text-primary)" }}
                            >
                                Edit Template
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                {t.template_name_alliance}
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={(e) => { void handleSubmit(e); }}
                        className="card"
                        style={{ fontFamily: "var(--font-sans)" }}
                    >
                        <DocumentTemplateAllianceForm
                            formData={formData}
                            onChange={handleChange}
                            errors={errors}
                            isEditing
                        />

                        <div className="mt-8 flex items-center justify-end gap-3">
                            <Link
                                href={`/document-template-alliances/${t.uuid}`}
                                className="btn-ghost rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={updateMutation.isPending}
                                className="btn-primary rounded-xl px-6 py-3 text-sm font-semibold disabled:opacity-60"
                            >
                                {updateMutation.isPending ? "Saving…" : "Save Changes"}
                            </button>
                        </div>
                    </form>
                </div>
            </AppLayout>
        </>
    );
}
