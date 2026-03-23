import * as React from 'react';
import { Upload } from 'lucide-react';
import { ADJUSTER_TEMPLATE_TYPES } from '@/modules/document-template-adjusters/types';
import type { DocumentTemplateAdjusterFormData } from '@/modules/document-template-adjusters/types';

interface DocumentTemplateAdjusterFormProps {
    formData: DocumentTemplateAdjusterFormData;
    onChange: (field: keyof DocumentTemplateAdjusterFormData, value: string | File | null) => void;
    errors: Partial<Record<keyof DocumentTemplateAdjusterFormData, string>>;
    isEditing?: boolean;
}

export default function DocumentTemplateAdjusterForm({
    formData,
    onChange,
    errors,
    isEditing = false,
}: DocumentTemplateAdjusterFormProps): React.JSX.Element {
    const fileInputRef = React.useRef<HTMLInputElement>(null);
    const [fileName, setFileName] = React.useState<string | null>(null);

    function handleFileChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const file = event.target.files?.[0] ?? null;
        onChange('template_path_adjuster', file);
        setFileName(file?.name ?? null);
    }

    const fieldStyle: React.CSSProperties = {
        width: '100%',
        height: 'var(--input-height)',
        padding: '0 var(--input-padding-x)',
        fontSize: 'var(--input-font-size)',
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

    return (
        <div className="flex flex-col gap-5">

            {/* Template Type */}
            <div>
                <label htmlFor="template_type_adjuster" style={labelStyle}>
                    Template Type <span style={{ color: 'var(--accent-error)' }}>*</span>
                </label>
                <select
                    id="template_type_adjuster"
                    value={formData.template_type_adjuster}
                    onChange={(e) => onChange('template_type_adjuster', e.target.value)}
                    style={{
                        ...fieldStyle,
                        borderColor: errors.template_type_adjuster ? 'var(--input-border-error)' : 'var(--input-border)',
                        colorScheme: 'dark',
                    }}
                >
                    <option value="">Select type…</option>
                    {ADJUSTER_TEMPLATE_TYPES.map((t) => (
                        <option key={t.value} value={t.value}>{t.label}</option>
                    ))}
                </select>
                {errors.template_type_adjuster ? (
                    <p style={errorStyle}>{errors.template_type_adjuster}</p>
                ) : null}
            </div>

            {/* Public Adjuster ID */}
            <div>
                <label htmlFor="public_adjuster_id" style={labelStyle}>
                    Public Adjuster (User ID) <span style={{ color: 'var(--accent-error)' }}>*</span>
                </label>
                <input
                    id="public_adjuster_id"
                    type="number"
                    min={1}
                    value={formData.public_adjuster_id}
                    onChange={(e) => onChange('public_adjuster_id', e.target.value)}
                    placeholder="Enter user ID of the public adjuster"
                    style={{
                        ...fieldStyle,
                        borderColor: errors.public_adjuster_id ? 'var(--input-border-error)' : 'var(--input-border)',
                    }}
                />
                {errors.public_adjuster_id ? (
                    <p style={errorStyle}>{errors.public_adjuster_id}</p>
                ) : null}
            </div>

            {/* Description */}
            <div>
                <label htmlFor="template_description_adjuster" style={labelStyle}>
                    Description
                </label>
                <textarea
                    id="template_description_adjuster"
                    value={formData.template_description_adjuster}
                    onChange={(e) => onChange('template_description_adjuster', e.target.value)}
                    placeholder="Optional description for this adjuster template…"
                    rows={4}
                    style={{
                        ...fieldStyle,
                        height: 'auto',
                        padding: '10px var(--input-padding-x)',
                        resize: 'vertical',
                        borderColor: errors.template_description_adjuster ? 'var(--input-border-error)' : 'var(--input-border)',
                    }}
                />
                {errors.template_description_adjuster ? (
                    <p style={errorStyle}>{errors.template_description_adjuster}</p>
                ) : null}
            </div>

            {/* File Upload */}
            <div>
                <label style={labelStyle}>
                    Template File{' '}
                    {!isEditing ? <span style={{ color: 'var(--accent-error)' }}>*</span> : null}
                    {isEditing ? (
                        <span style={{ fontSize: '11px', fontWeight: 400, color: 'var(--text-muted)', marginLeft: '6px' }}>
                            (leave empty to keep current file)
                        </span>
                    ) : null}
                </label>
                <button
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    className="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors"
                    style={{
                        border: `1px dashed ${errors.template_path_adjuster ? 'var(--accent-error)' : 'var(--border-default)'}`,
                        background: 'var(--bg-surface)',
                        color: 'var(--text-secondary)',
                        fontFamily: 'var(--font-sans)',
                        cursor: 'pointer',
                    }}
                >
                    <Upload size={16} style={{ color: 'var(--accent-primary)', flexShrink: 0 }} />
                    <span style={{ color: fileName ? 'var(--text-primary)' : 'var(--text-muted)' }}>
                        {fileName ?? 'Click to upload DOC, DOCX or PDF (max 20 MB)'}
                    </span>
                </button>
                <input
                    ref={fileInputRef}
                    type="file"
                    accept=".doc,.docx,.pdf"
                    onChange={handleFileChange}
                    className="sr-only"
                    aria-label="Upload adjuster template file"
                />
                {errors.template_path_adjuster ? (
                    <p style={errorStyle}>{errors.template_path_adjuster}</p>
                ) : null}
            </div>
        </div>
    );
}
