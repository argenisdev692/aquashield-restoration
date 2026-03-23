import * as React from 'react';
import {
    createColumnHelper,
    flexRender,
    getCoreRowModel,
    useReactTable,
} from '@tanstack/react-table';
import { Eye, Pencil, FileText } from 'lucide-react';
import { Link } from '@inertiajs/react';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import DocumentTemplateTypeBadge from '@/modules/document-templates/components/DocumentTemplateTypeBadge';
import type { DocumentTemplate } from '@/modules/document-templates/types';

const columnHelper = createColumnHelper<DocumentTemplate>();

interface DocumentTemplatesTableProps {
    data: DocumentTemplate[];
    isPending: boolean;
}

const thStyle: React.CSSProperties = {
    padding: '10px 16px',
    textAlign: 'left',
    fontSize: '11px',
    fontWeight: 600,
    textTransform: 'uppercase',
    letterSpacing: '0.08em',
    color: 'var(--text-muted)',
    fontFamily: 'var(--font-sans)',
    borderBottom: '1px solid var(--border-subtle)',
    whiteSpace: 'nowrap',
};

const tdStyle: React.CSSProperties = {
    padding: '12px 16px',
    fontSize: '13px',
    color: 'var(--text-primary)',
    fontFamily: 'var(--font-sans)',
    verticalAlign: 'middle',
};

const COLUMNS = [
    columnHelper.accessor('template_name', {
        header: 'Template Name',
        cell: (info) => (
            <span style={{ fontWeight: 600, color: 'var(--text-primary)' }}>
                {info.getValue()}
            </span>
        ),
    }),
    columnHelper.accessor('template_type', {
        header: 'Type',
        cell: (info) => <DocumentTemplateTypeBadge type={info.getValue()} />,
    }),
    columnHelper.accessor('template_description', {
        header: 'Description',
        cell: (info) => {
            const val = info.getValue();
            return val ? (
                <span
                    style={{
                        color: 'var(--text-secondary)',
                        maxWidth: '240px',
                        display: 'block',
                        overflow: 'hidden',
                        textOverflow: 'ellipsis',
                        whiteSpace: 'nowrap',
                    }}
                    title={val}
                >
                    {val}
                </span>
            ) : (
                <span style={{ color: 'var(--text-disabled)' }}>—</span>
            );
        },
    }),
    columnHelper.accessor('uploaded_by_name', {
        header: 'Uploaded By',
        cell: (info) => (
            <span style={{ color: 'var(--text-secondary)' }}>
                {info.getValue() ?? '—'}
            </span>
        ),
    }),
    columnHelper.accessor('created_at', {
        header: 'Created',
        cell: (info) => (
            <span style={{ color: 'var(--text-muted)' }}>
                {new Date(info.getValue()).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric',
                })}
            </span>
        ),
    }),
    columnHelper.display({
        id: 'actions',
        header: 'Actions',
        cell: ({ row }) => (
            <div className="flex items-center gap-1">
                <PermissionGuard permissions={['READ_DOCUMENT_TEMPLATE']}>
                    <Link
                        href={`/document-templates/${row.original.uuid}`}
                        prefetch
                        className="btn-action btn-action-view"
                        title="View"
                        aria-label={`View ${row.original.template_name}`}
                    >
                        <Eye size={14} />
                    </Link>
                </PermissionGuard>
                <PermissionGuard permissions={['UPDATE_DOCUMENT_TEMPLATE']}>
                    <Link
                        href={`/document-templates/${row.original.uuid}/edit`}
                        prefetch
                        className="btn-action btn-action-edit"
                        title="Edit"
                        aria-label={`Edit ${row.original.template_name}`}
                    >
                        <Pencil size={14} />
                    </Link>
                </PermissionGuard>
            </div>
        ),
    }),
];

export default function DocumentTemplatesTable({
    data,
    isPending,
}: DocumentTemplatesTableProps): React.JSX.Element {
    const table = useReactTable({
        data,
        columns: COLUMNS,
        getRowId: (row) => row.uuid,
        getCoreRowModel: getCoreRowModel(),
    });

    if (isPending) {
        return (
            <div className="flex flex-col">
                {Array.from({ length: 6 }).map((_, i) => (
                    <div
                        key={i}
                        className="flex items-center gap-4 px-4 py-4"
                        style={{
                            borderBottom: '1px solid var(--border-subtle)',
                            opacity: 1 - i * 0.12,
                        }}
                    >
                        <div className="flex flex-1 flex-col gap-2">
                            <div
                                className="h-3 animate-pulse rounded"
                                style={{
                                    background: 'var(--bg-hover)',
                                    width: `${60 + (i % 3) * 15}%`,
                                }}
                            />
                            <div
                                className="h-3 animate-pulse rounded"
                                style={{
                                    background: 'var(--bg-hover)',
                                    width: `${30 + (i % 4) * 10}%`,
                                }}
                            />
                        </div>
                    </div>
                ))}
            </div>
        );
    }

    if (data.length === 0) {
        return (
            <div
                className="flex flex-col items-center justify-center py-20 text-center"
                style={{ color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}
            >
                <FileText
                    size={40}
                    style={{ color: 'var(--border-default)', marginBottom: '12px' }}
                />
                <p className="text-sm font-medium">No document templates found</p>
                <p
                    className="mt-1 text-xs"
                    style={{ color: 'var(--text-disabled)' }}
                >
                    Try adjusting your search or filters
                </p>
            </div>
        );
    }

    return (
        <div className="w-full overflow-x-auto">
            <table className="w-full border-collapse">
                <thead style={{ background: 'var(--bg-surface)' }}>
                    {table.getHeaderGroups().map((hg) => (
                        <tr key={hg.id}>
                            {hg.headers.map((header) => (
                                <th key={header.id} style={thStyle}>
                                    {header.isPlaceholder
                                        ? null
                                        : flexRender(
                                              header.column.columnDef.header,
                                              header.getContext(),
                                          )}
                                </th>
                            ))}
                        </tr>
                    ))}
                </thead>
                <tbody>
                    {table.getRowModel().rows.map((row, idx) => (
                        <tr
                            key={row.id}
                            style={{
                                background:
                                    idx % 2 === 0
                                        ? 'var(--bg-card)'
                                        : 'var(--bg-surface)',
                                borderBottom: '1px solid var(--border-subtle)',
                                transition: 'background var(--transition)',
                            }}
                        >
                            {row.getVisibleCells().map((cell) => (
                                <td key={cell.id} style={tdStyle}>
                                    {flexRender(
                                        cell.column.columnDef.cell,
                                        cell.getContext(),
                                    )}
                                </td>
                            ))}
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
