import * as React from 'react';
import {
    createColumnHelper,
    flexRender,
    getCoreRowModel,
    getSortedRowModel,
    useReactTable,
    type OnChangeFn,
    type RowSelectionState,
    type SortingState,
} from '@tanstack/react-table';
import { Eye, Pencil, Trash2, CheckCircle, ArrowUpDown, FileText, LayoutGrid, Images } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { motion } from 'framer-motion';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import type { ScopeSheetListItem } from '@/modules/scope-sheets/types';

// ── columnHelper OUTSIDE component (mandatory) ────────────────────────────────
const columnHelper = createColumnHelper<ScopeSheetListItem>();

function formatDateShort(dateStr: string | null): string {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

interface Props {
    data: ScopeSheetListItem[];
    isPending: boolean;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
    onDelete: (item: ScopeSheetListItem) => void;
    onRestore: (item: ScopeSheetListItem) => void;
}

export function ScopeSheetsTable({ data, isPending, rowSelection, onRowSelectionChange, onDelete, onRestore }: Props): React.JSX.Element {
    const [sorting, setSorting] = React.useState<SortingState>([]);

    const columns = React.useMemo(() => [
        columnHelper.display({
            id: 'select',
            header: ({ table }) => (
                <input
                    type="checkbox"
                    checked={table.getIsAllPageRowsSelected()}
                    ref={(el) => {
                        if (el) el.indeterminate = table.getIsSomePageRowsSelected();
                    }}
                    onChange={table.getToggleAllPageRowsSelectedHandler()}
                    aria-label="Select all rows"
                    style={{ accentColor: 'var(--accent-primary)', cursor: 'pointer' }}
                />
            ),
            cell: ({ row }) => (
                <input
                    type="checkbox"
                    checked={row.getIsSelected()}
                    onChange={row.getToggleSelectedHandler()}
                    aria-label={`Select scope sheet ${row.original.claim_number ?? row.original.claim_internal_id}`}
                    style={{ accentColor: 'var(--accent-primary)', cursor: 'pointer' }}
                />
            ),
            size: 40,
        }),

        columnHelper.accessor('claim_number', {
            id: 'claim_number',
            header: () => (
                <div style={{ display: 'flex', alignItems: 'center', gap: 4, justifyContent: 'center' }}>
                    <FileText size={13} /> Claim #
                </div>
            ),
            cell: ({ row }) => (
                <div style={{ textAlign: 'center', fontFamily: 'var(--font-mono)', fontSize: 12 }}>
                    <span style={{ color: 'var(--accent-primary)', fontWeight: 700 }}>
                        {row.original.claim_number ?? row.original.claim_internal_id ?? '—'}
                    </span>
                </div>
            ),
            enableSorting: true,
        }),

        columnHelper.accessor('generated_by_name', {
            id: 'generated_by',
            header: () => <div style={{ textAlign: 'center' }}>Inspector</div>,
            cell: ({ getValue }) => (
                <div style={{ textAlign: 'center', fontSize: 13, color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}>
                    {getValue() ?? '—'}
                </div>
            ),
        }),

        columnHelper.accessor('presentations_count', {
            id: 'presentations',
            header: () => (
                <div style={{ display: 'flex', alignItems: 'center', gap: 4, justifyContent: 'center' }}>
                    <Images size={13} /> Photos
                </div>
            ),
            cell: ({ getValue }) => (
                <div style={{ textAlign: 'center' }}>
                    <span
                        style={{
                            display: 'inline-flex',
                            alignItems: 'center',
                            gap: 4,
                            padding: '2px 8px',
                            borderRadius: 999,
                            fontSize: 11,
                            fontWeight: 700,
                            fontFamily: 'var(--font-sans)',
                            background: 'color-mix(in srgb, var(--accent-info) 12%, var(--bg-card))',
                            color: 'var(--accent-info)',
                            border: '1px solid color-mix(in srgb, var(--accent-info) 25%, transparent)',
                        }}
                    >
                        {getValue()}
                    </span>
                </div>
            ),
        }),

        columnHelper.accessor('zones_count', {
            id: 'zones',
            header: () => (
                <div style={{ display: 'flex', alignItems: 'center', gap: 4, justifyContent: 'center' }}>
                    <LayoutGrid size={13} /> Zones
                </div>
            ),
            cell: ({ getValue }) => (
                <div style={{ textAlign: 'center' }}>
                    <span
                        style={{
                            display: 'inline-flex',
                            alignItems: 'center',
                            gap: 4,
                            padding: '2px 8px',
                            borderRadius: 999,
                            fontSize: 11,
                            fontWeight: 700,
                            fontFamily: 'var(--font-sans)',
                            background: 'color-mix(in srgb, var(--accent-secondary) 12%, var(--bg-card))',
                            color: 'var(--accent-secondary)',
                            border: '1px solid color-mix(in srgb, var(--accent-secondary) 25%, transparent)',
                        }}
                    >
                        {getValue()}
                    </span>
                </div>
            ),
        }),

        columnHelper.accessor('status', {
            id: 'status',
            header: () => <div style={{ textAlign: 'center' }}>Status</div>,
            cell: ({ row }) => {
                const isDeleted = row.original.deleted_at !== null;
                return (
                    <div style={{ textAlign: 'center' }}>
                        <span
                            style={{
                                display: 'inline-flex',
                                alignItems: 'center',
                                gap: 5,
                                padding: '2px 10px',
                                borderRadius: 999,
                                fontSize: 11,
                                fontWeight: 700,
                                fontFamily: 'var(--font-sans)',
                                background: isDeleted
                                    ? 'color-mix(in srgb, var(--accent-error) 12%, var(--bg-card))'
                                    : 'color-mix(in srgb, var(--accent-success) 12%, var(--bg-card))',
                                color: isDeleted ? 'var(--accent-error)' : 'var(--accent-success)',
                                border: isDeleted
                                    ? '1px solid color-mix(in srgb, var(--accent-error) 30%, transparent)'
                                    : '1px solid color-mix(in srgb, var(--accent-success) 30%, transparent)',
                            }}
                        >
                            {isDeleted ? 'Deleted' : 'Active'}
                        </span>
                    </div>
                );
            },
        }),

        columnHelper.accessor('created_at', {
            id: 'created_at',
            header: () => (
                <div style={{ display: 'flex', alignItems: 'center', gap: 4, justifyContent: 'center' }}>
                    Created <ArrowUpDown size={11} />
                </div>
            ),
            cell: ({ getValue }) => (
                <div style={{ textAlign: 'center', fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                    {formatDateShort(getValue())}
                </div>
            ),
            enableSorting: true,
        }),

        columnHelper.display({
            id: 'actions',
            header: () => <div style={{ textAlign: 'center' }}>Actions</div>,
            cell: ({ row }) => {
                const item = row.original;
                const isDeleted = item.deleted_at !== null;
                return (
                    <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', gap: 6 }}>
                        <PermissionGuard permissions={['VIEW_SCOPE_SHEET']}>
                            <Link
                                href={`/scope-sheets/${item.uuid}`}
                                title="View scope sheet"
                                aria-label="View scope sheet"
                                style={iconBtnStyle('var(--accent-primary)')}
                            >
                                <Eye size={14} />
                            </Link>
                        </PermissionGuard>

                        {!isDeleted && (
                            <>
                                <PermissionGuard permissions={['UPDATE_SCOPE_SHEET']}>
                                    <Link
                                        href={`/scope-sheets/${item.uuid}/edit`}
                                        title="Edit scope sheet"
                                        aria-label="Edit scope sheet"
                                        style={iconBtnStyle('var(--accent-warning)')}
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                </PermissionGuard>

                                <PermissionGuard permissions={['DELETE_SCOPE_SHEET']}>
                                    <button
                                        type="button"
                                        title="Delete scope sheet"
                                        aria-label="Delete scope sheet"
                                        onClick={() => onDelete(item)}
                                        style={{ ...iconBtnStyle('var(--accent-error)'), border: 'none', cursor: 'pointer' }}
                                    >
                                        <Trash2 size={14} />
                                    </button>
                                </PermissionGuard>
                            </>
                        )}

                        {isDeleted && (
                            <PermissionGuard permissions={['RESTORE_SCOPE_SHEET']}>
                                <button
                                    type="button"
                                    title="Restore scope sheet"
                                    aria-label="Restore scope sheet"
                                    onClick={() => onRestore(item)}
                                    style={{ ...iconBtnStyle('var(--accent-success)'), border: 'none', cursor: 'pointer' }}
                                >
                                    <CheckCircle size={14} />
                                </button>
                            </PermissionGuard>
                        )}
                    </div>
                );
            },
        }),
    ], [onDelete, onRestore]);

    const table = useReactTable({
        data,
        columns,
        getRowId: (row) => row.uuid,
        state: { rowSelection, sorting },
        onRowSelectionChange,
        onSortingChange: setSorting,
        getCoreRowModel: getCoreRowModel(),
        getSortedRowModel: getSortedRowModel(),
        enableRowSelection: true,
    });

    if (isPending) {
        return (
            <div style={{ display: 'flex', flexDirection: 'column', gap: 8 }}>
                {Array.from({ length: 5 }).map((_, i) => (
                    <div
                        key={i}
                        style={{
                            height: 52,
                            borderRadius: 'var(--radius-md)',
                            background: 'var(--bg-elevated)',
                            opacity: 1 - i * 0.15,
                            animation: 'pulse 1.5s ease-in-out infinite',
                        }}
                    />
                ))}
            </div>
        );
    }

    if (data.length === 0) {
        return (
            <div
                style={{
                    display: 'flex',
                    flexDirection: 'column',
                    alignItems: 'center',
                    justifyContent: 'center',
                    padding: '60px 24px',
                    gap: 12,
                    background: 'var(--bg-card)',
                    border: '1px solid var(--border-default)',
                    borderRadius: 'var(--radius-lg)',
                }}
            >
                <div
                    style={{
                        width: 56,
                        height: 56,
                        borderRadius: '50%',
                        background: 'color-mix(in srgb, var(--accent-primary) 12%, var(--bg-elevated))',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        color: 'var(--accent-primary)',
                    }}
                >
                    <FileText size={24} />
                </div>
                <div style={{ fontSize: 15, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                    No scope sheets found
                </div>
                <div style={{ fontSize: 13, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                    Try adjusting your filters or create a new scope sheet.
                </div>
            </div>
        );
    }

    return (
        <div style={{ overflowX: 'auto', borderRadius: 'var(--radius-lg)', border: '1px solid var(--border-default)' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse', fontFamily: 'var(--font-sans)' }}>
                <thead>
                    {table.getHeaderGroups().map((hg) => (
                        <tr key={hg.id} style={{ borderBottom: '1px solid var(--border-default)', background: 'var(--bg-elevated)' }}>
                            {hg.headers.map((header) => (
                                <th
                                    key={header.id}
                                    style={{
                                        padding: '10px 12px',
                                        fontSize: 11,
                                        fontWeight: 700,
                                        color: 'var(--text-muted)',
                                        textTransform: 'uppercase',
                                        letterSpacing: '0.08em',
                                        textAlign: 'center',
                                        whiteSpace: 'nowrap',
                                        cursor: header.column.getCanSort() ? 'pointer' : 'default',
                                    }}
                                    onClick={header.column.getToggleSortingHandler()}
                                >
                                    {flexRender(header.column.columnDef.header, header.getContext())}
                                </th>
                            ))}
                        </tr>
                    ))}
                </thead>
                <tbody>
                    {table.getRowModel().rows.map((row, idx) => {
                        const isDeleted = row.original.deleted_at !== null;
                        return (
                            <motion.tr
                                key={row.id}
                                initial={{ opacity: 0, y: 6 }}
                                animate={{ opacity: 1, y: 0 }}
                                transition={{ duration: 0.2, delay: idx * 0.03 }}
                                style={{
                                    borderBottom: '1px solid var(--border-subtle)',
                                    background: isDeleted ? 'var(--deleted-row-bg)' : idx % 2 === 0 ? 'var(--bg-card)' : 'var(--bg-elevated)',
                                    opacity: isDeleted ? 'var(--deleted-row-opacity)' : 1,
                                    borderLeft: isDeleted ? '3px solid var(--deleted-row-border)' : '3px solid transparent',
                                    transition: 'background 0.15s ease',
                                }}
                            >
                                {row.getVisibleCells().map((cell) => (
                                    <td key={cell.id} style={{ padding: '10px 12px', textAlign: 'center' }}>
                                        {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                    </td>
                                ))}
                            </motion.tr>
                        );
                    })}
                </tbody>
            </table>
        </div>
    );
}

function iconBtnStyle(color: string): React.CSSProperties {
    return {
        display: 'inline-flex',
        alignItems: 'center',
        justifyContent: 'center',
        width: 30,
        height: 30,
        borderRadius: 'var(--radius-sm)',
        border: `1px solid color-mix(in srgb, ${color} 30%, transparent)`,
        background: `color-mix(in srgb, ${color} 10%, var(--bg-card))`,
        color,
        textDecoration: 'none',
        transition: 'all 0.15s ease',
        flexShrink: 0,
    };
}
