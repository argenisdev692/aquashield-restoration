import * as React from "react";
import { Head, Link, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { ArrowLeft, Download, ExternalLink, FileText, Pencil } from "lucide-react";
import { PermissionGuard } from "@/modules/auth/components/PermissionGuard";
import type { DocumentTemplateAlliance } from "@/modules/document-template-alliances/types";
import AppLayout from "@/pages/layouts/AppLayout";
import { formatDateShort } from "@/utils/dateFormatter";

interface ShowPageProps extends PageProps {
    documentTemplateAlliance: DocumentTemplateAlliance;
}

export default function DocumentTemplateAllianceShowPage(): React.JSX.Element {
    const { documentTemplateAlliance } = usePage<ShowPageProps>().props;
    const t = documentTemplateAlliance;

    return (
        <>
            <Head title={t.template_name_alliance} />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    {/* Header */}
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex items-center gap-3">
                            <Link
                                href="/document-template-alliances"
                                className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0"
                                aria-label="Back to list"
                            >
                                <ArrowLeft size={16} />
                            </Link>
                            <div>
                                <h1
                                    className="text-2xl font-extrabold tracking-tight"
                                    style={{ color: "var(--text-primary)" }}
                                >
                                    {t.template_name_alliance}
                                </h1>
                                <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                    Document Template Alliance
                                </p>
                            </div>
                        </div>

                        <PermissionGuard permissions={["UPDATE_DOCUMENT_TEMPLATE_ALLIANCE"]}>
                            <Link
                                href={`/document-template-alliances/${t.uuid}/edit`}
                                prefetch
                                className="btn-primary inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                <Pencil size={14} />
                                <span>Edit</span>
                            </Link>
                        </PermissionGuard>
                    </div>

                    {/* Detail card */}
                    <div className="card flex flex-col gap-6" style={{ fontFamily: "var(--font-sans)" }}>
                        <div className="flex items-center gap-3">
                            <div
                                className="flex h-11 w-11 items-center justify-center rounded-xl"
                                style={{
                                    background: "color-mix(in srgb, var(--accent-primary) 15%, transparent)",
                                    border: "1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)",
                                }}
                            >
                                <FileText size={20} style={{ color: "var(--accent-primary)" }} />
                            </div>
                            <div>
                                <p className="text-xs font-semibold uppercase tracking-widest" style={{ color: "var(--text-muted)" }}>
                                    Template Details
                                </p>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 gap-x-8 gap-y-5 sm:grid-cols-2 lg:grid-cols-3">
                            <DetailField label="Template Name" value={t.template_name_alliance} />
                            <DetailField label="Type">
                                <span
                                    className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                                    style={{
                                        color: "var(--accent-primary)",
                                        background: "color-mix(in srgb, var(--accent-primary) 15%, transparent)",
                                        border: "1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)",
                                    }}
                                >
                                    {t.template_type_alliance}
                                </span>
                            </DetailField>
                            <DetailField label="Alliance Company" value={t.alliance_company_name ?? "—"} />
                            <DetailField label="Uploaded By" value={t.uploaded_by_name ?? "—"} />
                            <DetailField label="Created" value={formatDateShort(t.created_at)} />
                            <DetailField label="Last Updated" value={formatDateShort(t.updated_at)} />
                        </div>

                        {t.template_description_alliance ? (
                            <div>
                                <p className="mb-2 text-xs font-semibold uppercase tracking-widest" style={{ color: "var(--text-muted)" }}>
                                    Description
                                </p>
                                <p className="text-sm leading-relaxed" style={{ color: "var(--text-secondary)" }}>
                                    {t.template_description_alliance}
                                </p>
                            </div>
                        ) : null}

                        {t.template_path_alliance ? (
                            <div>
                                <p className="mb-2 text-xs font-semibold uppercase tracking-widest" style={{ color: "var(--text-muted)" }}>
                                    File
                                </p>
                                <div className="flex items-center gap-3">
                                    <a
                                        href={t.template_path_alliance}
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        className="btn-ghost inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold"
                                        aria-label="Open file in new tab"
                                        title="Open file"
                                    >
                                        <ExternalLink size={14} />
                                        Open
                                    </a>
                                    <a
                                        href={t.template_path_alliance}
                                        download={t.template_name_alliance}
                                        className="btn-ghost inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold"
                                        aria-label="Download file"
                                        title="Download file"
                                        style={{ color: "var(--accent-primary)" }}
                                    >
                                        <Download size={14} />
                                        Download
                                    </a>
                                </div>
                            </div>
                        ) : null}
                    </div>
                </div>
            </AppLayout>
        </>
    );
}

function DetailField({
    label,
    value,
    children,
}: {
    label: string;
    value?: string;
    children?: React.ReactNode;
}): React.JSX.Element {
    return (
        <div>
            <p className="mb-1 text-xs font-semibold uppercase tracking-widest" style={{ color: "var(--text-muted)" }}>
                {label}
            </p>
            {children ?? (
                <p className="text-sm font-medium" style={{ color: "var(--text-primary)" }}>
                    {value ?? "—"}
                </p>
            )}
        </div>
    );
}
