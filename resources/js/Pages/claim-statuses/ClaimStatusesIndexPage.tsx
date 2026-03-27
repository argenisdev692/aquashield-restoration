import * as React from "react";
import { Head, Link, useRemember } from "@inertiajs/react";
import type { RowSelectionState } from "@tanstack/react-table";
import { ChevronLeft, ChevronRight, Plus, Search } from "lucide-react";
import { DataTableBulkActions } from "@/shadcn/DataTableBulkActions";
import { DeleteConfirmModal } from "@/shadcn/DeleteConfirmModal";
import { RestoreConfirmModal } from "@/shadcn/RestoreConfirmModal";
import { DataTableDateRangeFilter } from "@/common/data-table/DataTableDateRangeFilter";
import { ExportButton } from "@/common/export/ExportButton";
import {
    useBulkDeleteClaimStatuses,
    useDeleteClaimStatus,
    useRestoreClaimStatus,
} from "@/modules/claim-statuses/hooks/useClaimStatusMutations";
import { useClaimStatuses } from "@/modules/claim-statuses/hooks/useClaimStatuses";
import type { ClaimStatusFilters } from "@/modules/claim-statuses/types";
import AppLayout from "@/pages/layouts/AppLayout";
import ClaimStatusesTable from "./components/ClaimStatusesTable";

export default function ClaimStatusesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<ClaimStatusFilters>(
        { page: 1, per_page: 15 },
        "claim-statuses-filters",
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? "");
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{
        uuid: string;
        name: string;
    } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{
        uuid: string;
        name: string;
    } | null>(null);
    const [, startTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending } = useClaimStatuses(filters);
    const deleteClaimStatus = useDeleteClaimStatus();
    const restoreClaimStatus = useRestoreClaimStatus();
    const bulkDeleteClaimStatuses = useBulkDeleteClaimStatuses();

    const claimStatuses = data?.data ?? [];
    const meta = data?.meta ?? {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    };
    const selectedCount = Object.values(rowSelection).filter(Boolean).length;

    function handleSearchChange(
        event: React.ChangeEvent<HTMLInputElement>,
    ): void {
        const value = event.target.value;
        setSearch(value);

        startTransition(() => {
            setFilters((current) => ({
                ...current,
                search: value === "" ? undefined : value,
                page: 1,
            }));
        });
    }

    async function handleConfirmDelete(): Promise<void> {
        if (pendingDelete === null) return;
        await deleteClaimStatus.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) return;
        await restoreClaimStatus.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);

        if (selectedUuids.length === 0) return;

        await bulkDeleteClaimStatuses.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function handleExport(format: "excel" | "pdf"): void {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append("search", filters.search);
            if (filters.status) params.append("status", filters.status);
            if (filters.date_from) params.append("date_from", filters.date_from);
            if (filters.date_to) params.append("date_to", filters.date_to);
            params.append("format", format);
            window.open(
                `/claim-statuses/data/admin/export?${params.toString()}`,
                "_blank",
            );
        });
    }

    function goToPage(page: number): void {
        setFilters((current) => ({ ...current, page }));
    }

    return (
        <>
            <Head title="Claim Statuses" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <h1
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: "var(--text-primary)" }}
                            >
                                Claim Statuses
                            </h1>
                            <p
                                className="text-sm font-medium"
                                style={{ color: "var(--text-muted)" }}
                            >
                                {meta.total}{" "}
                                {meta.total === 1 ? "record" : "records"} found
                            </p>
                        </div>

                        <Link
                            href="/claim-statuses/create"
                            className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                        >
                            <Plus size={16} />
                            <span>New claim status</span>
                        </Link>
                    </div>

                    <div
                        className="card flex flex-col gap-4"
                        style={{ fontFamily: "var(--font-sans)" }}
                    >
                        <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div
                                className="flex flex-1 items-center gap-3 rounded-2xl px-4 py-3"
                                style={{ background: "var(--bg-surface)" }}
                            >
                                <Search
                                    size={16}
                                    style={{ color: "var(--text-muted)" }}
                                />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={handleSearchChange}
                                    placeholder="Search claim statuses..."
                                    className="w-full bg-transparent text-sm outline-none"
                                    style={{
                                        color: "var(--text-primary)",
                                        fontFamily: "var(--font-sans)",
                                    }}
                                />
                            </div>

                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-end">
                                <select
                                    value={filters.status ?? ""}
                                    onChange={(event) =>
                                        setFilters((current) => ({
                                            ...current,
                                            status:
                                                event.target.value === ""
                                                    ? undefined
                                                    : (event.target.value as
                                                          | "active"
                                                          | "deleted"),
                                            page: 1,
                                        }))
                                    }
                                    className="rounded-xl px-4 py-3 text-sm outline-none"
                                    style={{
                                        border: "1px solid var(--border-default)",
                                        background: "var(--bg-surface)",
                                        color: "var(--text-primary)",
                                        fontFamily: "var(--font-sans)",
                                    }}
                                >
                                    <option value="">All status</option>
                                    <option value="active">Active</option>
                                    <option value="deleted">Deleted</option>
                                </select>

                                <DataTableDateRangeFilter
                                    dateFrom={filters.date_from}
                                    dateTo={filters.date_to}
                                    onChange={(range) =>
                                        setFilters((current) => ({
                                            ...current,
                                            ...range,
                                            page: 1,
                                        }))
                                    }
                                />

                                <ExportButton
                                    onExport={handleExport}
                                    isExporting={isPendingExport}
                                />
                            </div>
                        </div>

                        <DataTableBulkActions
                            count={selectedCount}
                            onDelete={() => { void handleBulkDelete(); }}
                            isDeleting={bulkDeleteClaimStatuses.isPending}
                        />

                        <ClaimStatusesTable
                            data={claimStatuses}
                            isPending={isPending}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                            onDeleteClick={(uuid, name) =>
                                setPendingDelete({ uuid, name })
                            }
                            onRestoreClick={(uuid, name) =>
                                setPendingRestore({ uuid, name })
                            }
                        />

                        {meta.last_page > 1 && (
                            <div className="flex items-center justify-between border-t pt-4"
                                style={{ borderColor: "var(--border-subtle)" }}>
                                <p className="text-xs" style={{ color: "var(--text-disabled)" }}>
                                    Page {meta.current_page} of {meta.last_page}
                                </p>
                                <div className="flex items-center gap-1">
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page - 1)}
                                        disabled={meta.current_page <= 1}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0 disabled:opacity-40"
                                        aria-label="Previous page"
                                    >
                                        <ChevronLeft size={14} />
                                    </button>
                                    {Array.from(
                                        { length: Math.min(5, meta.last_page) },
                                        (_, i) => {
                                            const start = Math.max(
                                                1,
                                                Math.min(
                                                    meta.current_page - 2,
                                                    meta.last_page - 4,
                                                ),
                                            );
                                            return start + i;
                                        },
                                    ).map((page) => (
                                        <button
                                            key={page}
                                            type="button"
                                            onClick={() => goToPage(page)}
                                            className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-xs font-semibold transition-all"
                                            style={{
                                                background:
                                                    page === meta.current_page
                                                        ? "var(--accent-primary)"
                                                        : "transparent",
                                                color:
                                                    page === meta.current_page
                                                        ? "#ffffff"
                                                        : "var(--text-muted)",
                                                border:
                                                    page === meta.current_page
                                                        ? "none"
                                                        : "1px solid var(--border-default)",
                                            }}
                                        >
                                            {page}
                                        </button>
                                    ))}
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page + 1)}
                                        disabled={meta.current_page >= meta.last_page}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0 disabled:opacity-40"
                                        aria-label="Next page"
                                    >
                                        <ChevronRight size={14} />
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.name ?? ""}
                    onConfirm={handleConfirmDelete}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteClaimStatus.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel={pendingRestore?.name ?? ""}
                    onConfirm={() => { void handleConfirmRestore(); }}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreClaimStatus.isPending}
                />
            </AppLayout>
        </>
    );
}
