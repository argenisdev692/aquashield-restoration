import * as React from "react";
import { Upload } from "lucide-react";
import type { DocumentTemplateAllianceFormData } from "@/modules/document-template-alliances/types";

const TEMPLATE_TYPES = [
    { value: "contract", label: "Contract" },
    { value: "agreement", label: "Agreement" },
    { value: "addendum", label: "Addendum" },
    { value: "proposal", label: "Proposal" },
    { value: "invoice", label: "Invoice" },
    { value: "other", label: "Other" },
];

interface DocumentTemplateAllianceFormProps {
    formData: DocumentTemplateAllianceFormData;
    onChange: (field: keyof DocumentTemplateAllianceFormData, value: string | File | null) => void;
    errors: Partial<Record<keyof DocumentTemplateAllianceFormData, string>>;
    isEditing?: boolean;
}

export default function DocumentTemplateAllianceForm({
    formData,
    onChange,
    errors,
    isEditing = false,
}: DocumentTemplateAllianceFormProps): React.JSX.Element {
    const fileInputRef = React.useRef<HTMLInputElement>(null);
    const [fileName, setFileName] = React.useState<string | null>(null);

    function handleFileChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const file = event.target.files?.[0] ?? null;
        onChange("template_path_alliance", file);
        setFileName(file?.name ?? null);
    }

    const fieldStyle: React.CSSProperties = {
        width: "100%",
        height: "var(--input-height)",
        padding: "0 var(--input-padding-x)",
        fontSize: "var(--input-font-size)",
        background: "var(--input-bg)",
        border: "1px solid var(--input-border)",
        borderRadius: "var(--input-radius)",
        color: "var(--input-text)",
        fontFamily: "var(--font-sans)",
        outline: "none",
        transition: "border-color var(--transition)",
    };

    const labelStyle: React.CSSProperties = {
        display: "block",
        fontSize: "13px",
        fontWeight: 600,
        color: "var(--text-secondary)",
        marginBottom: "6px",
        fontFamily: "var(--font-sans)",
    };

    const errorStyle: React.CSSProperties = {
        fontSize: "12px",
        color: "var(--accent-error)",
        marginTop: "4px",
        fontFamily: "var(--font-sans)",
    };

    return (
        <div className="flex flex-col gap-5">
            {/* Template Name */}
            <div>
                <label htmlFor="template_name_alliance" style={labelStyle}>
                    Template Name <span style={{ color: "var(--accent-error)" }}>*</span>
                </label>
                <input
                    id="template_name_alliance"
                    type="text"
                    value={formData.template_name_alliance}
                    onChange={(e) => onChange("template_name_alliance", e.target.value)}
                    placeholder="Enter template name"
                    style={{
                        ...fieldStyle,
                        borderColor: errors.template_name_alliance ? "var(--input-border-error)" : "var(--input-border)",
                    }}
                />
                {errors.template_name_alliance ? (
                    <p style={errorStyle}>{errors.template_name_alliance}</p>
                ) : null}
            </div>

            {/* Template Type */}
            <div>
                <label htmlFor="template_type_alliance" style={labelStyle}>
                    Template Type <span style={{ color: "var(--accent-error)" }}>*</span>
                </label>
                <select
                    id="template_type_alliance"
                    value={formData.template_type_alliance}
                    onChange={(e) => onChange("template_type_alliance", e.target.value)}
                    style={{
                        ...fieldStyle,
                        borderColor: errors.template_type_alliance ? "var(--input-border-error)" : "var(--input-border)",
                    }}
                >
                    <option value="">Select type</option>
                    {TEMPLATE_TYPES.map((t) => (
                        <option key={t.value} value={t.value}>
                            {t.label}
                        </option>
                    ))}
                </select>
                {errors.template_type_alliance ? (
                    <p style={errorStyle}>{errors.template_type_alliance}</p>
                ) : null}
            </div>

            {/* Alliance Company ID */}
            <div>
                <label htmlFor="alliance_company_id" style={labelStyle}>
                    Alliance Company ID <span style={{ color: "var(--accent-error)" }}>*</span>
                </label>
                <input
                    id="alliance_company_id"
                    type="number"
                    min={1}
                    value={formData.alliance_company_id}
                    onChange={(e) => onChange("alliance_company_id", e.target.value)}
                    placeholder="Alliance company ID"
                    style={{
                        ...fieldStyle,
                        borderColor: errors.alliance_company_id ? "var(--input-border-error)" : "var(--input-border)",
                    }}
                />
                {errors.alliance_company_id ? (
                    <p style={errorStyle}>{errors.alliance_company_id}</p>
                ) : null}
            </div>

            {/* Description */}
            <div>
                <label htmlFor="template_description_alliance" style={labelStyle}>
                    Description
                </label>
                <textarea
                    id="template_description_alliance"
                    value={formData.template_description_alliance}
                    onChange={(e) => onChange("template_description_alliance", e.target.value)}
                    placeholder="Optional description"
                    rows={4}
                    style={{
                        ...fieldStyle,
                        height: "auto",
                        padding: "10px var(--input-padding-x)",
                        resize: "vertical",
                        borderColor: errors.template_description_alliance ? "var(--input-border-error)" : "var(--input-border)",
                    }}
                />
                {errors.template_description_alliance ? (
                    <p style={errorStyle}>{errors.template_description_alliance}</p>
                ) : null}
            </div>

            {/* File Upload */}
            <div>
                <label style={labelStyle}>
                    Template File {!isEditing ? <span style={{ color: "var(--accent-error)" }}>*</span> : null}
                    {isEditing ? (
                        <span style={{ fontSize: "11px", fontWeight: 400, color: "var(--text-muted)", marginLeft: "6px" }}>
                            (leave empty to keep current file)
                        </span>
                    ) : null}
                </label>
                <button
                    type="button"
                    onClick={() => fileInputRef.current?.click()}
                    className="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm transition-colors"
                    style={{
                        border: `1px dashed ${errors.template_path_alliance ? "var(--accent-error)" : "var(--border-default)"}`,
                        background: "var(--bg-surface)",
                        color: "var(--text-secondary)",
                        fontFamily: "var(--font-sans)",
                        cursor: "pointer",
                    }}
                >
                    <Upload size={16} style={{ color: "var(--accent-primary)", flexShrink: 0 }} />
                    <span style={{ color: fileName ? "var(--text-primary)" : "var(--text-muted)" }}>
                        {fileName ?? "Click to upload PDF, DOC, DOCX, XLS or XLSX (max 20 MB)"}
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
                {errors.template_path_alliance ? (
                    <p style={errorStyle}>{errors.template_path_alliance}</p>
                ) : null}
            </div>
        </div>
    );
}
