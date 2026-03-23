import * as React from 'react';
import { Upload } from 'lucide-react';
import { DOCUMENT_TEMPLATE_TYPES } from '@/modules/document-templates/types';
import type { DocumentTemplateFormData } from '@/modules/document-templates/types';

interface DocumentTemplateFormProps {
    formData: DocumentTemplateFormData;
    onChange: (field: keyof DocumentTemplateFormData, value: string | File | null) => void;
    errors: Partial<Record<keyof DocumentTemplateFormData, string>>;
    isEditing?: boolean;
}

const fieldStyle: React.CSSProperties = {
    width: '100%',
    height: 'var(--input-height)',
    padding: '0 12px',
    fontSize: '14px',
    background: 'var(--input-bg)',
    border: '1px solid var(--input-border)',
    borderRadius: 'var(--input-radius)',
    color: 'var(--input-text)',
    fontFamily: 'var(--font-sans)',
    outline: 'none',
    transition: 'border-color var(--transition)',
};

const labelStyle: React.CSSProperties = {
    display: 'block',
    fontSize: '13px',
    fontWeight: 600,
    color: 'var(--text-secondary)',
    marginBottom: '6px',
    fontFamily: 'var(--font-sans)',
};

const errorStyle: React.CSSProperties = {
    fontSize: '12px',
    color: 'var(--accent-error)',
    marginTop: '4px',
    fontFamily: 'var(--font-sans)',
};

export default function DocumentTemplateForm({
    formData,
    onChange,
    errors,
    isEditing = false,
}: DocumentTemplateFormProps): React.JSX.Element {
    const fileInputRef = React.useRef<HTMLInputElement>(null);
    const [fileName, setFileName] = React.useState<string | null>(null);

    function handleFileChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const file = event.target.files?.[0] ?? null;
        onChange('template_path', file);
        setFileName(file?.name ?? null);
    }

    return (
        <div className="flex flex-col gap-5">
            {/* Template Name */}
            <div>
                <label htmlFor="template_name" style={labelStyle}>
                    Template Name <span style={{ color: 'var(--accent-error)' }}>*</span>
                </label>
                <input
                    id="template_name"
                    type="text"
                    value={formData.template_name}
                    onChange={(e) => onChange('template_name', e.target.value)}
                    placeholder="Enter template name"
                    style={{
                        ...fieldStyle,
                        borderColor: errors.template_name
                            ? 'var(--input-border-error)'
                            : 'var(--input-border)',
                    }}
                />
                {errors.template_name ? (
                    <p style={errorStyle}>{errors.template_name}</p>
                ) : null}
            </div>

            {/* Template Type */}
            <div>
                <label htmlFor="template_type" style={labelStyle}>
                    Template Type <span style={{ color: 'var(--accent-error)' }}>*</span>
                </label>
                <select
                    id="template_type"
                    value={formData.template_type}
                    onChange={(e) => onChange('template_type', e.target.value)}
                    style={{
                        ...fieldStyle,
                        colorScheme: 'dark',
                        borderColor: errors.template_type
                            ? 'var(--input-border-error)'
                            : 'var(--input-border)',
                    }}
                >
                    <option value="">Select type</option>
                    {DOCUMENT_TEMPLATE_TYPES.map((t) => (
                        <option key={t.value} value={t.value}>
                            {t.label}
                        </option>
                    ))}
                </select>
                {errors.template_type ? (
                    <p style={errorStyle}>{errors.template_type}</p>
                ) : null}
            </div>

            {/* Description */}
            <div>
                <label htmlFor="template_description" style={labelStyle}>
                    Description
                </label>
                <textarea
                    id="template_description"
                    value={formData.template_description}
                    onChange={(e) => onChange('template_description', e.target.value)}
                    placeholder="Optional description"
                    rows={4}
                    style={{
                        ...fieldStyle,
                        height: 'auto',
                        padding: '10px 12px',
                        resize: 'vertical',
                        borderColor: errors.template_description
                            ? 'var(--input-border-error)'
                            : 'var(--input-border)',
                    }}
                />
                {errors.template_description ? (
                    <p style={errorStyle}>{errors.template_description}</p>
                ) : null}
            </div>

            {/* File Upload */}
            <div>
                <label style={labelStyle}>
                    Template File{' '}
                    {!isEditing ? (
                        <span style={{ color: 'var(--accent-error)' }}>*</span>
                    ) : null}
                    {isEditing ? (
                        <span
                            style={{
                                fontSize: '11px',
                                fontWeight: 400,
                                color: 'var(--text-muted)',
                                marginLeft: '6px',
                            }}
                        >
                            (leave empty to keep current file)
                        </span>
                    ) : null}
                </label>
                <button
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    className="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors"
                    style={{
                        border: `1px dashed ${
                            errors.template_path
                                ? 'var(--accent-error)'
                                : 'var(--border-default)'
                        }`,
                        background: 'var(--bg-surface)',
                        color: 'var(--text-secondary)',
                        fontFamily: 'var(--font-sans)',
                        cursor: 'pointer',
                    }}
                >
                    <Upload
                        size={16}
                        style={{ color: 'var(--accent-primary)', flexShrink: 0 }}
                    />
                    <span
                        style={{
                            color: fileName ? 'var(--text-primary)' : 'var(--text-muted)',
                        }}
                    >
                        {fileName ??
                            'Click to upload PDF, DOC, DOCX, XLS or XLSX (max 20 MB)'}
                    </span>
                </button>
                <input
                    ref={fileInputRef}
                    type="file"
                    accept=".pdf,.doc,.docx,.xls,.xlsx"
                    onChange={handleFileChange}
                    className="sr-only"
                    aria-label="Upload template file"
                />
                {errors.template_path ? (
                    <p style={errorStyle}>{errors.template_path}</p>
                ) : null}
            </div>
        </div>
    );
}
