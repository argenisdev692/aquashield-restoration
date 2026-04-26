import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, Plus } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useInsuranceCompanies } from '@/modules/insurance-companies/hooks/useInsuranceCompanies';
import { useInsuranceCompanyMutations } from '@/modules/insurance-companies/hooks/useInsuranceCompanyMutations';
import type { InsuranceCompany, InsuranceCompanyFilters } from '@/modules/insurance-companies/types';
import InsuranceCompaniesTable from './components/InsuranceCompaniesTable';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { ExportButton } from '@/common/export/ExportButton';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';

type OptimisticInsuranceCompaniesAction =
    | { type: 'delete'; uuid: string; removeFromList: boolean }
    | { type: 'bulk-delete'; uuids: string[]; removeFromList: boolean }
    | { type: 'restore'; uuid: string; removeFromList: boolean };

function mapStatusValue(value: string): InsuranceCompanyFilters['status'] | undefined {
    return value === 'all' ? undefined : value as InsuranceCompanyFilters['status'];
}

export default function InsuranceCompaniesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<InsuranceCompanyFilters>({ page: 1, per_page: 15 }, 'insurance-companies-filters');
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);
    const [, startSearchTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending, isError } = useInsuranceCompanies(filters);
    const companies = data?.data ?? [];
    const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };
    const isActiveFilter = filters.status === 'active';
    const isDeletedFilter = filters.status === 'deleted';

    const [optimisticCompanies, setOptimisticCompanies] = React.useOptimistic<InsuranceCompany[], OptimisticInsuranceCompaniesAction>(
        companies,
        (currentState, action) => {
            if (action.type === 'delete') {
                if (action.removeFromList) {
                    return currentState.filter((company) => company.uuid !== action.uuid);
                }

                return currentState.map((company) =>
                    company.uuid === action.uuid
                        ? { ...company, deleted_at: new Date().toISOString() }
                        : company,
                );
            }

            if (action.type === 'bulk-delete') {
                const uuids = new Set(action.uuids);

                if (action.removeFromList) {
                    return currentState.filter((company) => !uuids.has(company.uuid));
                }

                return currentState.map((company) =>
                    uuids.has(company.uuid)
                        ? { ...company, deleted_at: new Date().toISOString() }
                        : company,
                );
            }

            if (action.removeFromList) {
                return currentState.filter((company) => company.uuid !== action.uuid);
            }

            return currentState.map((company) =>
                company.uuid === action.uuid
                    ? { ...company, deleted_at: null }
                    : company,
            );
        },
    );

    const {
        deleteInsuranceCompany,
        restoreInsuranceCompany,
        bulkDeleteInsuranceCompanies,
    } = useInsuranceCompanyMutations();

    function handleSearchChange(value: string): void {
        setSearch(value);

        startSearchTransition(() => {
            setFilters((previous) => ({ ...previous, search: value || undefined, page: 1 }));
        });
    }

    function handleDeleteClick(uuid: string, name: string): void {
        setPendingDelete({ uuid, name });
    }

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = new URLSearchParams();

            if (filters.search) {
                params.append('search', filters.search);
            }

            if (filters.status) {
                params.append('status', filters.status);
            }

            if (filters.date_from) {
                params.append('date_from', filters.date_from);
            }

            if (filters.date_to) {
                params.append('date_to', filters.date_to);
            }

            params.append('format', format);
            window.open(`/insurance-companies/data/admin/export?${params.toString()}`, '_blank');
        });
    }

    function handleRestoreClick(uuid: string, name: string): void {
        setPendingRestore({ uuid, name });
    }

    async function handleConfirmDelete(): Promise<void> {
        if (!pendingDelete) {
            return;
        }

        const targetUuid = pendingDelete.uuid;

        React.startTransition(async () => {
            setOptimisticCompanies({ type: 'delete', uuid: targetUuid, removeFromList: isActiveFilter });

            try {
                await deleteInsuranceCompany.mutateAsync(targetUuid);
                setPendingDelete(null);
                setRowSelection((currentSelection) => {
                    const nextSelection = { ...currentSelection };
                    delete nextSelection[targetUuid];
                    return nextSelection;
                });
            } catch {
            }
        });
    }

    async function handleConfirmRestore(): Promise<void> {
        if (!pendingRestore) {
            return;
        }

        const targetUuid = pendingRestore.uuid;

        React.startTransition(async () => {
            setOptimisticCompanies({ type: 'restore', uuid: targetUuid, removeFromList: isDeletedFilter });

            try {
                await restoreInsuranceCompany.mutateAsync(targetUuid);
                setPendingRestore(null);
                setRowSelection((currentSelection) => {
                    const nextSelection = { ...currentSelection };
                    delete nextSelection[targetUuid];
                    return nextSelection;
                });
            } catch {
            }
        });
    }

    function goToPage(page: number): void {
        setFilters((previous) => ({ ...previous, page }));
    }

    const selectedUuids = React.useMemo(
        () => Object.keys(rowSelection).filter((key) => rowSelection[key]),
        [rowSelection],
    );

    const selectedActiveUuids = React.useMemo(
        () => optimisticCompanies
            .filter((company) => selectedUuids.includes(company.uuid) && company.deleted_at === null)
            .map((company) => company.uuid),
        [optimisticCompanies, selectedUuids],
    );

    function handleBulkDelete(): void {
        if (selectedActiveUuids.length === 0) {
            return;
        }

        const uuidsToDelete = [...selectedActiveUuids];

        React.startTransition(async () => {
            setOptimisticCompanies({ type: 'bulk-delete', uuids: uuidsToDelete, removeFromList: isActiveFilter });

            try {
                await bulkDeleteInsuranceCompanies.mutateAsync(uuidsToDelete);
                setRowSelection({});
            } catch {
            }
        });
    }

    const paginationPages = React.useMemo(() => {
        const start = Math.max(1, meta.currentPage - 2);
        const end = Math.min(meta.lastPage, start + 4);
        const adjustedStart = Math.max(1, end - 4);

        return Array.from({ length: end - adjustedStart + 1 }, (_, index) => adjustedStart + index);
    }, [meta.currentPage, meta.lastPage]);

    return (
        <>
            <Head title="Insurance Companies" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 className="text-3xl font-extrabold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                                Insurance Companies
                            </h1>
                            <p className="mt-1 text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                                Manage insurance carriers — <span style={{ color: 'var(--accent-primary)' }}>{meta.total} {meta.total === 1 ? 'record' : 'records'} found</span>
                            </p>
                        </div>

                        <PermissionGuard permissions={['CREATE_INSURANCE_COMPANY']}>
                            <Link
                                href="/insurance-companies/create"
                                prefetch
                                className="btn-primary flex items-center gap-2 px-4 py-2"
                            >
                                <Plus size={18} />
                                <span className="font-semibold">New Company</span>
                            </Link>
                        </PermissionGuard>
                    </div>

                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search by name, email, phone or address..."
                        searchAriaLabel="Search insurance companies"
                        statusValue={filters.status ?? ''}
                        onStatusChange={(value) => {
                            startSearchTransition(() => {
                                setFilters((previous) => ({
                                    ...previous,
                                    status: mapStatusValue(value === '' ? 'all' : value),
                                    page: 1,
                                }));
                            });
                        }}
                        dateFrom={filters.date_from}
                        dateTo={filters.date_to}
                        onDateRangeChange={(range) => {
                            startSearchTransition(() => {
                                setFilters((previous) => ({
                                    ...previous,
                                    date_from: range.dateFrom,
                                    date_to: range.dateTo,
                                    page: 1,
                                }));
                            });
                        }}
                        actions={<ExportButton onExport={handleExport} isExporting={isPendingExport} />}
                    />

                    {selectedActiveUuids.length > 0 && (
                        <PermissionGuard permissions={['DELETE_INSURANCE_COMPANY']}>
                            <DataTableBulkActions
                                count={selectedActiveUuids.length}
                                onDelete={handleBulkDelete}
                                isDeleting={bulkDeleteInsuranceCompanies.isPending}
                            />
                        </PermissionGuard>
                    )}

                    <div className="overflow-hidden rounded-2xl border shadow-xl" style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}>
                        <InsuranceCompaniesTable
                            data={optimisticCompanies}
                            isLoading={isPending}
                            isError={isError}
                            onDelete={handleDeleteClick}
                            onRestore={handleRestoreClick}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                        />

                        {meta.lastPage > 1 && (
                            <div className="flex items-center justify-between border-t px-6 py-4" style={{ borderColor: 'var(--border-subtle)' }}>
                                <span className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                                    {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                                </span>
                                <div className="flex items-center gap-2">
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.currentPage - 1)}
                                        disabled={meta.currentPage <= 1}
                                        className="flex h-9 w-9 items-center justify-center rounded-xl border disabled:pointer-events-none disabled:opacity-30"
                                        style={{ borderColor: 'var(--border-default)', color: 'var(--text-muted)' }}
                                    >
                                        <ChevronLeft size={18} />
                                    </button>

                                    <div className="mx-2 flex items-center gap-1">
                                        {paginationPages.map((page) => (
                                            <button
                                                key={page}
                                                type="button"
                                                onClick={() => goToPage(page)}
                                                className="h-9 w-9 rounded-xl text-xs font-bold"
                                                style={meta.currentPage === page
                                                    ? { background: 'var(--accent-primary)', color: 'var(--text-primary)' }
                                                    : { color: 'var(--text-muted)' }}
                                            >
                                                {page}
                                            </button>
                                        ))}
                                    </div>

                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.currentPage + 1)}
                                        disabled={meta.currentPage >= meta.lastPage}
                                        className="flex h-9 w-9 items-center justify-center rounded-xl border disabled:pointer-events-none disabled:opacity-30"
                                        style={{ borderColor: 'var(--border-default)', color: 'var(--text-muted)' }}
                                    >
                                        <ChevronRight size={18} />
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.name ?? ''}
                    onConfirm={handleConfirmDelete}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteInsuranceCompany.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="insurance company"
                    entityName={pendingRestore?.name}
                    onConfirm={handleConfirmRestore}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreInsuranceCompany.isPending}
                />
            </AppLayout>
        </>
    );
}
