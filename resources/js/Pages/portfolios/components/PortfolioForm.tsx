import * as React from 'react';
import { FolderOpen } from 'lucide-react';
import type { PortfolioFormData, ProjectTypeOption } from '@/modules/portfolios/types';

interface PortfolioFormProps {
    projectTypes: ProjectTypeOption[];
    initialData?: Partial<PortfolioFormData>;
    onSubmit: (data: PortfolioFormData) => Promise<void>;
    onCancel: () => void;
    isSubmitting: boolean;
}

export default function PortfolioForm({
    projectTypes,
    initialData,
    onSubmit,
    onCancel,
    isSubmitting,
}: PortfolioFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<PortfolioFormData>({
        project_type_uuid: initialData?.project_type_uuid ?? null,
    });

    async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
        event.preventDefault();
        await onSubmit(formData);
    }

    const inputStyle: React.CSSProperties = {
        border: '1px solid var(--border-default)',
        background: 'var(--bg-surface)',
        color: 'var(--text-primary)',
        fontFamily: 'var(--font-sans)',
    };

    return (
        <form onSubmit={(e) => { void handleSubmit(e); }}>
            <div className="space-y-6 p-6">
                <div className="flex flex-col gap-2">
                    <label
                        htmlFor="project_type_uuid"
                        className="text-sm font-semibold"
                        style={{ color: 'var(--text-primary)' }}
                    >
                        Project Type
                    </label>
                    <div className="relative">
                        <FolderOpen
                            size={16}
                            className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2"
                            style={{ color: 'var(--text-muted)' }}
                        />
                        <select
                            id="project_type_uuid"
                            value={formData.project_type_uuid ?? ''}
                            onChange={(e) =>
                                setFormData((prev) => ({
                                    ...prev,
                                    project_type_uuid: e.target.value === '' ? null : e.target.value,
                                }))
                            }
                            className="w-full rounded-xl py-3 pl-10 pr-4 text-sm outline-none"
                            style={inputStyle}
                        >
                            <option value="">— Select project type —</option>
                            {projectTypes.map((pt) => (
                                <option key={pt.uuid} value={pt.uuid}>
                                    {pt.title}
                                    {pt.service_category_name !== null && pt.service_category_name !== undefined
                                        ? ` · ${pt.service_category_name}`
                                        : ''}
                                </option>
                            ))}
                        </select>
                    </div>
                    <p className="text-xs" style={{ color: 'var(--text-muted)' }}>
                        Associate this portfolio entry with a project type for categorization.
                    </p>
                </div>
            </div>

            <div
                className="flex items-center justify-end gap-3 px-6 py-4"
                style={{ borderTop: '1px solid var(--border-default)', background: 'var(--bg-subtle)' }}
            >
                <button
                    type="button"
                    onClick={onCancel}
                    disabled={isSubmitting}
                    className="btn-ghost rounded-xl px-5 py-2.5 text-sm font-semibold disabled:opacity-50"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    disabled={isSubmitting}
                    className="btn-primary rounded-xl px-5 py-2.5 text-sm font-semibold disabled:opacity-50"
                >
                    {isSubmitting ? 'Saving…' : 'Save Portfolio'}
                </button>
            </div>
        </form>
    );
}
