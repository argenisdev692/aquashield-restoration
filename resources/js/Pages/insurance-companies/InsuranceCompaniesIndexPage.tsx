import * as React from 'react';
import { Link, Head, useRemember } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useInsuranceCompanies } from '@/modules/insurance-companies/hooks/useInsuranceCompanies';
import { useInsuranceCompanyMutations } from '@/modules/insurance-companies/hooks/useInsuranceCompanyMutations';
import InsuranceCompaniesTable from './components/InsuranceCompaniesTable';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { DataTableDateRangeFilter } from '@/common/data-table/DataTableDateRangeFilter';
import { ExportButton } from '@/common/export/ExportButton';
import type { InsuranceCompanyFilters } from '@/modules/insurance-companies/types';
import { Search, ChevronLeft, ChevronRight, Plus } from 'lucide-react';

function mapStatusValue(value: string): InsuranceCompanyFilters['status'] | undefined {
    return value === 'all' ? undefined : value as InsuranceCompanyFilters['status'];
}

export default function InsuranceCompaniesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<InsuranceCompanyFilters>({ page: 1, perPage: 15 }, 'insurance-companies-filters');
    const [search, setSearch] = React.useState<string>(filters.search || '');
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);

    const [isPendingExport, startExportTransition] = React.useTransition();
    const [, startSearchTransition] = React.useTransition();

    const { data, isPending, isError } = useInsuranceCompanies(filters);
    const insuranceCompanies = data?.data ?? [];
    const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };

    const { deleteInsuranceCompany } = useInsuranceCompanyMutations();

    const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        setSearch(value);
        startSearchTransition(() => {
            setFilters((prev) => ({ ...prev, search: value || undefined, page: 1 }));
        });
    };

    const handleDeleteClick = (uuid: string, name: string) => {
        setPendingDelete({ uuid, name });
    };

    const handleConfirmDelete = async () => {
        if (!pendingDelete) return;
        await deleteInsuranceCompany.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    };

    const handleExport = (format: 'excel' | 'pdf') => {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.dateFrom) params.append('dateFrom', filters.dateFrom);
            if (filters.dateTo) params.append('dateTo', filters.dateTo);
            params.append('format', format);
            window.open(`/insurance-companies/data/admin/export?${params.toString()}`, '_blank');
        });
    };

    const goToPage = (page: number) => {
        setFilters((prev) => ({ ...prev, page }));
    };

    return (
        <>
            <Head title="Insurance Companies" />
            <AppLayout>
                <div className="flex flex-col gap-6 animate-in fade-in duration-500">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 className="text-3xl font-extrabold tracking-tight text-(--text-primary)">
                                Insurance Companies
                            </h1>
                            <p className="text-sm mt-1 text-(--text-muted) font-medium">
                                Manage your insurance carriers — <span className="text-(--accent-primary)">{meta.total} {meta.total === 1 ? 'carrier' : 'carriers'}</span> found
                            </p>
                        </div>
                        <Link
                            href="/insurance-companies/create"
                            className="bg-(--accent-primary) text-white font-bold py-2.5 px-6 rounded-xl hover:scale-[1.03] active:scale-[0.97] transition-all flex items-center gap-2 shadow-lg shadow-blue-500/20"
                        >
                            <Plus size={18} />
                            <span>New Company</span>
                        </Link>
                    </div>

                    <div className="flex flex-col items-center gap-3 rounded-2xl px-5 py-4 sm:flex-row glass-morphism border border-(--border-default) shadow-sm bg-(--bg-card)/50">
                        <div className="flex flex-1 items-center gap-3 w-full group">
                            <Search size={18} className="text-(--text-disabled) group-focus-within:text-(--accent-primary) transition-colors" />
                            <input
                                type="text"
                                value={search}
                                onChange={handleSearchChange}
                                placeholder="Search by name or email..."
                                className="flex-1 bg-transparent text-sm outline-none placeholder:text-(--text-disabled) text-(--text-primary)"
                            />
                        </div>

                        <div className="flex w-full items-center gap-4 sm:w-auto">
                            <DataTableDateRangeFilter
                                dateFrom={filters.dateFrom}
                                dateTo={filters.dateTo}
                                onChange={(range) => setFilters(p => ({ 
                                    ...p, 
                                    dateFrom: range.dateFrom, 
                                    dateTo: range.dateTo, 
                                    page: 1 
                                }))}
                            />
                            
                            <select
                                value={filters.status || "all"}
                                onChange={(e) =>
                                    setFilters((p) => ({
                                        ...p,
                                        status: mapStatusValue(e.target.value),
                                        page: 1,
                                    }))
                                }
                                className="px-3 py-2 rounded-lg text-sm outline-none bg-(--bg-subtle) text-(--text-primary) border border-(--border-default)"
                            >
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="deleted">Deleted</option>
                            </select>
                            
                            <div className="h-8 w-px bg-(--border-subtle) hidden sm:block" />
                            <ExportButton 
                                onExport={handleExport} 
                                isExporting={isPendingExport} 
                            />
                        </div>
                    </div>

                    <div className="overflow-hidden rounded-2xl border border-(--border-default) shadow-2xl bg-(--bg-card)">
                        <InsuranceCompaniesTable
                            data={insuranceCompanies}
                            isLoading={isPending}
                            isError={isError}
                            onDelete={handleDeleteClick}
                        />

                        {meta.lastPage > 1 && (
                            <div className="flex items-center justify-between px-6 py-4 border-t border-(--border-subtle) bg-white/5">
                                <span className="text-xs font-semibold text-(--text-disabled) uppercase tracking-wider">
                                    Page {meta.currentPage} / {meta.lastPage} • {meta.total} Total
                                </span>
                                <div className="flex items-center gap-2">
                                    <button
                                        onClick={() => goToPage(meta.currentPage - 1)}
                                        disabled={meta.currentPage <= 1}
                                        className="h-9 w-9 flex items-center justify-center rounded-xl bg-(--bg-app) border border-(--border-default) hover:bg-(--bg-hover) disabled:opacity-30 transition-all font-bold"
                                    >
                                        <ChevronLeft size={18} />
                                    </button>
                                    <button
                                        onClick={() => goToPage(meta.currentPage + 1)}
                                        disabled={meta.currentPage >= meta.lastPage}
                                        className="h-9 w-9 flex items-center justify-center rounded-xl bg-(--bg-app) border border-(--border-default) hover:bg-(--bg-hover) disabled:opacity-30 transition-all font-bold"
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
                    entityLabel={pendingDelete?.name || ''}
                    onConfirm={handleConfirmDelete}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteInsuranceCompany.isPending}
                />
            </AppLayout>
        </>
    );
}
