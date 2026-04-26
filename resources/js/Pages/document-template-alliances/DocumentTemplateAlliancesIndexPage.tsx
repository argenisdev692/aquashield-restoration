import * as React from "react";
import { Head, Link, useRemember } from "@inertiajs/react";
import { ChevronLeft, ChevronRight, Plus } from "lucide-react";
import { ExportButton } from "@/common/export/ExportButton";
import { CrudFilterBar } from "@/common/filters/CrudFilterBar";
import { PermissionGuard } from "@/modules/auth/components/PermissionGuard";
import { useDocumentTemplateAlliances } from "@/modules/document-template-alliances/hooks/useDocumentTemplateAlliances";
import type { DocumentTemplateAllianceFilters } from "@/modules/document-template-alliances/types";
import AppLayout from "@/pages/layouts/AppLayout";
import DocumentTemplateAlliancesTable from "./components/DocumentTemplateAlliancesTable";

export default function DocumentTemplateAlliancesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<DocumentTemplateAllianceFilters>(
        { page: 1, per_page: 15 },
        "document-template-alliances-filters",
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? "");
    const [, startTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending } = useDocumentTemplateAlliances(filters);

    const items = data?.data ?? [];
    const meta = data?.meta ?? { current_page: 1, last_page: 1, per_page: 15, total: 0 };

    function handleSearchChange(value: string): void {
        setSearch(value);
        startTransition(() => {
            setFilters((prev) => ({ ...prev, search: value === "" ? undefined : value, page: 1 }));
        });
    }

    function handleExport(format: "excel" | "pdf"): void {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append("search", filters.search);
            if (filters.date_from) params.append("date_from", filters.date_from);
            if (filters.date_to) params.append("date_to", filters.date_to);
            if (filters.alliance_company_id) params.append("alliance_company_id", String(filters.alliance_company_id));
            if (filters.template_type_alliance) params.append("template_type_alliance", filters.template_type_alliance);
            params.append("format", format);
            window.open(`/document-template-alliances/data/admin/export?${params.toString()}`, "_blank");
        });
    }

    function goToPage(page: number): void {
        setFilters((prev) => ({ ...prev, page }));
    }

    return (
        <>
            <Head title="Document Template Alliances" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    {/* Header */}
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <h1
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: "var(--text-primary)" }}
                            >
                                Document Template Alliances
                            </h1>
                            <p className="text-sm font-medium" style={{ color: "var(--text-muted)" }}>
                                {meta.total} {meta.total === 1 ? "record" : "records"} found
                            </p>
                        </div>

                        <PermissionGuard permissions={["CREATE_DOCUMENT_TEMPLATE_ALLIANCE"]}>
                            <Link
                                href="/document-template-alliances/create"
                                prefetch
                                className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                <Plus size={16} />
                                <span>New Template</span>
                            </Link>
                        </PermissionGuard>
                    </div>

                    {/* Filters */}
                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search templates…"
                        searchAriaLabel="Search document template alliances"
                        dateFrom={filters.date_from}
                        dateTo={filters.date_to}
                        onDateRangeChange={(range) => {
                            startTransition(() => {
                                setFilters((prev) => ({
                                    ...prev,
                                    date_from: range.dateFrom,
                                    date_to: range.dateTo,
                                    page: 1,
                                }));
                            });
                        }}
                        actions={<ExportButton onExport={handleExport} isExporting={isPendingExport} />}
                    />

                    {/* Table */}
                    <div className="card overflow-hidden p-0">
                        <DocumentTemplateAlliancesTable
                            data={items}
                            isPending={isPending}
                        />
                    </div>

                    {/* Pagination */}
                    {meta.last_page > 1 ? (
                        <div className="flex items-center justify-between px-2">
                            <p className="text-sm" style={{ color: "var(--text-muted)", fontFamily: "var(--font-sans)" }}>
                                Page {meta.current_page} of {meta.last_page}
                            </p>
                            <div className="flex items-center gap-1">
                                <button
                                    type="button"
                                    onClick={() => goToPage(meta.current_page - 1)}
                                    disabled={meta.current_page === 1}
                                    className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-50"
                                    aria-label="Previous page"
                                >
                                    <ChevronLeft size={16} />
                                </button>
                                <button
                                    type="button"
                                    onClick={() => goToPage(meta.current_page + 1)}
                                    disabled={meta.current_page === meta.last_page}
                                    className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-50"
                                    aria-label="Next page"
                                >
                                    <ChevronRight size={16} />
                                </button>
                            </div>
                        </div>
                    ) : null}
                </div>
            </AppLayout>
        </>
    );
}
