import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { useCreateDocumentTemplateAdjuster } from '@/modules/document-template-adjusters/hooks/useDocumentTemplateAdjusterMutations';
import type { DocumentTemplateAdjusterFormData } from '@/modules/document-template-adjusters/types';
import AppLayout from '@/pages/layouts/AppLayout';
import DocumentTemplateAdjusterForm from './components/DocumentTemplateAdjusterForm';

const EMPTY_FORM: DocumentTemplateAdjusterFormData = {
    template_description_adjuster: '',
    template_type_adjuster: '',
    template_path_adjuster: null,
    public_adjuster_id: '',
};

export default function DocumentTemplateAdjusterCreatePage(): React.JSX.Element {
    const [formData, setFormData] = React.useState<DocumentTemplateAdjusterFormData>(EMPTY_FORM);
    const [errors, setErrors] = React.useState<Partial<Record<keyof DocumentTemplateAdjusterFormData, string>>>({});
    const createMutation = useCreateDocumentTemplateAdjuster();

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
        if (formData.template_path_adjuster === null) {
            newErrors.template_path_adjuster = 'Template file is required.';
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
            await createMutation.mutateAsync(fd);
            router.visit('/document-template-adjusters');
        } catch {
            // error toast handled by mutation onError
        }
    }

    return (
        <>
            <Head title="New Document Template Adjuster" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/document-template-adjusters"
                            className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0"
                            aria-label="Back to list"
                        >
                            <ArrowLeft size={16} />
                        </Link>
                        <div>
                            <h1
                                className="text-2xl font-extrabold tracking-tight"
                                style={{ color: 'var(--text-primary)' }}
                            >
                                New Document Template Adjuster
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Upload a new adjuster document template
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
                        />

                        <div className="mt-8 flex items-center justify-end gap-3">
                            <Link
                                href="/document-template-adjusters"
                                className="btn-ghost rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                disabled={createMutation.isPending}
                                className="btn-primary rounded-xl px-6 py-3 text-sm font-semibold disabled:opacity-60"
                            >
                                {createMutation.isPending ? 'Saving…' : 'Save Template'}
                            </button>
                        </div>
                    </form>
                </div>
            </AppLayout>
        </>
    );
}
