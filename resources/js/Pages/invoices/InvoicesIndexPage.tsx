import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import { Plus, ChevronLeft, ChevronRight } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useInvoices } from '@/modules/invoices/hooks/useInvoices';
import { useDeleteInvoice, useRestoreInvoice, useBulkDeleteInvoices } from '@/modules/invoices/hooks/useInvoiceMutations';
import type { InvoiceFilters, InvoiceStatus } from '@/modules/invoices/types';
import InvoicesTable from './components/InvoicesTable';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { ExportButton } from '@/common/export/ExportButton';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';
import type { RowSelectionState } from '@tanstack/react-table';

const INV_STATUSES: { value: InvoiceStatus | ''; label: string }[] = [
    { value: '', label: 'All Statuses' },
    { value: 'draft', label: 'Draft' },
    { value: 'sent', label: 'Sent' },
    { value: 'paid', label: 'Paid' },
    { value: 'cancelled', label: 'Cancelled' },
    { value: 'print_pdf', label: 'Print PDF' },
];

export default function InvoicesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<InvoiceFilters>({ page: 1, per_page: 15 }, 'invoices-filters');
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);
    const [, startSearchTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending } = useInvoices(filters);
    const invoices = data?.data ?? [];
    const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };

    const deleteInvoice = useDeleteInvoice();
    const restoreInvoice = useRestoreInvoice();
    const bulkDelete = useBulkDeleteInvoices();

    const handleSearchChange = (value: string): void => {
        setSearch(value);
        startSearchTransition(() => setFilters((p) => ({ ...p, search: value || undefined, page: 1 })));
    };

    const handleExport = (format: 'excel' | 'pdf') => {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.status) params.append('status', filters.status);
            if (filters.invoice_status) params.append('invoice_status', filters.invoice_status);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            params.append('format', format);
            window.open(`/invoices/data/admin/export?${params}`, '_blank');
        });
    };

    const startPage = Math.max(1, meta.currentPage - 2);
    const endPage = Math.min(meta.lastPage, startPage + 4);

    return (
        <>
            <Head title="Invoices" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 style={{ margin: 0, fontSize: 28, fontWeight: 800, letterSpacing: '-0.5px', color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>Invoices</h1>
                            <p style={{ margin: '4px 0 0', fontSize: 13, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>
                        <PermissionGuard permissions={['CREATE_INVOICE']}>
                            <Link href="/invoices/create" style={{ display: 'inline-flex', alignItems: 'center', gap: 8, padding: '10px 20px', borderRadius: 'var(--radius-lg)', background: 'var(--accent-primary)', color: 'var(--color-white)', fontWeight: 700, fontSize: 13, fontFamily: 'var(--font-sans)', textDecoration: 'none' }}>
                                <Plus size={16} /> New Invoice
                            </Link>
                        </PermissionGuard>
                    </div>

                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search invoices…"
                        searchAriaLabel="Search invoices"
                        statusValue={filters.status ?? ''}
                        onStatusChange={(value) => {
                            startSearchTransition(() => {
                                setFilters((p) => ({
                                    ...p,
                                    status: value === '' ? undefined : value as 'active' | 'deleted',
                                    page: 1,
                                }));
                            });
                        }}
                        selects={[
                            {
                                value: filters.invoice_status ?? '',
                                onChange: (value) => {
                                    startSearchTransition(() => {
                                        setFilters((p) => ({
                                            ...p,
                                            invoice_status: value === '' ? undefined : value as InvoiceStatus,
                                            page: 1,
                                        }));
                                    });
                                },
                                options: INV_STATUSES,
                                ariaLabel: 'Invoice status filter',
                                label: 'Invoice Status',
                                minWidth: 160,
                            },
                        ]}
                        dateFrom={filters.date_from}
                        dateTo={filters.date_to}
                        onDateRangeChange={(range) => {
                            startSearchTransition(() => {
                                setFilters((p) => ({
                                    ...p,
                                    date_from: range.dateFrom,
                                    date_to: range.dateTo,
                                    page: 1,
                                }));
                            });
                        }}
                        actions={<ExportButton onExport={handleExport} isExporting={isPendingExport} />}
                    />

                    <DataTableBulkActions count={Object.keys(rowSelection).length} onDelete={async () => { const uuids = Object.keys(rowSelection).filter((k) => rowSelection[k]); if (uuids.length) { await bulkDelete.mutateAsync(uuids); setRowSelection({}); } }} isDeleting={bulkDelete.isPending} />

                    {/* Table */}
                    <div style={{ borderRadius: 'var(--radius-lg)', border: '1px solid var(--border-default)', background: 'var(--bg-card)', overflow: 'hidden' }}>
                        <InvoicesTable
                            data={invoices}
                            isPending={isPending}
                            onDeleteClick={(uuid, name) => setPendingDelete({ uuid, name })}
                            onRestoreClick={(uuid, name) => setPendingRestore({ uuid, name })}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                        />

                        {meta.lastPage > 1 && (
                            <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '12px 20px', borderTop: '1px solid var(--border-subtle)' }}>
                                <span style={{ fontSize: 11, color: 'var(--text-disabled)', fontFamily: 'var(--font-sans)', textTransform: 'uppercase', letterSpacing: '0.08em' }}>
                                    Page {meta.currentPage} / {meta.lastPage} · {meta.total} total
                                </span>
                                <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
                                    <button onClick={() => setFilters((p) => ({ ...p, page: (p.page ?? 1) - 1 }))} disabled={meta.currentPage === 1} aria-label="Previous page" style={{ width: 32, height: 32, display: 'flex', alignItems: 'center', justifyContent: 'center', borderRadius: 'var(--radius-md)', border: '1px solid var(--border-default)', background: 'var(--bg-card)', color: 'var(--text-muted)', cursor: 'pointer', opacity: meta.currentPage === 1 ? 0.3 : 1 }}>
                                        <ChevronLeft size={14} />
                                    </button>
                                    {Array.from({ length: endPage - startPage + 1 }, (_, i) => startPage + i).map((p) => (
                                        <button key={p} onClick={() => setFilters((prev) => ({ ...prev, page: p }))} style={{ width: 32, height: 32, display: 'flex', alignItems: 'center', justifyContent: 'center', borderRadius: 'var(--radius-md)', border: '1px solid var(--border-default)', background: p === meta.currentPage ? 'var(--accent-primary)' : 'var(--bg-card)', color: p === meta.currentPage ? 'var(--color-white)' : 'var(--text-muted)', cursor: 'pointer', fontSize: 12, fontWeight: p === meta.currentPage ? 700 : 400 }}>
                                            {p}
                                        </button>
                                    ))}
                                    <button onClick={() => setFilters((p) => ({ ...p, page: (p.page ?? 1) + 1 }))} disabled={meta.currentPage === meta.lastPage} aria-label="Next page" style={{ width: 32, height: 32, display: 'flex', alignItems: 'center', justifyContent: 'center', borderRadius: 'var(--radius-md)', border: '1px solid var(--border-default)', background: 'var(--bg-card)', color: 'var(--text-muted)', cursor: 'pointer', opacity: meta.currentPage === meta.lastPage ? 0.3 : 1 }}>
                                        <ChevronRight size={14} />
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <DeleteConfirmModal open={!!pendingDelete} entityLabel={pendingDelete?.name ?? ''} onConfirm={async () => { if (!pendingDelete) return; await deleteInvoice.mutateAsync(pendingDelete.uuid); setPendingDelete(null); }} onCancel={() => setPendingDelete(null)} isDeleting={deleteInvoice.isPending} />
                <RestoreConfirmModal isOpen={!!pendingRestore} entityLabel="invoice" entityName={pendingRestore?.name} onConfirm={async () => { if (!pendingRestore) return; await restoreInvoice.mutateAsync(pendingRestore.uuid); setPendingRestore(null); }} onCancel={() => setPendingRestore(null)} isPending={restoreInvoice.isPending} />
            </AppLayout>
        </>
    );
}
