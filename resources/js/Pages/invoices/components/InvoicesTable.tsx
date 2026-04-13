import * as React from 'react';
import { Link } from '@inertiajs/react';
import {
    createColumnHelper,
    flexRender,
    getCoreRowModel,
    useReactTable,
    type RowSelectionState,
    type OnChangeFn,
} from '@tanstack/react-table';
import { Eye, Pencil, Trash2, CheckCircle } from 'lucide-react';
import { InvoiceStatusBadge } from '@/modules/invoices/components/InvoiceStatusBadge';
import { formatDateShort, formatCurrency } from '@/common/helpers/formatDate';
import type { InvoiceListItem } from '@/modules/invoices/types';

const columnHelper = createColumnHelper<InvoiceListItem>();

const COLUMNS = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => (
            <input
                type="checkbox"
                checked={table.getIsAllRowsSelected()}
                onChange={table.getToggleAllRowsSelectedHandler()}
                aria-label="Select all"
                style={{ accentColor: 'var(--accent-primary)', cursor: 'pointer', width: 14, height: 14 }}
            />
        ),
        cell: ({ row }) => (
            <input
                type="checkbox"
                checked={row.getIsSelected()}
                onChange={row.getToggleSelectedHandler()}
                disabled={!row.getCanSelect()}
                aria-label="Select row"
                style={{ accentColor: 'var(--accent-primary)', cursor: 'pointer', width: 14, height: 14 }}
            />
        ),
        size: 40,
    }),
    columnHelper.accessor('invoice_number', {
        header: 'Invoice #',
        cell: (info) => (
            <span style={{ fontWeight: 600, color: 'var(--text-primary)', fontFamily: 'var(--font-mono)', fontSize: 13 }}>
                {info.getValue()}
            </span>
        ),
    }),
    columnHelper.accessor('bill_to_name', {
        header: 'Bill To',
        cell: (info) => (
            <span style={{ color: 'var(--text-primary)', fontSize: 13 }}>{info.getValue()}</span>
        ),
    }),
    columnHelper.accessor('status', {
        header: 'Status',
        cell: (info) => <InvoiceStatusBadge status={info.getValue()} />,
    }),
    columnHelper.accessor('invoice_date', {
        header: 'Date',
        cell: (info) => (
            <span style={{ color: 'var(--text-secondary)', fontSize: 12 }}>
                {formatDateShort(info.getValue())}
            </span>
        ),
    }),
    columnHelper.accessor('items_count', {
        header: 'Items',
        cell: (info) => (
            <span
                style={{
                    display: 'inline-flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    minWidth: 24,
                    height: 24,
                    borderRadius: 6,
                    background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                    color: 'var(--accent-primary)',
                    fontSize: 11,
                    fontWeight: 700,
                }}
            >
                {info.getValue()}
            </span>
        ),
    }),
    columnHelper.accessor('balance_due', {
        header: 'Balance Due',
        cell: (info) => (
            <span style={{ color: 'var(--accent-success)', fontWeight: 700, fontSize: 13, fontFamily: 'var(--font-mono)' }}>
                {formatCurrency(info.getValue())}
            </span>
        ),
    }),
    columnHelper.accessor('claim_number', {
        header: 'Claim #',
        cell: (info) => (
            <span style={{ color: 'var(--text-secondary)', fontSize: 12 }}>
                {info.getValue() ?? <span style={{ color: 'var(--text-disabled)' }}>—</span>}
            </span>
        ),
    }),
    columnHelper.accessor('created_at', {
        header: 'Created',
        cell: (info) => (
            <span style={{ color: 'var(--text-muted)', fontSize: 12 }}>
                {formatDateShort(info.getValue())}
            </span>
        ),
    }),
];

interface InvoicesTableProps {
    data: InvoiceListItem[];
    isPending: boolean;
    onDeleteClick: (uuid: string, name: string) => void;
    onRestoreClick: (uuid: string, name: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

export default function InvoicesTable({
    data,
    isPending,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: InvoicesTableProps): React.JSX.Element {
    const table = useReactTable<InvoiceListItem>({
        data,
        columns: COLUMNS,
        getCoreRowModel: getCoreRowModel(),
        getRowId: (row) => row.uuid,
        enableRowSelection: true,
        state: { rowSelection },
        onRowSelectionChange,
    });

    const thStyle: React.CSSProperties = {
        padding: '10px 14px',
        textAlign: 'left',
        fontSize: 11,
        fontWeight: 700,
        fontFamily: 'var(--font-sans)',
        textTransform: 'uppercase',
        letterSpacing: '0.08em',
        color: 'var(--text-muted)',
        borderBottom: '1px solid var(--border-subtle)',
        background: 'var(--bg-surface)',
        whiteSpace: 'nowrap',
    };

    return (
        <div style={{ overflowX: 'auto', width: '100%' }}>
            <table style={{ width: '100%', borderCollapse: 'collapse' }}>
                <thead>
                    {table.getHeaderGroups().map((hg) => (
                        <tr key={hg.id}>
                            {hg.headers.map((header) => (
                                <th key={header.id} style={thStyle}>
                                    {flexRender(header.column.columnDef.header, header.getContext())}
                                </th>
                            ))}
                            <th style={{ ...thStyle, textAlign: 'right' }}>Actions</th>
                        </tr>
                    ))}
                </thead>
                <tbody>
                    {isPending ? (
                        Array.from({ length: 5 }).map((_, i) => (
                            <tr key={i}>
                                {Array.from({ length: COLUMNS.length + 1 }).map((__, j) => (
                                    <td key={j} style={{ padding: '12px 14px' }}>
                                        <div
                                            style={{
                                                height: 14,
                                                borderRadius: 4,
                                                background: 'var(--border-subtle)',
                                                animation: 'pulse 1.5s ease-in-out infinite',
                                                width: j === 0 ? 14 : '80%',
                                            }}
                                        />
                                    </td>
                                ))}
                            </tr>
                        ))
                    ) : data.length === 0 ? (
                        <tr>
                            <td
                                colSpan={COLUMNS.length + 1}
                                style={{ padding: 48, textAlign: 'center', color: 'var(--text-muted)', fontSize: 13, fontFamily: 'var(--font-sans)' }}
                            >
                                No invoices found.
                            </td>
                        </tr>
                    ) : (
                        table.getRowModel().rows.map((row) => {
                            const isDeleted = row.original.deleted_at !== null;
                            return (
                                <tr
                                    key={row.id}
                                    style={{
                                        background: isDeleted ? 'var(--deleted-row-bg)' : undefined,
                                        borderLeft: isDeleted ? '3px solid var(--deleted-row-border)' : undefined,
                                        opacity: isDeleted ? 'var(--deleted-row-opacity)' : undefined,
                                        transition: 'background 0.15s ease',
                                    }}
                                >
                                    {row.getVisibleCells().map((cell) => (
                                        <td
                                            key={cell.id}
                                            style={{
                                                padding: '10px 14px',
                                                borderBottom: '1px solid var(--border-subtle)',
                                                fontFamily: 'var(--font-sans)',
                                                verticalAlign: 'middle',
                                            }}
                                        >
                                            {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                        </td>
                                    ))}
                                    <td
                                        style={{
                                            padding: '10px 14px',
                                            borderBottom: '1px solid var(--border-subtle)',
                                            textAlign: 'right',
                                        }}
                                    >
                                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'flex-end', gap: 6 }}>
                                            <Link
                                                href={`/invoices/${row.original.uuid}`}
                                                style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: 30, height: 30, borderRadius: 6, color: 'var(--text-muted)', border: '1px solid var(--border-default)', background: 'var(--bg-elevated)' }}
                                                aria-label="View invoice"
                                                title="View"
                                            >
                                                <Eye size={13} />
                                            </Link>

                                            {!isDeleted ? (
                                                <>
                                                    <Link
                                                        href={`/invoices/${row.original.uuid}/edit`}
                                                        style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: 30, height: 30, borderRadius: 6, color: 'var(--accent-primary)', border: '1px solid color-mix(in srgb, var(--accent-primary) 30%, transparent)', background: 'color-mix(in srgb, var(--accent-primary) 8%, transparent)' }}
                                                        aria-label="Edit invoice"
                                                        title="Edit"
                                                    >
                                                        <Pencil size={13} />
                                                    </Link>
                                                    <button
                                                        onClick={() => onDeleteClick(row.original.uuid, row.original.invoice_number)}
                                                        style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: 30, height: 30, borderRadius: 6, color: 'var(--accent-error)', border: '1px solid color-mix(in srgb, var(--accent-error) 30%, transparent)', background: 'color-mix(in srgb, var(--accent-error) 8%, transparent)', cursor: 'pointer' }}
                                                        aria-label="Delete invoice"
                                                        title="Delete"
                                                    >
                                                        <Trash2 size={13} />
                                                    </button>
                                                </>
                                            ) : (
                                                <button
                                                    onClick={() => onRestoreClick(row.original.uuid, row.original.invoice_number)}
                                                    style={{ display: 'flex', alignItems: 'center', justifyContent: 'center', width: 30, height: 30, borderRadius: 6, color: 'var(--accent-success)', border: '1px solid color-mix(in srgb, var(--accent-success) 30%, transparent)', background: 'color-mix(in srgb, var(--accent-success) 8%, transparent)', cursor: 'pointer' }}
                                                    aria-label="Restore invoice"
                                                    title="Restore"
                                                >
                                                    <CheckCircle size={13} />
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            );
                        })
                    )}
                </tbody>
            </table>
        </div>
    );
}
