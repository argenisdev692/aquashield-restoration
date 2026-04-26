import * as React from "react";
import { Head, Link, useRemember } from "@inertiajs/react";
import type { RowSelectionState } from "@tanstack/react-table";
import { CalendarDays, ChevronLeft, ChevronRight, Plus } from "lucide-react";
import { DataTableBulkActions } from "@/shadcn/DataTableBulkActions";
import { DeleteConfirmModal } from "@/shadcn/DeleteConfirmModal";
import { RestoreConfirmModal } from "@/shadcn/RestoreConfirmModal";
import { ExportButton } from "@/common/export/ExportButton";
import { CrudFilterBar } from "@/common/filters/CrudFilterBar";
import {
    useBulkDeleteAppointments,
    useDeleteAppointment,
    useRestoreAppointment,
} from "@/modules/appointments/hooks/useAppointmentMutations";
import { useAppointments } from "@/modules/appointments/hooks/useAppointments";
import type { AppointmentFilters } from "@/modules/appointments/types";
import AppLayout from "@/pages/layouts/AppLayout";
import AppointmentsTable from "./components/AppointmentsTable";

const INSPECTION_STATUS_OPTIONS = ["Pending", "Confirmed", "Declined", "Completed"];
const STATUS_LEAD_OPTIONS = ["New", "Called", "Pending", "Declined"];

export default function AppointmentsIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<AppointmentFilters>({
        page: 1,
        per_page: 15,
    }, "appointments-filters");
    const [search, setSearch] = React.useState<string>(filters.search ?? "");
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);
    const [, startTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending } = useAppointments(filters);
    const deleteAppointment = useDeleteAppointment();
    const restoreAppointment = useRestoreAppointment();
    const bulkDeleteAppointments = useBulkDeleteAppointments();

    const appointments = data?.data ?? [];
    const meta = data?.meta ?? { current_page: 1, last_page: 1, per_page: 15, total: 0 };
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

        await deleteAppointment.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) {
            return;
        }

        await restoreAppointment.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);

        if (selectedUuids.length === 0) {
            return;
        }

        await bulkDeleteAppointments.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.status) params.append('status', filters.status);
            if (filters.inspection_status) params.append('inspection_status', filters.inspection_status);
            if (filters.status_lead) params.append('status_lead', filters.status_lead);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            params.append('format', format);
            window.open(`/appointments/data/admin/export?${params.toString()}`, '_blank');
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
            <Head title="Appointments" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 className="text-3xl font-extrabold tracking-tight" style={{ color: "var(--text-primary)" }}>
                                Appointments
                            </h1>
                            <p className="text-sm font-medium" style={{ color: "var(--text-muted)" }}>
                                {meta.total} {meta.total === 1 ? "record" : "records"} found
                            </p>
                        </div>

                        <div className="flex flex-wrap items-center gap-2">
                            <Link href="/appointments/calendar" className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold">
                                <CalendarDays size={14} />
                                <span>Calendar view</span>
                            </Link>
                            <Link href="/appointments/create" className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold">
                                <Plus size={16} />
                                <span>New appointment</span>
                            </Link>
                        </div>
                    </div>

                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search appointments..."
                        searchAriaLabel="Search appointments"
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
                                value: filters.inspection_status ?? "",
                                onChange: (value) => {
                                    startTransition(() => {
                                        setFilters((current) => ({
                                            ...current,
                                            inspection_status: value === "" ? undefined : value,
                                            page: 1,
                                        }));
                                    });
                                },
                                options: [
                                    { value: "", label: "All Inspection Status" },
                                    ...INSPECTION_STATUS_OPTIONS.map((option) => ({ value: option, label: option })),
                                ],
                                ariaLabel: "Filter by inspection status",
                                label: "Inspection",
                                minWidth: 180,
                            },
                            {
                                value: filters.status_lead ?? "",
                                onChange: (value) => {
                                    startTransition(() => {
                                        setFilters((current) => ({
                                            ...current,
                                            status_lead: value === "" ? undefined : value,
                                            page: 1,
                                        }));
                                    });
                                },
                                options: [
                                    { value: "", label: "All Lead Status" },
                                    ...STATUS_LEAD_OPTIONS.map((option) => ({ value: option, label: option })),
                                ],
                                ariaLabel: "Filter by lead status",
                                label: "Lead Status",
                                minWidth: 164,
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

                    <DataTableBulkActions count={selectedCount} onDelete={handleBulkDelete} isDeleting={bulkDeleteAppointments.isPending} />

                    <div className="card overflow-hidden p-0">
                        <AppointmentsTable
                            data={appointments}
                            isPending={isPending}
                            onDeleteClick={(uuid, name) => setPendingDelete({ uuid, name })}
                            onRestoreClick={(uuid, name) => setPendingRestore({ uuid, name })}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                        />

                        {meta.last_page > 1 ? (
                            <div className="flex items-center justify-between gap-4 px-6 py-4" style={{ borderTop: "1px solid var(--border-subtle)" }}>
                                <span className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Page {meta.current_page} / {meta.last_page}
                                </span>

                                <div className="flex items-center gap-2">
                                    <button type="button" onClick={() => goToPage(meta.current_page - 1)} disabled={meta.current_page === 1} className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-50" aria-label="Previous page">
                                        <ChevronLeft size={16} />
                                    </button>
                                    <button type="button" onClick={() => goToPage(meta.current_page + 1)} disabled={meta.current_page === meta.last_page} className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-50" aria-label="Next page">
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
                    isDeleting={deleteAppointment.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="appointment"
                    entityName={pendingRestore?.name}
                    onConfirm={() => {
                        void handleConfirmRestore();
                    }}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreAppointment.isPending}
                />
            </AppLayout>
        </>
    );
}
