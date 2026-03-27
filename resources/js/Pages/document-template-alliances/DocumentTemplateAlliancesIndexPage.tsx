import * as React from "react";
import { Head, Link, useRemember } from "@inertiajs/react";
import { ChevronLeft, ChevronRight, Plus, Search } from "lucide-react";
import { ExportButton } from "@/common/export/ExportButton";
import { DataTableDateRangeFilter } from "@/common/data-table/DataTableDateRangeFilter";
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

    function handleSearchChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const value = event.target.value;
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
                    <div
                        className="flex flex-col gap-4 rounded-3xl px-5 py-4 shadow-sm lg:flex-row lg:items-end lg:justify-between"
                        style={{ background: "var(--bg-card)", border: "1px solid var(--border-default)", fontFamily: "var(--font-sans)" }}
                    >
                        <div
                            className="flex flex-1 items-center gap-3 rounded-2xl px-4 py-3"
                            style={{ background: "var(--bg-surface)" }}
                        >
                            <Search size={16} style={{ color: "var(--text-muted)", flexShrink: 0 }} />
                            <input
                                type="text"
                                value={search}
                                onChange={handleSearchChange}
                                placeholder="Search templates…"
                                className="w-full bg-transparent text-sm outline-none"
                                style={{ color: "var(--text-primary)", fontFamily: "var(--font-sans)" }}
                            />
                        </div>

                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-end">
                            <DataTableDateRangeFilter
                                dateFrom={filters.date_from}
                                dateTo={filters.date_to}
                                onChange={(range) =>
                                    setFilters((prev) => ({
                                        ...prev,
                                        date_from: range.dateFrom,
                                        date_to: range.dateTo,
                                        page: 1,
                                    }))
                                }
                            />
                            <ExportButton onExport={handleExport} isExporting={isPendingExport} />
                        </div>
                    </div>

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
