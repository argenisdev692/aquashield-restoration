import * as React from 'react';
import { Link, Head, useRemember } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useMortgageCompanies } from '@/modules/mortgage-companies/hooks/useMortgageCompanies';
import { useMortgageCompanyMutations } from '@/modules/mortgage-companies/hooks/useMortgageCompanyMutations';
import MortgageCompaniesTable from '@/pages/mortgage-companies/components/MortgageCompaniesTable';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { DataTableDateRangeFilter } from '@/common/data-table/DataTableDateRangeFilter';
import { ExportButton } from '@/common/export/ExportButton';
import type { MortgageCompanyFilters } from '@/types/api';
import { Search, ChevronLeft, ChevronRight, Plus } from 'lucide-react';

export default function MortgageCompaniesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<MortgageCompanyFilters>({ page: 1, perPage: 15 }, 'mortgage-companies-filters');
    const [search, setSearch] = React.useState<string>(filters.search || '');
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);

    const [isPendingExport, startExportTransition] = React.useTransition();
    const [, startSearchTransition] = React.useTransition();

    const { data, isPending, isError } = useMortgageCompanies(filters);
    const mortgageCompanies = data?.data ?? [];
    const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };

    const { deleteMortgageCompany, restoreMortgageCompany } = useMortgageCompanyMutations();

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

    const handleRestoreClick = (uuid: string, name: string) => {
        setPendingRestore({ uuid, name });
    };

    const handleConfirmDelete = async () => {
        if (!pendingDelete) return;
        await deleteMortgageCompany.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    };

    const handleConfirmRestore = async () => {
        if (!pendingRestore) return;
        await restoreMortgageCompany.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    };

    const handleExport = (format: 'excel' | 'pdf') => {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.dateFrom) params.append('dateFrom', filters.dateFrom);
            if (filters.dateTo) params.append('dateTo', filters.dateTo);
            params.append('format', format);
            window.open(`/mortgage-companies/data/export?${params.toString()}`, '_blank');
        });
    };

    const goToPage = (page: number) => {
        setFilters((prev) => ({ ...prev, page }));
    };

    return (
        <>
            <Head title="Mortgage Companies" />
            <AppLayout>
                <div className="flex flex-col gap-6 animate-in fade-in duration-500">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}
                            >
                                Mortgage Companies
                            </h1>
                            <p 
                                className="text-sm mt-1 font-medium"
                                style={{ color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}
                            >
                                Manage your mortgage lenders — <span style={{ color: 'var(--accent-primary)' }}>{meta.total} {meta.total === 1 ? 'company' : 'companies'}</span> found
                            </p>
                        </div>
                        <Link
                            href="/mortgage-companies/create"
                            className="font-bold py-2.5 px-6 rounded-xl hover:scale-[1.03] active:scale-[0.97] transition-all flex items-center gap-2 shadow-lg"
                            style={{
                                background: 'var(--accent-primary)',
                                color: '#ffffff',
                                fontFamily: 'var(--font-sans)' ,
                                boxShadow: '0 10px 40px color-mix(in srgb, var(--blue-500) 20%, transparent)',
                            }}
                        >
                            <Plus size={18} />
                            <span>New Company</span>
                        </Link>
                    </div>

                    <div 
                        className="flex flex-col items-center gap-3 rounded-2xl px-5 py-4 sm:flex-row shadow-sm"
                        style={{
                            background: 'color-mix(in srgb, var(--bg-card) 50%, transparent)',
                            border: '1px solid var(--border-default)',
                            backdropFilter: 'blur(12px)',
                        }}
                    >
                        <div className="flex flex-1 items-center gap-3 w-full group">
                            <Search 
                                size={18} 
                                className="transition-colors" 
                                style={{ color: 'var(--text-disabled)' }}
                            />
                            <input
                                type="text"
                                value={search}
                                onChange={handleSearchChange}
                                placeholder="Search by name, email, or phone..."
                                className="flex-1 bg-transparent text-sm outline-none"
                                style={{
                                    color: 'var(--text-primary)',
                                    fontFamily: 'var(--font-sans)',
                                }}
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
                                onChange={(e) => {
                                    const value = e.target.value;
                                    setFilters((p) => ({
                                        ...p,
                                        status: value === "all" ? undefined : (value as 'active' | 'deleted'),
                                        page: 1,
                                    }));
                                }}
                                className="px-3 py-2 rounded-lg text-sm outline-none"
                                style={{
                                    background: 'var(--bg-subtle)',
                                    color: 'var(--text-primary)',
                                    border: '1px solid var(--border-default)',
                                    fontFamily: 'var(--font-sans)',
                                }}
                            >
                                <option value="all">All Status</option>
                                <option value="active">Active</option>
                                <option value="deleted">Deleted</option>
                            </select>
                            
                            <div 
                                className="h-8 w-px hidden sm:block" 
                                style={{ background: 'var(--border-subtle)' }}
                            />
                            <ExportButton 
                                onExport={handleExport} 
                                isExporting={isPendingExport} 
                            />
                        </div>
                    </div>

                    <div 
                        className="overflow-hidden rounded-2xl shadow-2xl"
                        style={{
                            border: '1px solid var(--border-default)',
                            background: 'var(--bg-card)',
                        }}
                    >
                        <MortgageCompaniesTable
                            data={mortgageCompanies}
                            isLoading={isPending}
                            isError={isError}
                            onDelete={handleDeleteClick}
                            onRestore={handleRestoreClick}
                        />

                        {meta.lastPage > 1 && (
                            <div 
                                className="flex items-center justify-between px-6 py-4"
                                style={{
                                    borderTop: '1px solid var(--border-subtle)',
                                    background: 'color-mix(in srgb, #ffffff 5%, transparent)',
                                }}
                            >
                                <span 
                                    className="text-xs font-semibold uppercase tracking-wider"
                                    style={{ color: 'var(--text-disabled)', fontFamily: 'var(--font-sans)' }}
                                >
                                    Page {meta.currentPage} / {meta.lastPage} • {meta.total} Total
                                </span>
                                <div className="flex items-center gap-2">
                                    <button
                                        onClick={() => goToPage(meta.currentPage - 1)}
                                        disabled={meta.currentPage <= 1}
                                        className="h-9 w-9 flex items-center justify-center rounded-xl font-bold transition-all disabled:opacity-30"
                                        style={{
                                            background: 'var(--bg-app)',
                                            border: '1px solid var(--border-default)',
                                        }}
                                    >
                                        <ChevronLeft size={18} />
                                    </button>
                                    <button
                                        onClick={() => goToPage(meta.currentPage + 1)}
                                        disabled={meta.currentPage >= meta.lastPage}
                                        className="h-9 w-9 flex items-center justify-center rounded-xl font-bold transition-all disabled:opacity-30"
                                        style={{
                                            background: 'var(--bg-app)',
                                            border: '1px solid var(--border-default)',
                                        }}
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
                    isDeleting={deleteMortgageCompany.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="mortgage company"
                    entityName={pendingRestore?.name}
                    onConfirm={handleConfirmRestore}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreMortgageCompany.isPending}
                />
            </AppLayout>
        </>
    );
}
