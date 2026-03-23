import * as React from "react";
import { Link } from "@inertiajs/react";
import { createColumnHelper } from "@tanstack/react-table";
import { Eye, Pencil } from "lucide-react";
import { DataTable } from "@/shadcn/data-table";
import { formatDateShort } from "@/utils/dateFormatter";
import type { DocumentTemplateAlliance } from "@/modules/document-template-alliances/types";

interface DocumentTemplateAlliancesTableProps {
    data: DocumentTemplateAlliance[];
    isPending: boolean;
}

const columnHelper = createColumnHelper<DocumentTemplateAlliance>();

export default function DocumentTemplateAlliancesTable({
    data,
    isPending,
}: DocumentTemplateAlliancesTableProps): React.JSX.Element {
    const columns = React.useMemo(
        () => [
            columnHelper.display({
                id: "template_name_alliance",
                header: "Template Name",
                cell: ({ row }) => (
                    <span className="font-semibold" style={{ color: "var(--text-primary)" }}>
                        {row.original.template_name_alliance}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "template_type_alliance",
                header: "Type",
                cell: ({ row }) => (
                    <span
                        className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                        style={{
                            color: "var(--accent-primary)",
                            background: "color-mix(in srgb, var(--accent-primary) 15%, transparent)",
                            border: "1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)",
                        }}
                    >
                        {row.original.template_type_alliance}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "alliance_company_name",
                header: "Alliance Company",
                cell: ({ row }) => (
                    <span style={{ color: "var(--text-secondary)" }}>
                        {row.original.alliance_company_name ?? "—"}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "uploaded_by_name",
                header: "Uploaded By",
                cell: ({ row }) => (
                    <span style={{ color: "var(--text-muted)" }}>
                        {row.original.uploaded_by_name ?? "—"}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "created_at",
                header: "Created",
                cell: ({ row }) => (
                    <span style={{ color: "var(--text-muted)" }}>
                        {formatDateShort(row.original.created_at)}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "actions",
                header: "Actions",
                cell: ({ row }) => {
                    const item = row.original;
                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/document-template-alliances/${item.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View document template alliance"
                                aria-label="View document template alliance"
                            >
                                <Eye size={14} />
                            </Link>
                            <Link
                                href={`/document-template-alliances/${item.uuid}/edit`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="Edit document template alliance"
                                aria-label="Edit document template alliance"
                            >
                                <Pencil size={14} />
                            </Link>
                        </div>
                    );
                },
            }),
        ],
        [],
    );

    return (
        <DataTable
            columns={columns}
            data={data}
            isLoading={isPending}
            isError={false}
            noDataMessage="No document template alliances found."
            getRowId={(row) => row.uuid}
        />
    );
}
