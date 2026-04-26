import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, Plus } from 'lucide-react';
import { ExportButton } from '@/common/export/ExportButton';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { usePublicCompanies } from '@/modules/public-companies/hooks/usePublicCompanies';
import { usePublicCompanyMutations } from '@/modules/public-companies/hooks/usePublicCompanyMutations';
import type { PublicCompanyFilters } from '@/modules/public-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import PublicCompaniesTable from './components/PublicCompaniesTable';

const DEFAULT_META = {
    currentPage: 1,
    lastPage: 1,
    perPage: 15,
    total: 0,
};

export default function PublicCompaniesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<PublicCompanyFilters>({ page: 1, per_page: 15 }, 'public-companies-filters');
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [isPendingExport, startExportTransition] = React.useTransition();
    const [, startSearchTransition] = React.useTransition();

    const { data, isPending, isError } = usePublicCompanies(filters);
    const companies = data?.data ?? [];
    const meta = data?.meta ?? DEFAULT_META;
    const { deletePublicCompany, restorePublicCompany } = usePublicCompanyMutations();

    function handleSearchChange(value: string): void {
        setSearch(value);

        startSearchTransition(() => {
            setFilters((previous) => ({
                ...previous,
                search: value || undefined,
                page: 1,
            }));
        });
    }

    async function handleConfirmDelete(): Promise<void> {
        if (pendingDelete === null) {
            return;
        }

        await deletePublicCompany.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) {
            return;
        }

        await restorePublicCompany.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
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
            window.open(`/public-companies/data/admin/export?${params.toString()}`, '_blank', 'noopener,noreferrer');
        });
    }

    function goToPage(page: number): void {
        setFilters((previous) => ({ ...previous, page }));
    }

    return (
        <>
            <Head title="Public Companies" />
            <AppLayout>
                <div className="mx-auto flex max-w-7xl flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                                Public Companies
                            </h1>
                            <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                                Manage public companies in the CRM.{' '}
                                <span style={{ color: 'var(--accent-primary)' }}>
                                    {meta.total} {meta.total === 1 ? 'company' : 'companies'}
                                </span>
                            </p>
                        </div>

                        <PermissionGuard permissions={['CREATE_PUBLIC_COMPANY']}>
                            <Link href="/public-companies/create" className="btn-primary inline-flex items-center justify-center gap-2 px-6 py-2.5 text-sm font-bold">
                                <Plus size={18} />
                                <span>New Company</span>
                            </Link>
                        </PermissionGuard>
                    </div>

                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search by company, email, phone or address"
                        searchAriaLabel="Search public companies"
                        statusValue={filters.status ?? ''}
                        onStatusChange={(value) => {
                            startSearchTransition(() => {
                                setFilters((previous) => ({
                                    ...previous,
                                    status: value === '' ? undefined : value as 'active' | 'deleted',
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

                    <div className="overflow-hidden rounded-2xl border shadow-xl" style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}>
                        <PublicCompaniesTable
                            data={companies}
                            isLoading={isPending}
                            isError={isError}
                            onDelete={(uuid, name) => setPendingDelete({ uuid, name })}
                            onRestore={(uuid, name) => setPendingRestore({ uuid, name })}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                        />

                        {meta.lastPage > 1 ? (
                            <div className="flex flex-col gap-3 border-t px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6" style={{ borderColor: 'var(--border-subtle)' }}>
                                <span className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                                    Page {meta.currentPage} / {meta.lastPage} · {meta.total} total
                                </span>
                                <div className="flex items-center gap-2">
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.currentPage - 1)}
                                        disabled={meta.currentPage <= 1}
                                        className="inline-flex h-9 w-9 items-center justify-center rounded-xl border disabled:opacity-40"
                                        style={{ borderColor: 'var(--border-default)', background: 'var(--bg-app)', color: 'var(--text-primary)' }}
                                    >
                                        <ChevronLeft size={18} />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.currentPage + 1)}
                                        disabled={meta.currentPage >= meta.lastPage}
                                        className="inline-flex h-9 w-9 items-center justify-center rounded-xl border disabled:opacity-40"
                                        style={{ borderColor: 'var(--border-default)', background: 'var(--bg-app)', color: 'var(--text-primary)' }}
                                    >
                                        <ChevronRight size={18} />
                                    </button>
                                </div>
                            </div>
                        ) : null}
                    </div>
                </div>

                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.name ?? ''}
                    onConfirm={() => {
                        void handleConfirmDelete();
                    }}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deletePublicCompany.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="public company"
                    entityName={pendingRestore?.name}
                    onConfirm={() => {
                        void handleConfirmRestore();
                    }}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restorePublicCompany.isPending}
                />
            </AppLayout>
        </>
    );
}
