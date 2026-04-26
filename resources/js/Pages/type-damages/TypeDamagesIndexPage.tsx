import * as React from "react";
import { Head, Link, useRemember } from "@inertiajs/react";
import type { RowSelectionState } from "@tanstack/react-table";
import { ChevronLeft, ChevronRight, Plus } from "lucide-react";
import { DataTableBulkActions } from "@/shadcn/DataTableBulkActions";
import { DeleteConfirmModal } from "@/shadcn/DeleteConfirmModal";
import { RestoreConfirmModal } from "@/shadcn/RestoreConfirmModal";
import { ExportButton } from "@/common/export/ExportButton";
import { CrudFilterBar } from "@/common/filters/CrudFilterBar";
import {
    useBulkDeleteTypeDamages,
    useDeleteTypeDamage,
    useRestoreTypeDamage,
} from "@/modules/type-damages/hooks/useTypeDamageMutations";
import { useTypeDamages } from "@/modules/type-damages/hooks/useTypeDamages";
import type { TypeDamageFilters } from "@/modules/type-damages/types";
import AppLayout from "@/pages/layouts/AppLayout";
import TypeDamagesTable from "./components/TypeDamagesTable";

export default function TypeDamagesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<TypeDamageFilters>(
        {
            page: 1,
            per_page: 15,
        },
        "type-damages-filters",
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

    const { data, isPending } = useTypeDamages(filters);
    const deleteTypeDamage = useDeleteTypeDamage();
    const restoreTypeDamage = useRestoreTypeDamage();
    const bulkDeleteTypeDamages = useBulkDeleteTypeDamages();

    const typeDamages = data?.data ?? [];
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

    async function handleConfirmDelete(): Promise<void> {
        if (pendingDelete === null) {
            return;
        }

        await deleteTypeDamage.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) {
            return;
        }

        await restoreTypeDamage.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);

        if (selectedUuids.length === 0) {
            return;
        }

        await bulkDeleteTypeDamages.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.status) params.append('status', filters.status);
            if (filters.severity) params.append('severity', filters.severity);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            params.append('format', format);
            window.open(`/type-damages/data/admin/export?${params.toString()}`, '_blank');
        });
    }

    function goToPage(page: number): void {
        setFilters((current) => ({
            ...current,
            page,
        }));
    }

    return (
        <>
            <Head title="Type Damages" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <h1
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: "var(--text-primary)" }}
                            >
                                Type Damages
                            </h1>
                            <p
                                className="text-sm font-medium"
                                style={{ color: "var(--text-muted)" }}
                            >
                                {meta.total} {meta.total === 1 ? "record" : "records"} found
                            </p>
                        </div>

                        <Link
                            href="/type-damages/create"
                            className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                        >
                            <Plus size={16} />
                            <span>New type damage</span>
                        </Link>
                    </div>

                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search type damages..."
                        searchAriaLabel="Search type damages"
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
                        selects={[
                            {
                                value: filters.severity ?? "",
                                onChange: (value) => {
                                    startTransition(() => {
                                        setFilters((current) => ({
                                            ...current,
                                            severity: value === "" ? undefined : value as "low" | "medium" | "high",
                                            page: 1,
                                        }));
                                    });
                                },
                                options: [
                                    { value: "", label: "All Severities" },
                                    { value: "low", label: "Low" },
                                    { value: "medium", label: "Medium" },
                                    { value: "high", label: "High" },
                                ],
                                ariaLabel: "Filter by severity",
                                label: "Severity",
                            },
                        ]}
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
                    />

                    <DataTableBulkActions
                        count={selectedCount}
                        onDelete={handleBulkDelete}
                        isDeleting={bulkDeleteTypeDamages.isPending}
                    />

                    <div className="card overflow-hidden p-0">
                        <TypeDamagesTable
                            data={typeDamages}
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
                    isDeleting={deleteTypeDamage.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="type damage"
                    entityName={pendingRestore?.name}
                    onConfirm={() => {
                        void handleConfirmRestore();
                    }}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreTypeDamage.isPending}
                />
            </AppLayout>
        </>
    );
}
