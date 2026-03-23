import * as React from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import type { PageProps } from '@inertiajs/core';
import { ArrowLeft, FileText, Save } from 'lucide-react';
import { sileo } from 'sileo';
import AppLayout from '@/pages/layouts/AppLayout';
import { useFileEsx } from '@/modules/files-esx/hooks/useFileEsx';
import { useFileEsxMutations } from '@/modules/files-esx/hooks/useFileEsxMutations';
import type { FileEsxFormErrors } from '@/modules/files-esx/types';

interface FileEsxEditPageProps extends PageProps {
    uuid: string;
}

export default function FileEsxEditPage(): React.JSX.Element {
    const { uuid } = usePage<FileEsxEditPageProps>().props;
    const { data, isPending: isLoadingFile } = useFileEsx(uuid);
    const file = data?.data ?? null;

    const [fileName, setFileName] = React.useState<string>('');
    const [errors, setErrors] = React.useState<FileEsxFormErrors>({});
    const [isPending, startTransition] = React.useTransition();
    const [initialized, setInitialized] = React.useState<boolean>(false);

    React.useEffect(() => {
        if (file !== null && !initialized) {
            setFileName(file.file_name ?? '');
            setInitialized(true);
        }
    }, [file, initialized]);

    const { updateFileEsx } = useFileEsxMutations();

    function handleSubmit(event: React.FormEvent<HTMLFormElement>): void {
        event.preventDefault();

        setErrors({});

        startTransition(async () => {
            try {
                await updateFileEsx.mutateAsync({
                    uuid,
                    payload: { file_name: fileName },
                });
                router.visit(`/files-esx/${uuid}`);
            } catch (error: unknown) {
                if (
                    error !== null &&
                    typeof error === 'object' &&
                    'response' in error
                ) {
                    const axiosError = error as { response?: { data?: { errors?: Record<string, string[]> } } };
                    const serverErrors = axiosError.response?.data?.errors ?? {};
                    const mapped: FileEsxFormErrors = {};

                    for (const [field, messages] of Object.entries(serverErrors)) {
                        mapped[field as keyof FileEsxFormErrors] = messages[0];
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
            <Head title="Edit File ESX" />
            <AppLayout>
                <div className="mx-auto max-w-2xl">
                    <Link
                        href={`/files-esx/${uuid}`}
                        prefetch
                        className="mb-6 inline-flex items-center gap-2 text-sm font-medium transition-colors"
                        style={{ color: 'var(--text-muted)' }}
                    >
                        <ArrowLeft size={16} />
                        Back to File ESX
                    </Link>

                    <div
                        className="card rounded-2xl p-8 shadow-xl"
                        style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
                    >
                        {/* Header */}
                        <div className="mb-8 flex items-center gap-4">
                            <div
                                className="flex h-11 w-11 items-center justify-center rounded-xl"
                                style={{
                                    background: 'color-mix(in srgb, var(--accent-warning) 15%, transparent)',
                                    border: '1px solid color-mix(in srgb, var(--accent-warning) 25%, transparent)',
                                }}
                            >
                                <FileText size={20} style={{ color: 'var(--accent-warning)' }} />
                            </div>
                            <div>
                                <h1
                                    className="text-2xl font-extrabold tracking-tight"
                                    style={{ color: 'var(--text-primary)' }}
                                >
                                    Edit File ESX
                                </h1>
                                <p className="mt-0.5 font-mono text-xs" style={{ color: 'var(--text-muted)' }}>
                                    {file?.file_path ?? uuid}
                                </p>
                            </div>
                        </div>

                        {isLoadingFile ? (
                            <div className="flex items-center justify-center py-12">
                                <div
                                    className="h-8 w-8 animate-spin rounded-full border-2 border-t-transparent"
                                    style={{ borderColor: 'var(--accent-primary)' }}
                                />
                            </div>
                        ) : (
                            <form onSubmit={handleSubmit} className="flex flex-col gap-5" noValidate>
                                {/* File Path (read-only) */}
                                <div className="flex flex-col gap-1.5">
                                    <label
                                        className="text-sm font-semibold"
                                        style={{ color: 'var(--text-secondary)' }}
                                    >
                                        File Path
                                        <span className="ml-1 text-xs font-normal" style={{ color: 'var(--text-muted)' }}>
                                            (read-only)
                                        </span>
                                    </label>
                                    <div
                                        className="h-11 rounded-lg border px-4 flex items-center font-mono text-sm"
                                        style={{
                                            background: 'color-mix(in srgb, var(--bg-card) 60%, var(--bg-surface))',
                                            borderColor: 'var(--border-subtle)',
                                            color: 'var(--text-muted)',
                                        }}
                                    >
                                        {file?.file_path ?? '—'}
                                    </div>
                                </div>

                                {/* File Name */}
                                <div className="flex flex-col gap-1.5">
                                    <label
                                        htmlFor="file_name"
                                        className="text-sm font-semibold"
                                        style={{ color: 'var(--text-secondary)' }}
                                    >
                                        File Name
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
                                        placeholder="e.g. claim-2024-001.esx"
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
                                        href={`/files-esx/${uuid}`}
                                        className="btn-ghost px-5 py-2 text-sm font-semibold"
                                    >
                                        Cancel
                                    </Link>
                                    <button
                                        type="submit"
                                        disabled={isPending || updateFileEsx.isPending}
                                        className="btn-primary flex items-center gap-2 px-5 py-2 text-sm font-semibold disabled:opacity-60"
                                    >
                                        <Save size={16} />
                                        {isPending || updateFileEsx.isPending ? 'Saving…' : 'Save Changes'}
                                    </button>
                                </div>
                            </form>
                        )}
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
