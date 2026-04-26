import * as React from "react";
import { Head, Link, useRemember } from "@inertiajs/react";
import type { RowSelectionState } from "@tanstack/react-table";
import { ChevronLeft, ChevronRight, Mail, Plus } from "lucide-react";
import { ExportButton } from "@/common/export/ExportButton";
import { CrudFilterBar } from "@/common/filters/CrudFilterBar";
import { PermissionGuard } from "@/modules/auth/components/PermissionGuard";
import { useAuthContext } from "@/modules/auth/context/AuthContext";
import { useBulkDeleteEmailData, useDeleteEmailData, useRestoreEmailData } from "@/modules/email-data/hooks/useEmailDataMutations";
import { useEmailData } from "@/modules/email-data/hooks/useEmailData";
import type { EmailDataFilters } from "@/modules/email-data/types";
import AppLayout from "@/pages/layouts/AppLayout";
import { DataTableBulkActions } from "@/shadcn/DataTableBulkActions";
import { DeleteConfirmModal } from "@/shadcn/DeleteConfirmModal";
import { RestoreConfirmModal } from "@/shadcn/RestoreConfirmModal";
import EmailDataTable from "./components/EmailDataTable";

export default function EmailDataIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<EmailDataFilters>(
        {
            page: 1,
            per_page: 15,
        },
        "email-data-filters",
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? "");
    const [typeSearch, setTypeSearch] = React.useState<string>(filters.type ?? "");
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; label: string } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; label: string } | null>(null);
    const [, startTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { permissions, isSuperAdmin } = useAuthContext();
    const canDelete = isSuperAdmin || permissions.includes("DELETE_EMAIL_DATA");

    const { data, isPending, isError } = useEmailData(filters);
    const deleteEmailData = useDeleteEmailData();
    const restoreEmailData = useRestoreEmailData();
    const bulkDeleteEmailData = useBulkDeleteEmailData();

    const records = data?.data ?? [];
    const meta = data?.meta ?? {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    };
    const selectedCount = Object.values(rowSelection).filter(Boolean).length;

    function handleSearchChange(value: string): void {
        setSearch(value);

        startTransition(() => {
            setFilters((current) => ({
                ...current,
                search: value === "" ? undefined : value,
                page: 1,
            }));
        });
    }

    function handleTypeChange(value: string): void {
        setTypeSearch(value);

        startTransition(() => {
            setFilters((current) => ({
                ...current,
                type: value === "" ? undefined : value,
                page: 1,
            }));
        });
    }

    async function handleConfirmDelete(): Promise<void> {
        if (pendingDelete === null) {
            return;
        }

        await deleteEmailData.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) {
            return;
        }

        await restoreEmailData.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);

        if (selectedUuids.length === 0) {
            return;
        }

        await bulkDeleteEmailData.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function handleExport(format: "excel" | "pdf"): void {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append("search", filters.search);
            if (filters.type) params.append("type", filters.type);
            if (filters.status) params.append("status", filters.status);
            if (filters.date_from) params.append("date_from", filters.date_from);
            if (filters.date_to) params.append("date_to", filters.date_to);
            params.append("format", format);
            window.open(`/email-data/data/admin/export?${params.toString()}`, "_blank");
        });
    }

    function goToPage(page: number): void {
        setFilters((current) => ({
            ...current,
            page,
        }));
    }

    const visiblePages = React.useMemo(() => {
        const start = Math.max(1, meta.current_page - 2);
        const end = Math.min(meta.last_page, meta.current_page + 2);

        return Array.from({ length: end - start + 1 }, (_, index) => start + index);
    }, [meta.current_page, meta.last_page]);

    return (
        <>
            <Head title="Email Data" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: "var(--text-primary)" }}
                            >
                                Email Data
                            </h1>
                            <p
                                className="text-sm font-medium"
                                style={{ color: "var(--text-muted)" }}
                            >
                                Manage operational inboxes and contact emails — <span style={{ color: "var(--accent-primary)" }}>{meta.total} {meta.total === 1 ? "record" : "records"}</span>
                            </p>
                        </div>

                        <PermissionGuard permissions={["CREATE_EMAIL_DATA"]}>
                            <Link
                                href="/email-data/create"
                                className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                <Plus size={16} />
                                <span>New email data</span>
                            </Link>
                        </PermissionGuard>
                    </div>

                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search email, phone, type or description..."
                        searchAriaLabel="Search email data"
                        statusValue={filters.status ?? ""}
                        onStatusChange={(value) => {
                            startTransition(() => {
                                setFilters((current) => ({
                                    ...current,
                                    status: value === "" ? undefined : value as "active" | "deleted",
                                    page: 1,
                                }));
                            });
                        }}
                        dateFrom={filters.date_from}
                        dateTo={filters.date_to}
                        onDateRangeChange={(range) => {
                            startTransition(() => {
                                setFilters((current) => ({
                                    ...current,
                                    date_from: range.dateFrom,
                                    date_to: range.dateTo,
                                    page: 1,
                                }));
                            });
                        }}
                        actions={<ExportButton onExport={handleExport} isExporting={isPendingExport} />}
                    >
                        <div
                            className="flex h-10 min-w-44 items-center gap-3 rounded-lg border px-3"
                            style={{
                                borderColor: "var(--border-default)",
                                background: "var(--bg-surface)",
                                color: "var(--text-primary)",
                                fontFamily: "var(--font-sans)",
                            }}
                        >
                            <Mail size={16} style={{ color: "var(--text-secondary)", flexShrink: 0 }} />
                            <input
                                type="text"
                                value={typeSearch}
                                onChange={(event) => handleTypeChange(event.target.value)}
                                placeholder="Filter by type"
                                aria-label="Filter email data by type"
                                className="w-full bg-transparent text-sm font-medium outline-none placeholder:text-(--text-muted)"
                                style={{ color: "var(--text-primary)", fontFamily: "var(--font-sans)" }}
                            />
                        </div>
                    </CrudFilterBar>

                    {canDelete ? (
                        <DataTableBulkActions
                            count={selectedCount}
                            onDelete={() => {
                                void handleBulkDelete();
                            }}
                            isDeleting={bulkDeleteEmailData.isPending}
                        />
                    ) : null}

                    <div className="card overflow-hidden p-0">
                        <EmailDataTable
                            data={records}
                            isPending={isPending}
                            isError={isError}
                            onDeleteClick={(uuid, label) => setPendingDelete({ uuid, label })}
                            onRestoreClick={(uuid, label) => setPendingRestore({ uuid, label })}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                        />

                        {meta.last_page > 1 ? (
                            <div
                                className="flex flex-col gap-4 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                                style={{ borderTop: "1px solid var(--border-subtle)" }}
                            >
                                <span
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: "var(--text-disabled)" }}
                                >
                                    Page {meta.current_page} / {meta.last_page} · {meta.total} total
                                </span>

                                <div className="flex items-center gap-2 self-end sm:self-auto">
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page - 1)}
                                        disabled={meta.current_page === 1}
                                        className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-50"
                                        aria-label="Previous page"
                                    >
                                        <ChevronLeft size={16} />
                                    </button>

                                    {visiblePages.map((page) => (
                                        <button
                                            key={page}
                                            type="button"
                                            onClick={() => goToPage(page)}
                                            className="inline-flex h-9 min-w-9 items-center justify-center rounded-lg px-3 text-sm font-semibold transition-all"
                                            style={{
                                                background: page === meta.current_page ? "var(--accent-primary)" : "transparent",
                                                color: page === meta.current_page ? "var(--text-primary)" : "var(--text-secondary)",
                                                border: page === meta.current_page ? "1px solid var(--accent-primary)" : "1px solid var(--border-default)",
                                                fontFamily: "var(--font-sans)",
                                            }}
                                            aria-label={`Go to page ${page}`}
                                            aria-current={page === meta.current_page ? "page" : undefined}
                                        >
                                            {page}
                                        </button>
                                    ))}

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
                </div>

                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.label ?? ""}
                    onConfirm={() => {
                        void handleConfirmDelete();
                    }}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteEmailData.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="email data record"
                    entityName={pendingRestore?.label}
                    onConfirm={() => {
                        void handleConfirmRestore();
                    }}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreEmailData.isPending}
                />
            </AppLayout>
        </>
    );
}
