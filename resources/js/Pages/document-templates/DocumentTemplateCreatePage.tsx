import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { sileo } from 'sileo';
import { useCreateDocumentTemplate } from '@/modules/document-templates/hooks/useDocumentTemplateMutations';
import type { DocumentTemplateFormData } from '@/modules/document-templates/types';
import AppLayout from '@/pages/layouts/AppLayout';
import DocumentTemplateForm from './components/DocumentTemplateForm';

const EMPTY_FORM: DocumentTemplateFormData = {
    template_name: '',
    template_description: '',
    template_type: '',
    template_path: null,
};

export default function DocumentTemplateCreatePage(): React.JSX.Element {
    const [formData, setFormData] = React.useState<DocumentTemplateFormData>(EMPTY_FORM);
    const [errors, setErrors] = React.useState<
        Partial<Record<keyof DocumentTemplateFormData, string>>
    >({});
    const createMutation = useCreateDocumentTemplate();

    function handleChange(
        field: keyof DocumentTemplateFormData,
        value: string | File | null,
    ): void {
        setFormData((prev) => ({ ...prev, [field]: value }));
        setErrors((prev) => ({ ...prev, [field]: undefined }));
    }

    function validate(): boolean {
        const newErrors: Partial<Record<keyof DocumentTemplateFormData, string>> = {};
        if (!formData.template_name.trim()) {
            newErrors.template_name = 'Template name is required.';
        }
        if (!formData.template_type) {
            newErrors.template_type = 'Template type is required.';
        }
        if (formData.template_path === null) {
            newErrors.template_path = 'Template file is required.';
        }
        setErrors(newErrors);
        return Object.keys(newErrors).length === 0;
    }

    async function handleSubmit(event: React.FormEvent): Promise<void> {
        event.preventDefault();
        if (!validate()) return;

        const data = new FormData();
        data.append('template_name', formData.template_name);
        data.append('template_description', formData.template_description);
        data.append('template_type', formData.template_type);
        if (formData.template_path) {
            data.append('template_path', formData.template_path);
        }

        try {
            await createMutation.mutateAsync(data);
            sileo.success({ title: 'Template uploaded successfully.' });
            router.visit('/document-templates');
        } catch (error: unknown) {
            sileo.error({ title: 'Failed to upload template. Please try again.' });
            if (error instanceof Error) {
                setErrors({ template_name: error.message });
            }
        }
    }

    return (
        <>
            <Head title="New Document Template" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex items-center gap-3">
                        <Link
                            href="/document-templates"
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
                                New Document Template
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Upload a new document template
                            </p>
                        </div>
                    </div>

                    <form
                        onSubmit={(e) => {
                            void handleSubmit(e);
                        }}
                        className="card p-6"
                        style={{ fontFamily: 'var(--font-sans)' }}
                    >
                        <DocumentTemplateForm
                            formData={formData}
                            onChange={handleChange}
                            errors={errors}
                        />

                        <div className="mt-8 flex items-center justify-end gap-3">
                            <Link
                                href="/document-templates"
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
