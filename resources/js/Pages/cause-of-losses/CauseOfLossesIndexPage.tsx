import * as React from "react";
import { Head, Link, useRemember } from "@inertiajs/react";
import type { RowSelectionState } from "@tanstack/react-table";
import { ChevronLeft, ChevronRight, Plus, Search } from "lucide-react";
import { DataTableBulkActions } from "@/shadcn/DataTableBulkActions";
import { DeleteConfirmModal } from "@/shadcn/DeleteConfirmModal";
import { RestoreConfirmModal } from "@/shadcn/RestoreConfirmModal";
import { DataTableDateRangeFilter } from "@/common/data-table/DataTableDateRangeFilter";
import {
    useBulkDeleteCauseOfLosses,
    useDeleteCauseOfLoss,
    useRestoreCauseOfLoss,
} from "@/modules/cause-of-losses/hooks/useCauseOfLossMutations";
import { useCauseOfLosses } from "@/modules/cause-of-losses/hooks/useCauseOfLosses";
import type { CauseOfLossFilters } from "@/modules/cause-of-losses/types";
import AppLayout from "@/pages/layouts/AppLayout";
import CauseOfLossesTable from "./components/CauseOfLossesTable";

export default function CauseOfLossesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<CauseOfLossFilters>(
        {
            page: 1,
            per_page: 15,
        },
        "cause-of-losses-filters",
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

    const { data, isPending } = useCauseOfLosses(filters);
    const deleteCauseOfLoss = useDeleteCauseOfLoss();
    const restoreCauseOfLoss = useRestoreCauseOfLoss();
    const bulkDeleteCauseOfLosses = useBulkDeleteCauseOfLosses();

    const causeOfLosses = data?.data ?? [];
    const meta = data?.meta ?? {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    };
    const selectedCount = Object.values(rowSelection).filter(Boolean).length;

    function handleSearchChange(event: React.ChangeEvent<HTMLInputElement>): void {
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
        if (pendingDelete === null) {
            return;
        }

        await deleteCauseOfLoss.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) {
            return;
        }

        await restoreCauseOfLoss.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);

        if (selectedUuids.length === 0) {
            return;
        }

        await bulkDeleteCauseOfLosses.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function goToPage(page: number): void {
        setFilters((current) => ({
            ...current,
            page,
        }));
    }

    return (
        <>
            <Head title="Cause of Losses" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: "var(--text-primary)" }}
                            >
                                Cause of Losses
                            </h1>
                            <p
                                className="text-sm font-medium"
                                style={{ color: "var(--text-muted)" }}
                            >
                                {meta.total} {meta.total === 1 ? "record" : "records"} found
                            </p>
                        </div>

                        <Link
                            href="/cause-of-losses/create"
                            className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                        >
                            <Plus size={16} />
                            <span>New cause of loss</span>
                        </Link>
                    </div>

                    <div
                        className="card flex flex-col gap-4"
                        style={{ fontFamily: "var(--font-sans)" }}
                    >
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <div
                                className="flex flex-1 items-center gap-3 rounded-xl px-4 py-3"
                                style={{
                                    border: "1px solid var(--border-default)",
                                    background: "var(--bg-surface)",
                                }}
                            >
                                <Search size={16} style={{ color: "var(--text-muted)" }} />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={handleSearchChange}
                                    placeholder="Search cause of losses..."
                                    className="w-full bg-transparent text-sm outline-none"
                                    style={{
                                        color: "var(--text-primary)",
                                        fontFamily: "var(--font-sans)",
                                    }}
                                />
                            </div>

                            <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                                <select
                                    value={filters.status ?? ""}
                                    onChange={(event) =>
                                        setFilters((current) => ({
                                            ...current,
                                            status: event.target.value === "" ? undefined : event.target.value as "active" | "deleted",
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

                                <select
                                    value={filters.severity ?? ""}
                                    onChange={(event) =>
                                        setFilters((current) => ({
                                            ...current,
                                            severity: event.target.value === "" ? undefined : event.target.value as "low" | "medium" | "high",
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
                                    <option value="">All severities</option>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>

                                <DataTableDateRangeFilter
                                    dateFrom={filters.date_from}
                                    dateTo={filters.date_to}
                                    onChange={(range) =>
                                        setFilters((current) => ({
                                            ...current,
                                            date_from: range.dateFrom,
                                            date_to: range.dateTo,
                                            page: 1,
                                        }))
                                    }
                                />
                            </div>
                        </div>
                    </div>

                    <DataTableBulkActions
                        count={selectedCount}
                        onDelete={handleBulkDelete}
                        isDeleting={bulkDeleteCauseOfLosses.isPending}
                    />

                    <div className="card overflow-hidden p-0">
                        <CauseOfLossesTable
                            data={causeOfLosses}
                            isPending={isPending}
                            onDeleteClick={(uuid, name) => setPendingDelete({ uuid, name })}
                            onRestoreClick={(uuid, name) => setPendingRestore({ uuid, name })}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                        />

                        {meta.last_page > 1 ? (
                            <div
                                className="flex items-center justify-between gap-4 px-6 py-4"
                                style={{ borderTop: "1px solid var(--border-subtle)" }}
                            >
                                <span
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: "var(--text-disabled)" }}
                                >
                                    Page {meta.current_page} / {meta.last_page}
                                </span>

                                <div className="flex items-center gap-2">
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
                </div>

                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.name ?? ""}
                    onConfirm={() => {
                        void handleConfirmDelete();
                    }}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteCauseOfLoss.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="cause of loss"
                    entityName={pendingRestore?.name}
                    onConfirm={() => {
                        void handleConfirmRestore();
                    }}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreCauseOfLoss.isPending}
                />
            </AppLayout>
        </>
    );
}
