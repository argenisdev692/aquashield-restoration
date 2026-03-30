import * as React from 'react';
import {
    createColumnHelper,
    flexRender,
    getCoreRowModel,
    useReactTable,
    type RowSelectionState,
} from '@tanstack/react-table';
import { Link } from '@inertiajs/react';
import { Eye, Pencil, Trash2, RotateCcw, MapPin, User } from 'lucide-react';
import { useDeleteClaim, useRestoreClaim, useBulkDeleteClaims } from '@/modules/claims/hooks/useClaimMutations';
import type { ClaimListItem } from '@/modules/claims/types';

const columnHelper = createColumnHelper<ClaimListItem>();

interface DeleteConfirmState {
    open: boolean;
    uuid: string;
    isRestore: boolean;
}

interface ClaimsTableProps {
    data: ClaimListItem[];
    isLoading: boolean;
    currentPage: number;
    lastPage: number;
    total: number;
    onPageChange: (page: number) => void;
}

export function ClaimsTable({
    data, isLoading, currentPage, lastPage, total, onPageChange,
}: ClaimsTableProps): React.JSX.Element {
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [confirmState, setConfirmState] = React.useState<DeleteConfirmState>({
        open: false, uuid: '', isRestore: false,
    });
    const [bulkConfirm, setBulkConfirm] = React.useState(false);

    const deleteMutation  = useDeleteClaim();
    const restoreMutation = useRestoreClaim();
    const bulkDeleteMutation = useBulkDeleteClaims();

    const columns = React.useMemo(() => [
        columnHelper.display({
            id: 'select',
            header: ({ table }) => (
                <input
                    type="checkbox"
                    checked={table.getIsAllPageRowsSelected()}
                    onChange={table.getToggleAllPageRowsSelectedHandler()}
                    aria-label="Select all"
                    style={{ cursor: 'pointer', accentColor: 'var(--accent-primary)' }}
                />
            ),
            cell: ({ row }) => (
                <input
                    type="checkbox"
                    checked={row.getIsSelected()}
                    onChange={row.getToggleSelectedHandler()}
                    aria-label="Select row"
                    style={{ cursor: 'pointer', accentColor: 'var(--accent-primary)' }}
                />
            ),
            size: 40,
        }),
        columnHelper.accessor('claim_internal_id', {
            header: 'Claim ID',
            cell: (info) => (
                <span style={{ fontSize: 12, fontWeight: 700, color: 'var(--accent-primary)', fontFamily: 'var(--font-sans)', fontVariantNumeric: 'tabular-nums' }}>
                    {info.getValue()}
                </span>
            ),
        }),
        columnHelper.accessor('policy_number', {
            header: 'Policy #',
            cell: (info) => (
                <span style={{ fontSize: 13, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                    {info.getValue()}
                </span>
            ),
        }),
        columnHelper.accessor('property_address', {
            header: 'Property',
            cell: (info) => (
                <div style={{ display: 'flex', alignItems: 'flex-start', gap: 6 }}>
                    <MapPin size={12} style={{ color: 'var(--text-muted)', flexShrink: 0, marginTop: 2 }} />
                    <span style={{ fontSize: 12, color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)', maxWidth: 200, overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {info.getValue() ?? '—'}
                    </span>
                </div>
            ),
        }),
        columnHelper.accessor('customers', {
            header: 'Owner',
            cell: (info) => {
                const owner = info.getValue()[0];
                return owner ? (
                    <div style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                        <User size={12} style={{ color: 'var(--text-muted)', flexShrink: 0 }} />
                        <span style={{ fontSize: 12, color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}>
                            {owner.full_name}
                        </span>
                    </div>
                ) : <span style={{ color: 'var(--text-disabled)', fontSize: 12 }}>—</span>;
            },
        }),
        columnHelper.accessor('type_damage_name', {
            header: 'Damage Type',
            cell: (info) => (
                <span style={{ fontSize: 12, color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}>
                    {info.getValue() ?? '—'}
                </span>
            ),
        }),
        columnHelper.accessor('claim_status_name', {
            header: 'Status',
            cell: (info) => {
                const color = info.row.original.claim_status_color ?? 'var(--accent-primary)';
                return info.getValue() ? (
                    <span
                        style={{
                            padding: '3px 10px',
                            borderRadius: 999,
                            fontSize: 11,
                            fontWeight: 600,
                            fontFamily: 'var(--font-sans)',
                            background: `color-mix(in srgb, ${color} 15%, var(--bg-card))`,
                            color: color,
                            border: `1px solid color-mix(in srgb, ${color} 30%, transparent)`,
                            whiteSpace: 'nowrap',
                        }}
                    >
                        {info.getValue()}
                    </span>
                ) : null;
            },
        }),
        columnHelper.accessor('date_of_loss', {
            header: 'Loss Date',
            cell: (info) => (
                <span style={{ fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)', fontVariantNumeric: 'tabular-nums' }}>
                    {info.getValue() ? new Date(info.getValue()!).toLocaleDateString() : '—'}
                </span>
            ),
        }),
        columnHelper.accessor('created_at', {
            header: 'Created',
            cell: (info) => (
                <span style={{ fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)', fontVariantNumeric: 'tabular-nums' }}>
                    {new Date(info.getValue()).toLocaleDateString()}
                </span>
            ),
        }),
        columnHelper.display({
            id: 'actions',
            header: () => <span style={{ fontSize: 11, color: 'var(--text-muted)' }}>Actions</span>,
            cell: ({ row }) => {
                const { uuid, deleted_at } = row.original;
                const isDeleted = deleted_at !== null;
                return (
                    <div style={{ display: 'flex', alignItems: 'center', gap: 4 }}>
                        <Link
                            href={`/claims/${uuid}`}
                            aria-label="View claim"
                            style={{
                                width: 28, height: 28, display: 'flex', alignItems: 'center', justifyContent: 'center',
                                borderRadius: 'var(--radius-sm)', border: '1px solid var(--border-default)',
                                background: 'transparent', color: 'var(--text-secondary)', textDecoration: 'none',
                                transition: 'all 0.15s ease',
                            }}
                        >
                            <Eye size={13} />
                        </Link>
                        {!isDeleted && (
                            <Link
                                href={`/claims/${uuid}/edit`}
                                aria-label="Edit claim"
                                style={{
                                    width: 28, height: 28, display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    borderRadius: 'var(--radius-sm)', border: '1px solid var(--border-default)',
                                    background: 'transparent', color: 'var(--accent-primary)', textDecoration: 'none',
                                    transition: 'all 0.15s ease',
                                }}
                            >
                                <Pencil size={13} />
                            </Link>
                        )}
                        {isDeleted ? (
                            <button
                                type="button"
                                onClick={() => setConfirmState({ open: true, uuid, isRestore: true })}
                                aria-label="Restore claim"
                                style={{
                                    width: 28, height: 28, display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    borderRadius: 'var(--radius-sm)', border: '1px solid var(--border-default)',
                                    background: 'transparent', color: 'var(--accent-success)', cursor: 'pointer',
                                }}
                            >
                                <RotateCcw size={13} />
                            </button>
                        ) : (
                            <button
                                type="button"
                                onClick={() => setConfirmState({ open: true, uuid, isRestore: false })}
                                aria-label="Delete claim"
                                style={{
                                    width: 28, height: 28, display: 'flex', alignItems: 'center', justifyContent: 'center',
                                    borderRadius: 'var(--radius-sm)', border: '1px solid var(--border-default)',
                                    background: 'transparent', color: 'var(--accent-error)', cursor: 'pointer',
                                }}
                            >
                                <Trash2 size={13} />
                            </button>
                        )}
                    </div>
                );
            },
        }),
    ], []);

    const table = useReactTable({
        data,
        columns,
        state: { rowSelection },
        onRowSelectionChange: setRowSelection,
        getCoreRowModel: getCoreRowModel(),
        getRowId: (row) => row.uuid,
    });

    const selectedUuids = Object.keys(rowSelection).filter((k) => rowSelection[k]);

    async function handleConfirm(): Promise<void> {
        if (confirmState.isRestore) {
            await restoreMutation.mutateAsync(confirmState.uuid);
        } else {
            await deleteMutation.mutateAsync(confirmState.uuid);
        }
        setConfirmState({ open: false, uuid: '', isRestore: false });
        setRowSelection({});
    }

    async function handleBulkDelete(): Promise<void> {
        await bulkDeleteMutation.mutateAsync(selectedUuids);
        setBulkConfirm(false);
        setRowSelection({});
    }

    const pages = Array.from({ length: lastPage }, (_, i) => i + 1);
    const visiblePages = pages.filter(
        (p) => p === 1 || p === lastPage || Math.abs(p - currentPage) <= 2,
    );

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
            {/* Bulk actions */}
            {selectedUuids.length > 0 && (
                <div
                    style={{
                        display: 'flex', alignItems: 'center', gap: 12, padding: '8px 14px',
                        background: 'color-mix(in srgb, var(--accent-error) 8%, var(--bg-card))',
                        border: '1px solid color-mix(in srgb, var(--accent-error) 25%, transparent)',
                        borderRadius: 'var(--radius-md)',
                    }}
                >
                    <span style={{ fontSize: 13, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                        {selectedUuids.length} selected
                    </span>
                    <button
                        type="button"
                        onClick={() => setBulkConfirm(true)}
                        style={{
                            display: 'flex', alignItems: 'center', gap: 6,
                            padding: '5px 12px', borderRadius: 'var(--radius-md)',
                            border: 'none', background: 'var(--accent-error)', color: '#fff',
                            fontSize: 12, fontFamily: 'var(--font-sans)', cursor: 'pointer', fontWeight: 600,
                        }}
                    >
                        <Trash2 size={12} /> Delete Selected
                    </button>
                </div>
            )}

            {/* Table */}
            <div style={{ overflowX: 'auto', borderRadius: 'var(--radius-lg)', border: '1px solid var(--border-default)' }}>
                <table style={{ width: '100%', borderCollapse: 'collapse', fontFamily: 'var(--font-sans)' }}>
                    <thead>
                        {table.getHeaderGroups().map((hg) => (
                            <tr key={hg.id} style={{ background: 'var(--bg-elevated)' }}>
                                {hg.headers.map((header) => (
                                    <th
                                        key={header.id}
                                        style={{
                                            padding: '10px 12px',
                                            textAlign: 'left',
                                            fontSize: 11,
                                            fontWeight: 700,
                                            color: 'var(--text-muted)',
                                            textTransform: 'uppercase',
                                            letterSpacing: '0.08em',
                                            borderBottom: '1px solid var(--border-default)',
                                            whiteSpace: 'nowrap',
                                        }}
                                    >
                                        {header.isPlaceholder ? null : flexRender(header.column.columnDef.header, header.getContext())}
                                    </th>
                                ))}
                            </tr>
                        ))}
                    </thead>
                    <tbody>
                        {isLoading ? (
                            <tr>
                                <td colSpan={columns.length} style={{ padding: '32px', textAlign: 'center', color: 'var(--text-muted)', fontSize: 13 }}>
                                    Loading claims...
                                </td>
                            </tr>
                        ) : data.length === 0 ? (
                            <tr>
                                <td colSpan={columns.length} style={{ padding: '32px', textAlign: 'center', color: 'var(--text-muted)', fontSize: 13 }}>
                                    No claims found.
                                </td>
                            </tr>
                        ) : (
                            table.getRowModel().rows.map((row) => {
                                const isDeleted = row.original.deleted_at !== null;
                                return (
                                    <tr
                                        key={row.id}
                                        style={{
                                            background: isDeleted ? 'var(--deleted-row-bg)' : 'var(--bg-surface)',
                                            borderBottom: '1px solid var(--border-subtle)',
                                            opacity: isDeleted ? 'var(--deleted-row-opacity)' : 1,
                                            transition: 'background 0.15s ease',
                                        }}
                                    >
                                        {row.getVisibleCells().map((cell) => (
                                            <td key={cell.id} style={{ padding: '10px 12px', verticalAlign: 'middle' }}>
                                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                            </td>
                                        ))}
                                    </tr>
                                );
                            })
                        )}
                    </tbody>
                </table>
            </div>

            {/* Pagination */}
            {lastPage > 1 && (
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', padding: '0 4px' }}>
                    <span style={{ fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                        {total} total records
                    </span>
                    <div style={{ display: 'flex', gap: 4 }}>
                        {visiblePages.map((p, idx) => {
                            const prev = visiblePages[idx - 1];
                            const showEllipsis = prev !== undefined && p - prev > 1;
                            return (
                                <React.Fragment key={p}>
                                    {showEllipsis && (
                                        <span style={{ padding: '4px 8px', color: 'var(--text-muted)', fontSize: 13 }}>…</span>
                                    )}
                                    <button
                                        type="button"
                                        onClick={() => onPageChange(p)}
                                        aria-label={`Page ${p}`}
                                        aria-current={p === currentPage ? 'page' : undefined}
                                        style={{
                                            width: 32, height: 32,
                                            borderRadius: 'var(--radius-sm)',
                                            border: `1px solid ${p === currentPage ? 'var(--accent-primary)' : 'var(--border-default)'}`,
                                            background: p === currentPage
                                                ? 'color-mix(in srgb, var(--accent-primary) 15%, var(--bg-card))'
                                                : 'var(--bg-card)',
                                            color: p === currentPage ? 'var(--accent-primary)' : 'var(--text-secondary)',
                                            fontSize: 12,
                                            fontFamily: 'var(--font-sans)',
                                            fontWeight: p === currentPage ? 700 : 400,
                                            cursor: 'pointer',
                                            transition: 'all 0.15s ease',
                                        }}
                                    >
                                        {p}
                                    </button>
                                </React.Fragment>
                            );
                        })}
                    </div>
                </div>
            )}

            {/* Delete / Restore confirm modal */}
            {confirmState.open && (
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-label={confirmState.isRestore ? 'Confirm restore' : 'Confirm delete'}
                    style={{
                        position: 'fixed', inset: 0, zIndex: 200,
                        background: 'rgba(0,0,0,0.6)',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                    }}
                >
                    <div style={{
                        background: 'var(--bg-elevated)', borderRadius: 'var(--radius-lg)',
                        border: '1px solid var(--border-default)', padding: '24px 28px',
                        maxWidth: 400, width: '90%', boxShadow: '0 16px 48px rgba(0,0,0,0.4)',
                    }}>
                        <h3 style={{ margin: '0 0 8px', fontSize: 16, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                            {confirmState.isRestore ? 'Restore Claim?' : 'Delete Claim?'}
                        </h3>
                        <p style={{ margin: '0 0 20px', fontSize: 13, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                            {confirmState.isRestore
                                ? 'This claim will be restored and become active again.'
                                : 'This claim will be soft-deleted and can be restored later.'}
                        </p>
                        <div style={{ display: 'flex', gap: 10, justifyContent: 'flex-end' }}>
                            <button
                                type="button"
                                onClick={() => setConfirmState({ open: false, uuid: '', isRestore: false })}
                                style={{
                                    padding: '8px 16px', borderRadius: 'var(--radius-md)',
                                    border: '1px solid var(--border-default)', background: 'transparent',
                                    color: 'var(--text-secondary)', fontSize: 13,
                                    fontFamily: 'var(--font-sans)', cursor: 'pointer',
                                }}
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                onClick={() => void handleConfirm()}
                                style={{
                                    padding: '8px 16px', borderRadius: 'var(--radius-md)',
                                    border: 'none',
                                    background: confirmState.isRestore ? 'var(--accent-success)' : 'var(--accent-error)',
                                    color: '#fff', fontSize: 13,
                                    fontFamily: 'var(--font-sans)', cursor: 'pointer', fontWeight: 600,
                                }}
                            >
                                {confirmState.isRestore ? 'Restore' : 'Delete'}
                            </button>
                        </div>
                    </div>
                </div>
            )}

            {/* Bulk delete confirm */}
            {bulkConfirm && (
                <div
                    role="dialog"
                    aria-modal="true"
                    aria-label="Confirm bulk delete"
                    style={{
                        position: 'fixed', inset: 0, zIndex: 200,
                        background: 'rgba(0,0,0,0.6)',
                        display: 'flex', alignItems: 'center', justifyContent: 'center',
                    }}
                >
                    <div style={{
                        background: 'var(--bg-elevated)', borderRadius: 'var(--radius-lg)',
                        border: '1px solid var(--border-default)', padding: '24px 28px',
                        maxWidth: 400, width: '90%',
                    }}>
                        <h3 style={{ margin: '0 0 8px', fontSize: 16, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                            Delete {selectedUuids.length} Claims?
                        </h3>
                        <p style={{ margin: '0 0 20px', fontSize: 13, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                            All selected claims will be soft-deleted and can be restored later.
                        </p>
                        <div style={{ display: 'flex', gap: 10, justifyContent: 'flex-end' }}>
                            <button type="button" onClick={() => setBulkConfirm(false)}
                                style={{ padding: '8px 16px', borderRadius: 'var(--radius-md)', border: '1px solid var(--border-default)', background: 'transparent', color: 'var(--text-secondary)', fontSize: 13, fontFamily: 'var(--font-sans)', cursor: 'pointer' }}>
                                Cancel
                            </button>
                            <button type="button" onClick={() => void handleBulkDelete()}
                                style={{ padding: '8px 16px', borderRadius: 'var(--radius-md)', border: 'none', background: 'var(--accent-error)', color: '#fff', fontSize: 13, fontFamily: 'var(--font-sans)', cursor: 'pointer', fontWeight: 600 }}>
                                Delete All
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
