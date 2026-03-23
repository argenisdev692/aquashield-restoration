import * as React from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { ArrowLeft } from 'lucide-react';
import { useUpdateDocumentTemplateAdjuster } from '@/modules/document-template-adjusters/hooks/useDocumentTemplateAdjusterMutations';
import type {
    DocumentTemplateAdjuster,
    DocumentTemplateAdjusterFormData,
} from '@/modules/document-template-adjusters/types';
import AppLayout from '@/pages/layouts/AppLayout';
import DocumentTemplateAdjusterForm from './components/DocumentTemplateAdjusterForm';

interface EditPageProps extends PageProps {
    uuid: string;
    documentTemplateAdjuster: DocumentTemplateAdjuster;
}

export default function DocumentTemplateAdjusterEditPage(): React.JSX.Element {
    const { documentTemplateAdjuster: t } = usePage<EditPageProps>().props;

    const [formData, setFormData] = React.useState<DocumentTemplateAdjusterFormData>({
        template_description_adjuster: t.template_description_adjuster ?? '',
        template_type_adjuster: t.template_type_adjuster,
        template_path_adjuster: null,
        public_adjuster_id: String(t.public_adjuster_id),
    });
    const [errors, setErrors] = React.useState<Partial<Record<keyof DocumentTemplateAdjusterFormData, string>>>({});
    const updateMutation = useUpdateDocumentTemplateAdjuster();

    function handleChange(field: keyof DocumentTemplateAdjusterFormData, value: string | File | null): void {
        setFormData((prev) => ({ ...prev, [field]: value }));
        setErrors((prev) => ({ ...prev, [field]: undefined }));
    }

    function validate(): boolean {
        const newErrors: Partial<Record<keyof DocumentTemplateAdjusterFormData, string>> = {};

        if (!formData.template_type_adjuster) {
            newErrors.template_type_adjuster = 'Template type is required.';
        }
        if (!formData.public_adjuster_id || Number(formData.public_adjuster_id) < 1) {
            newErrors.public_adjuster_id = 'Public adjuster user ID is required.';
        }

        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    }

    async function handleSubmit(event: React.FormEvent): Promise<void> {
        event.preventDefault();
        if (!validate()) return;

        const fd = new FormData();
        fd.append('template_type_adjuster', formData.template_type_adjuster);
        fd.append('template_description_adjuster', formData.template_description_adjuster);
        fd.append('public_adjuster_id', formData.public_adjuster_id);
        if (formData.template_path_adjuster) {
            fd.append('template_path_adjuster', formData.template_path_adjuster);
        }

        try {
            await updateMutation.mutateAsync({ uuid: t.uuid, formData: fd });
            router.visit(`/document-template-adjusters/${t.uuid}`);
        } catch {
            // error toast handled by mutation onError
        }
    }

    return (
        <>
            <Head title={`Edit — ${t.template_type_adjuster}`} />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex items-center gap-3">
                        <Link
                            href={`/document-template-adjusters/${t.uuid}`}
                            className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0"
                            aria-label="Back to detail"
                        >
                            <ArrowLeft size={16} />
                        </Link>
                        <div>
                            <h1
                                className="text-2xl font-extrabold tracking-tight"
                                style={{ color: 'var(--text-primary)' }}
                            >
                                Edit Adjuster Template
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Update this adjuster document template
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={(e) => { void handleSubmit(e); }}
                        className="card"
                        style={{ fontFamily: 'var(--font-sans)' }}
                    >
                        <DocumentTemplateAdjusterForm
                            formData={formData}
                            onChange={handleChange}
                            errors={errors}
                            isEditing
                        />

                        <div className="mt-8 flex items-center justify-end gap-3">
                            <Link
                                href={`/document-template-adjusters/${t.uuid}`}
                                className="btn-ghost rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={updateMutation.isPending}
                                className="btn-primary rounded-xl px-6 py-3 text-sm font-semibold disabled:opacity-60"
                            >
                                {updateMutation.isPending ? 'Updating…' : 'Update Template'}
                            </button>
                        </div>
                    </form>
                </div>
            </AppLayout>
        </>
    );
}
