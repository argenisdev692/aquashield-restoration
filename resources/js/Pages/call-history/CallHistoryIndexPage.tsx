import * as React from 'react';
import { useState, useMemo, useTransition, useCallback } from 'react';
import { Head, router } from '@inertiajs/react';
import {
    useReactTable,
    getCoreRowModel,
    getSortedRowModel,
    flexRender,
    createColumnHelper,
    type SortingState,
    type RowSelectionState,
} from '@tanstack/react-table';
import {
    PhoneIncoming,
    PhoneOutgoing,
    Calendar,
    Clock,
    Filter,
    Search,
    ChevronLeft,
    ChevronRight,
    RefreshCw,
    Eye,
    Trash2,
    CheckCircle,
} from 'lucide-react';
import AppLayout from '../layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { ExportButton } from '@/common/export/ExportButton';
import {
    useCallHistoryList,
    useDeleteCallHistory,
    useRestoreCallHistory,
    useBulkDeleteCallHistory,
    useSyncCallsFromRetell,
} from './hooks';
import type { CallHistoryListItem, CallHistoryFilters } from './types';

const columnHelper = createColumnHelper<CallHistoryListItem>();

export default function CallHistoryIndexPage(): React.JSX.Element {
    const [sorting, setSorting] = React.useState<SortingState>([{ id: 'start_timestamp', desc: true }]);
    const [rowSelection, setRowSelection] = useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = useState<CallHistoryListItem | null>(null);
    const [pendingRestore, setPendingRestore] = useState<CallHistoryListItem | null>(null);
    const [, startSearchTransition] = useTransition();
    const [isPendingExport, startExportTransition] = useTransition();

    const [filters, setFilters] = useState<CallHistoryFilters>({
        search: '',
        status: '',
        direction: '',
        callType: '',
        dateFrom: '',
        dateTo: '',
        sortField: 'start_timestamp',
        sortDirection: 'desc',
        page: 1,
        perPage: 10,
    });

    const { data, isPending, isError } = useCallHistoryList(filters);
    const deleteCallHistory = useDeleteCallHistory();
    const restoreCallHistory = useRestoreCallHistory();
    const bulkDeleteCallHistory = useBulkDeleteCallHistory();
    const syncCallsFromRetell = useSyncCallsFromRetell();

    const calls = useMemo(() => data?.data ?? [], [data]);
    const meta = useMemo(() => data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 10, total: 0 }, [data]);

    const columns = useMemo(
        () => [
            columnHelper.accessor('callId', {
                header: 'Call ID',
                cell: ({ row }) => (
                    <span className="font-mono text-xs" style={{ color: 'var(--text-muted)' }}>
                        {row.original.callId.slice(0, 16)}...
                    </span>
                ),
            }),
            columnHelper.accessor('direction', {
                header: 'Direction',
                cell: ({ row }) => {
                    const isInbound = row.original.direction === 'inbound';
                    return (
                        <div className="flex items-center gap-2">
                            {isInbound ? (
                                <PhoneIncoming size={16} style={{ color: 'var(--success)' }} />
                            ) : (
                                <PhoneOutgoing size={16} style={{ color: 'var(--accent-primary)' }} />
                            )}
                            <span className="text-sm capitalize">{row.original.direction}</span>
                        </div>
                    );
                },
            }),
            columnHelper.accessor('fromNumber', {
                header: 'From',
                cell: ({ row }) => (
                    <span className="text-sm font-medium">{row.original.fromNumber ?? 'N/A'}</span>
                ),
            }),
            columnHelper.accessor('toNumber', {
                header: 'To',
                cell: ({ row }) => (
                    <span className="text-sm font-medium">{row.original.toNumber ?? 'N/A'}</span>
                ),
            }),
            columnHelper.accessor('callStatus', {
                header: 'Status',
                cell: ({ row }) => {
                    const status = row.original.callStatus;
                    const statusColors: Record<string, string> = {
                        registered: 'var(--warning)',
                        ongoing: 'var(--accent-primary)',
                        ended: 'var(--success)',
                        error: 'var(--error)',
                    };
                    return (
                        <span
                            className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize"
                            style={{
                                background: `color-mix(in srgb, ${statusColors[status] ?? 'var(--text-muted)'} 15%, transparent)`,
                                color: statusColors[status] ?? 'var(--text-muted)',
                            }}
                        >
                            {status}
                        </span>
                    );
                },
            }),
            columnHelper.accessor('callType', {
                header: 'Type',
                cell: ({ row }) => (
                    <span className="text-sm capitalize" style={{ color: 'var(--text-muted)' }}>
                        {row.original.callType}
                    </span>
                ),
            }),
            columnHelper.accessor('startTimestamp', {
                header: 'Start Time',
                cell: ({ row }) => {
                    const date = row.original.startTimestamp;
                    if (!date) return <span style={{ color: 'var(--text-disabled)' }}>-</span>;
                    return (
                        <div className="flex items-center gap-2">
                            <Calendar size={14} style={{ color: 'var(--text-muted)' }} />
                            <span className="text-sm">
                                {new Date(date).toLocaleString('en-US', {
                                    month: 'short',
                                    day: 'numeric',
                                    hour: '2-digit',
                                    minute: '2-digit',
                                })}
                            </span>
                        </div>
                    );
                },
            }),
            columnHelper.accessor('durationMs', {
                header: 'Duration',
                cell: ({ row }) => {
                    const duration = row.original.durationMs;
                    if (!duration) return <span style={{ color: 'var(--text-disabled)' }}>-</span>;
                    const minutes = Math.floor(duration / 60000);
                    const seconds = Math.floor((duration % 60000) / 1000);
                    return (
                        <div className="flex items-center gap-2">
                            <Clock size={14} style={{ color: 'var(--text-muted)' }} />
                            <span className="text-sm">{minutes}:{seconds.toString().padStart(2, '0')}</span>
                        </div>
                    );
                },
            }),
            columnHelper.display({
                id: 'actions',
                header: '',
                cell: ({ row }) => {
                    const call = row.original;
                    const isDeleted = call.deletedAt !== null;

                    return (
                        <div className="flex items-center justify-end gap-2">
                            <PermissionGuard permissions={['VIEW_CALL_HISTORY']}>
                                <button
                                    onClick={() => router.visit(`/call-history/${call.uuid}`)}
                                    className="flex h-8 w-8 items-center justify-center rounded-lg transition-colors hover:bg-black/5"
                                    style={{ color: 'var(--text-muted)' }}
                                    title="View"
                                >
                                    <Eye size={16} />
                                </button>
                            </PermissionGuard>

                            {!isDeleted ? (
                                <PermissionGuard permissions={['DELETE_CALL_HISTORY']}>
                                    <button
                                        onClick={() => setPendingDelete(call)}
                                        className="flex h-8 w-8 items-center justify-center rounded-lg transition-colors hover:bg-red-500/10"
                                        style={{ color: 'var(--error)' }}
                                        title="Delete"
                                    >
                                        <Trash2 size={16} />
                                    </button>
                                </PermissionGuard>
                            ) : (
                                <PermissionGuard permissions={['RESTORE_CALL_HISTORY']}>
                                    <button
                                        onClick={() => setPendingRestore(call)}
                                        className="flex h-8 w-8 items-center justify-center rounded-lg transition-colors hover:bg-green-500/10"
                                        style={{ color: 'var(--success)' }}
                                        title="Restore"
                                    >
                                        <CheckCircle size={16} />
                                    </button>
                                </PermissionGuard>
                            )}
                        </div>
                    );
                },
            }),
        ],
        []
    );

    const table = useReactTable({
        data: calls,
        columns,
        state: {
            sorting,
            rowSelection,
        },
        enableRowSelection: true,
        onSortingChange: setSorting,
        onRowSelectionChange: setRowSelection,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
        getRowId: (row) => row.uuid,
    });

    const selectedUuids = useMemo(
        () => Object.keys(rowSelection).filter((uuid) => rowSelection[uuid]),
        [rowSelection]
    );

    const selectedActiveUuids = useMemo(
        () => selectedUuids.filter((uuid) => !calls.find((c) => c.uuid === uuid)?.deletedAt),
        [selectedUuids, calls]
    );

    const handleSearchChange = useCallback((value: string) => {
        startSearchTransition(() => {
            setFilters((prev) => ({ ...prev, search: value, page: 1 }));
        });
    }, []);

    const handleFilterChange = useCallback((key: keyof CallHistoryFilters, value: string) => {
        setFilters((prev) => ({ ...prev, [key]: value, page: 1 }));
    }, []);

    const goToPage = useCallback((page: number) => {
        setFilters((prev) => ({ ...prev, page }));
    }, []);

    const handleBulkDelete = useCallback(() => {
        if (selectedActiveUuids.length > 0) {
            bulkDeleteCallHistory.mutate(selectedActiveUuids);
            setRowSelection({});
        }
    }, [selectedActiveUuids, bulkDeleteCallHistory]);

    const handleConfirmDelete = useCallback(() => {
        if (pendingDelete) {
            deleteCallHistory.mutate(pendingDelete.uuid);
            setPendingDelete(null);
        }
    }, [pendingDelete, deleteCallHistory]);

    const handleConfirmRestore = useCallback(() => {
        if (pendingRestore) {
            restoreCallHistory.mutate(pendingRestore.uuid);
            setPendingRestore(null);
        }
    }, [pendingRestore, restoreCallHistory]);

    const handleSync = useCallback(() => {
        syncCallsFromRetell.mutate({
            startDate: filters.dateFrom,
            endDate: filters.dateTo,
        });
    }, [syncCallsFromRetell, filters.dateFrom, filters.dateTo]);

    const handleExport = useCallback(
        (format: 'excel' | 'pdf') => {
            startExportTransition(() => {
                const params = new URLSearchParams({ format });
                if (filters.search) params.append('search', filters.search);
                if (filters.status) params.append('status', filters.status);
                if (filters.direction) params.append('direction', filters.direction);
                if (filters.callType) params.append('call_type', filters.callType);
                if (filters.dateFrom) params.append('date_from', filters.dateFrom);
                if (filters.dateTo) params.append('date_to', filters.dateTo);
                window.open(`/call-history/data/admin/export?${params.toString()}`, '_blank');
            });
        },
        [filters]
    );

    const paginationPages = useMemo(() => {
        const current = meta.currentPage;
        const last = meta.lastPage;
        const pages: (number | string)[] = [];

        if (last <= 7) {
            for (let i = 1; i <= last; i++) pages.push(i);
        } else {
            if (current <= 3) {
                for (let i = 1; i <= 5; i++) pages.push(i);
                pages.push('...', last);
            } else if (current >= last - 2) {
                pages.push(1, '...');
                for (let i = last - 4; i <= last; i++) pages.push(i);
            } else {
                pages.push(1, '...');
                for (let i = current - 1; i <= current + 1; i++) pages.push(i);
                pages.push('...', last);
            }
        }
        return pages;
    }, [meta]);

    return (
        <>
            <Head title="Call History" />
            <AppLayout>
                <div className="mx-auto max-w-[1600px] space-y-6 p-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 className="text-2xl font-bold" style={{ color: 'var(--text-primary)' }}>
                                Call History
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                View and manage AI call records from Retell
                            </p>
                        </div>

                        <PermissionGuard permissions={['SYNC_CALL_HISTORY']}>
                            <button
                                onClick={handleSync}
                                disabled={syncCallsFromRetell.isPending}
                                className="inline-flex items-center gap-2 rounded-xl border px-4 py-2.5 text-sm font-semibold transition-all hover:opacity-90 disabled:pointer-events-none disabled:opacity-50"
                                style={{
                                    borderColor: 'var(--border-default)',
                                    background: 'var(--bg-card)',
                                    color: 'var(--text-primary)',
                                }}
                            >
                                <RefreshCw size={16} className={syncCallsFromRetell.isPending ? 'animate-spin' : ''} />
                                Sync from Retell
                            </button>
                        </PermissionGuard>
                    </div>

                    <div className="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div className="flex flex-1 flex-wrap items-center gap-3">
                            <div
                                className="flex flex-1 items-center gap-3 rounded-xl border px-4 py-2.5"
                                style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                            >
                                <Search size={16} style={{ color: 'var(--text-muted)' }} />
                                <input
                                    type="text"
                                    placeholder="Search calls..."
                                    value={filters.search}
                                    onChange={(e) => handleSearchChange(e.target.value)}
                                    className="flex-1 bg-transparent text-sm outline-none placeholder:text-[var(--text-disabled)]"
                                    style={{ color: 'var(--text-primary)' }}
                                />
                            </div>

                            <div
                                className="flex items-center gap-3 rounded-xl border px-4 py-2.5"
                                style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                            >
                                <Filter size={16} style={{ color: 'var(--text-muted)' }} />
                                <select
                                    value={filters.status}
                                    onChange={(e) => handleFilterChange('status', e.target.value)}
                                    className="bg-transparent text-sm outline-none"
                                    style={{ color: 'var(--text-primary)' }}
                                >
                                    <option value="">All Status</option>
                                    <option value="registered">Registered</option>
                                    <option value="ongoing">Ongoing</option>
                                    <option value="ended">Ended</option>
                                    <option value="error">Error</option>
                                </select>
                            </div>

                            <div
                                className="flex items-center gap-3 rounded-xl border px-4 py-2.5"
                                style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                            >
                                <select
                                    value={filters.direction}
                                    onChange={(e) => handleFilterChange('direction', e.target.value)}
                                    className="bg-transparent text-sm outline-none"
                                    style={{ color: 'var(--text-primary)' }}
                                >
                                    <option value="">All Directions</option>
                                    <option value="inbound">Inbound</option>
                                    <option value="outbound">Outbound</option>
                                </select>
                            </div>

                            <div
                                className="flex items-center gap-3 rounded-xl border px-4 py-2.5"
                                style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                            >
                                <select
                                    value={filters.callType}
                                    onChange={(e) => handleFilterChange('callType', e.target.value)}
                                    className="bg-transparent text-sm outline-none"
                                    style={{ color: 'var(--text-primary)' }}
                                >
                                    <option value="">All Types</option>
                                    <option value="lead">Lead</option>
                                    <option value="appointment">Appointment</option>
                                    <option value="support">Support</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div className="hidden h-6 w-px sm:block" style={{ background: 'var(--border-subtle)' }} />

                            <ExportButton onExport={handleExport} isExporting={isPendingExport} />
                        </div>
                    </div>

                    {selectedActiveUuids.length > 0 && (
                        <PermissionGuard permissions={['DELETE_CALL_HISTORY']}>
                            <DataTableBulkActions
                                count={selectedActiveUuids.length}
                                onDelete={handleBulkDelete}
                                isDeleting={bulkDeleteCallHistory.isPending}
                            />
                        </PermissionGuard>
                    )}

                    <div
                        className="overflow-hidden rounded-2xl border shadow-xl"
                        style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                    >
                        <div className="overflow-x-auto">
                            <table className="w-full">
                                <thead style={{ background: 'var(--bg-secondary)' }}>
                                    {table.getHeaderGroups().map((headerGroup) => (
                                        <tr key={headerGroup.id}>
                                            {headerGroup.headers.map((header) => (
                                                <th
                                                    key={header.id}
                                                    className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider"
                                                    style={{ color: 'var(--text-muted)' }}
                                                >
                                                    {header.isPlaceholder
                                                        ? null
                                                        : flexRender(header.column.columnDef.header, header.getContext())}
                                                </th>
                                            ))}
                                        </tr>
                                    ))}
                                </thead>
                                <tbody className="divide-y" style={{ borderColor: 'var(--border-subtle)' }}>
                                    {isPending ? (
                                        <tr>
                                            <td colSpan={columns.length} className="px-4 py-8 text-center">
                                                <div className="flex items-center justify-center gap-2">
                                                    <div className="h-5 w-5 animate-spin rounded-full border-2 border-[var(--accent-primary)] border-t-transparent" />
                                                    <span style={{ color: 'var(--text-muted)' }}>Loading...</span>
                                                </div>
                                            </td>
                                        </tr>
                                    ) : isError ? (
                                        <tr>
                                            <td colSpan={columns.length} className="px-4 py-8 text-center">
                                                <span style={{ color: 'var(--error)' }}>Failed to load calls</span>
                                            </td>
                                        </tr>
                                    ) : calls.length === 0 ? (
                                        <tr>
                                            <td colSpan={columns.length} className="px-4 py-8 text-center">
                                                <span style={{ color: 'var(--text-muted)' }}>No calls found</span>
                                            </td>
                                        </tr>
                                    ) : (
                                        table.getRowModel().rows.map((row) => (
                                            <tr
                                                key={row.id}
                                                className={row.original.deletedAt ? 'opacity-60' : ''}
                                                style={{
                                                    background: row.original.deletedAt
                                                        ? 'var(--deleted-row-bg)'
                                                        : undefined,
                                                }}
                                            >
                                                {row.getVisibleCells().map((cell) => (
                                                    <td
                                                        key={cell.id}
                                                        className="whitespace-nowrap px-4 py-3"
                                                        style={{ color: 'var(--text-primary)' }}
                                                    >
                                                        {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                                    </td>
                                                ))}
                                            </tr>
                                        ))
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {meta.lastPage > 1 && (
                            <div
                                className="flex items-center justify-between border-t px-6 py-4"
                                style={{ borderColor: 'var(--border-subtle)' }}
                            >
                                <span
                                    className="text-xs font-semibold uppercase tracking-wider"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
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
                                        {paginationPages.map((page, index) =>
                                            page === '...' ? (
                                                <span
                                                    key={`ellipsis-${index}`}
                                                    className="px-2"
                                                    style={{ color: 'var(--text-muted)' }}
                                                >
                                                    ...
                                                </span>
                                            ) : (
                                                <button
                                                    key={`page-${page}`}
                                                    type="button"
                                                    onClick={() => goToPage(page as number)}
                                                    className="h-9 w-9 rounded-xl text-xs font-bold"
                                                    style={
                                                        meta.currentPage === page
                                                            ? { background: 'var(--accent-primary)', color: 'var(--text-primary)' }
                                                            : { color: 'var(--text-muted)' }
                                                    }
                                                >
                                                    {page}
                                                </button>
                                            )
                                        )}
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
                    entityLabel={`call ${pendingDelete?.callId.slice(0, 8) ?? ''}...`}
                    onConfirm={handleConfirmDelete}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteCallHistory.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="call"
                    entityName={pendingRestore?.callId}
                    onConfirm={handleConfirmRestore}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreCallHistory.isPending}
                />
            </AppLayout>
        </>
    );
}
