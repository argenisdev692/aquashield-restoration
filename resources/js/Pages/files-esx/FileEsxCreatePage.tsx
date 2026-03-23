import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, FileText, Save, Upload } from 'lucide-react';
import { sileo } from 'sileo';
import AppLayout from '@/pages/layouts/AppLayout';
import { useFileEsxMutations } from '@/modules/files-esx/hooks/useFileEsxMutations';
import type { CreateFileEsxFormPayload } from '@/modules/files-esx/hooks/useFileEsxMutations';

interface FormErrors {
    file?: string;
    file_name?: string;
}

export default function FileEsxCreatePage(): React.JSX.Element {
    const [fileName, setFileName] = React.useState<string>('');
    const [selectedFile, setSelectedFile] = React.useState<File | null>(null);
    const [errors, setErrors] = React.useState<FormErrors>({});
    const [isPending, startTransition] = React.useTransition();
    const fileInputRef = React.useRef<HTMLInputElement>(null);

    const { createFileEsx } = useFileEsxMutations();

    function handleFileChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const file = event.target.files?.[0] ?? null;
        setSelectedFile(file);
        setErrors((prev) => ({ ...prev, file: undefined }));

        if (file && !fileName) {
            setFileName(file.name);
        }
    }

    function validate(): boolean {
        const next: FormErrors = {};

        if (selectedFile === null) {
            next.file = 'A file is required.';
        }

        setErrors(next);

        return Object.keys(next).length === 0;
    }

    function handleSubmit(event: React.FormEvent<HTMLFormElement>): void {
        event.preventDefault();

        if (!validate() || selectedFile === null) return;

        const payload: CreateFileEsxFormPayload = {
            file: selectedFile,
            file_name: fileName.trim() || undefined,
        };

        startTransition(async () => {
            try {
                await createFileEsx.mutateAsync(payload);
                router.visit('/files-esx');
            } catch (error: unknown) {
                if (
                    error !== null &&
                    typeof error === 'object' &&
                    'response' in error
                ) {
                    const axiosError = error as { response?: { data?: { errors?: Record<string, string[]> } } };
                    const serverErrors = axiosError.response?.data?.errors ?? {};
                    const mapped: FormErrors = {};

                    for (const [field, messages] of Object.entries(serverErrors)) {
                        mapped[field as keyof FormErrors] = messages[0];
                    }

                    setErrors(mapped);
                } else {
                    sileo.error({ title: 'An unexpected error occurred.' });
                }
            }
        });
    }

    return (
        <>
            <Head title="Create File ESX" />
            <AppLayout>
                <div className="mx-auto max-w-2xl">
                    <Link
                        href="/files-esx"
                        prefetch
                        className="mb-6 inline-flex items-center gap-2 text-sm font-medium transition-colors"
                        style={{ color: 'var(--text-muted)' }}
                    >
                        <ArrowLeft size={16} />
                        Back to Files ESX
                    </Link>

                    <div
                        className="rounded-2xl p-8 shadow-xl"
                        style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
                    >
                        {/* Header */}
                        <div className="mb-8 flex items-center gap-4">
                            <div
                                className="flex h-11 w-11 items-center justify-center rounded-xl"
                                style={{
                                    background: 'color-mix(in srgb, var(--accent-primary) 15%, transparent)',
                                    border: '1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)',
                                }}
                            >
                                <FileText size={20} style={{ color: 'var(--accent-primary)' }} />
                            </div>
                            <div>
                                <h1
                                    className="text-2xl font-extrabold tracking-tight"
                                    style={{ color: 'var(--text-primary)' }}
                                >
                                    Create File ESX
                                </h1>
                                <p className="mt-0.5 text-sm" style={{ color: 'var(--text-muted)' }}>
                                    Upload a new ESX claim file to R2 storage
                                </p>
                            </div>
                        </div>

                        <form onSubmit={handleSubmit} className="flex flex-col gap-5" noValidate>
                            {/* File upload */}
                            <div className="flex flex-col gap-1.5">
                                <label
                                    htmlFor="file"
                                    className="text-sm font-semibold"
                                    style={{ color: 'var(--text-secondary)' }}
                                >
                                    File
                                    <span className="ml-1 text-xs" style={{ color: 'var(--accent-error)' }}>*</span>
                                </label>

                                <button
                                    type="button"
                                    onClick={() => fileInputRef.current?.click()}
                                    className="flex min-h-[100px] w-full flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed px-4 py-6 transition-colors"
                                    style={{
                                        borderColor: errors.file
                                            ? 'var(--accent-error)'
                                            : selectedFile
                                                ? 'var(--accent-primary)'
                                                : 'var(--border-default)',
                                        background: selectedFile
                                            ? 'color-mix(in srgb, var(--accent-primary) 6%, transparent)'
                                            : 'var(--bg-surface)',
                                    }}
                                    aria-label="Select file to upload"
                                >
                                    <Upload
                                        size={24}
                                        style={{ color: selectedFile ? 'var(--accent-primary)' : 'var(--text-disabled)' }}
                                    />
                                    {selectedFile ? (
                                        <div className="text-center">
                                            <p className="text-sm font-semibold" style={{ color: 'var(--accent-primary)' }}>
                                                {selectedFile.name}
                                            </p>
                                            <p className="text-xs" style={{ color: 'var(--text-muted)' }}>
                                                {(selectedFile.size / 1024).toFixed(1)} KB — click to change
                                            </p>
                                        </div>
                                    ) : (
                                        <div className="text-center">
                                            <p className="text-sm font-semibold" style={{ color: 'var(--text-secondary)' }}>
                                                Click to select a file
                                            </p>
                                            <p className="text-xs" style={{ color: 'var(--text-muted)' }}>
                                                PDF, DOC, DOCX, XLS, XLSX, ESX, ZIP, TXT — max 50 MB
                                            </p>
                                        </div>
                                    )}
                                </button>

                                <input
                                    ref={fileInputRef}
                                    id="file"
                                    name="file"
                                    type="file"
                                    accept=".pdf,.doc,.docx,.xls,.xlsx,.esx,.zip,.txt"
                                    onChange={handleFileChange}
                                    className="sr-only"
                                    aria-label="File upload input"
                                />

                                {errors.file && (
                                    <p className="text-xs" style={{ color: 'var(--accent-error)' }}>
                                        {errors.file}
                                    </p>
                                )}
                            </div>

                            {/* File Name */}
                            <div className="flex flex-col gap-1.5">
                                <label
                                    htmlFor="file_name"
                                    className="text-sm font-semibold"
                                    style={{ color: 'var(--text-secondary)' }}
                                >
                                    Display Name
                                    <span className="ml-1 text-xs font-normal" style={{ color: 'var(--text-muted)' }}>
                                        (optional — defaults to file name)
                                    </span>
                                </label>
                                <input
                                    id="file_name"
                                    name="file_name"
                                    type="text"
                                    value={fileName}
                                    onChange={(e) => {
                                        setFileName(e.target.value);
                                        setErrors((prev) => ({ ...prev, file_name: undefined }));
                                    }}
                                    placeholder="e.g. Claim 2024-001"
                                    className="h-11 rounded-lg border px-4 text-sm outline-none transition-colors"
                                    style={{
                                        background: 'var(--input-bg)',
                                        borderColor: errors.file_name
                                            ? 'var(--accent-error)'
                                            : 'var(--input-border)',
                                        color: 'var(--text-primary)',
                                    }}
                                />
                                {errors.file_name && (
                                    <p className="text-xs" style={{ color: 'var(--accent-error)' }}>
                                        {errors.file_name}
                                    </p>
                                )}
                            </div>

                            {/* Actions */}
                            <div
                                className="mt-2 flex items-center justify-end gap-3 border-t pt-6"
                                style={{ borderColor: 'var(--border-subtle)' }}
                            >
                                <Link
                                    href="/files-esx"
                                    className="btn-ghost px-5 py-2 text-sm font-semibold"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    disabled={isPending || createFileEsx.isPending || selectedFile === null}
                                    className="btn-primary flex items-center gap-2 px-5 py-2 text-sm font-semibold disabled:opacity-60"
                                >
                                    <Save size={16} />
                                    {isPending || createFileEsx.isPending ? 'Uploading…' : 'Upload File ESX'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
