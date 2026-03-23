import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import { ChevronLeft, ChevronRight, Plus, Search, X } from 'lucide-react';
import { ExportButton } from '@/common/export/ExportButton';
import { DataTableDateRangeFilter } from '@/common/data-table/DataTableDateRangeFilter';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useDocumentTemplates } from '@/modules/document-templates/hooks/useDocumentTemplates';
import { DOCUMENT_TEMPLATE_TYPES } from '@/modules/document-templates/types';
import type { DocumentTemplateFilters } from '@/modules/document-templates/types';
import AppLayout from '@/pages/layouts/AppLayout';
import { buildDocumentTemplateQueryParams } from './helpers/buildDocumentTemplateQueryParams';
import DocumentTemplatesTable from './components/DocumentTemplatesTable';

export default function DocumentTemplatesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<DocumentTemplateFilters>(
        { page: 1, per_page: 15 },
        'document-templates-filters',
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [, startTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending } = useDocumentTemplates(filters);

    const items = data?.data ?? [];
    const meta = data?.meta ?? {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    };

    function handleSearchChange(e: React.ChangeEvent<HTMLInputElement>): void {
        const value = e.target.value;
        setSearch(value);
        startTransition(() => {
            setFilters((prev) => ({
                ...prev,
                search: value === '' ? undefined : value,
                page: 1,
            }));
        });
    }

    function handleTypeFilter(value: string): void {
        startTransition(() => {
            setFilters((prev) => ({
                ...prev,
                template_type: value === '' ? undefined : value,
                page: 1,
            }));
        });
    }

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = buildDocumentTemplateQueryParams(filters);
            params.append('format', format);
            window.open(
                `/document-templates/data/admin/export?${params.toString()}`,
                '_blank',
            );
        });
    }

    function goToPage(page: number): void {
        setFilters((prev) => ({ ...prev, page }));
    }

    const currentPage = meta.current_page;
    const lastPage = meta.last_page;

    function buildSlidingPages(): number[] {
        const delta = 2;
        const range: number[] = [];
        for (
            let i = Math.max(1, currentPage - delta);
            i <= Math.min(lastPage, currentPage + delta);
            i++
        ) {
            range.push(i);
        }
        return range;
    }

    return (
        <>
            <Head title="Document Templates" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    {/* Header */}
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <h1
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: 'var(--text-primary)' }}
                            >
                                Document Templates
                            </h1>
                            <p
                                className="text-sm font-medium"
                                style={{ color: 'var(--text-muted)' }}
                            >
                                {meta.total}{' '}
                                {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>

                        <PermissionGuard permissions={['CREATE_DOCUMENT_TEMPLATE']}>
                            <Link
                                href="/document-templates/create"
                                prefetch
                                className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                <Plus size={16} />
                                <span>New Template</span>
                            </Link>
                        </PermissionGuard>
                    </div>

                    {/* Filters */}
                    <div
                        className="card flex flex-col gap-4"
                        style={{ fontFamily: 'var(--font-sans)' }}
                    >
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center">
                            {/* Search */}
                            <div
                                className="flex flex-1 items-center gap-3 rounded-xl px-4 py-3"
                                style={{
                                    border: '1px solid var(--border-default)',
                                    background: 'var(--bg-surface)',
                                }}
                            >
                                <Search
                                    size={16}
                                    style={{ color: 'var(--text-muted)', flexShrink: 0 }}
                                />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={handleSearchChange}
                                    placeholder="Search templates…"
                                    className="w-full bg-transparent text-sm outline-none"
                                    style={{
                                        color: 'var(--text-primary)',
                                        fontFamily: 'var(--font-sans)',
                                    }}
                                />
                                {search !== '' && (
                                    <button
                                        type="button"
                                        aria-label="Clear search"
                                        onClick={() => {
                                            setSearch('');
                                            startTransition(() => {
                                                setFilters((prev) => ({
                                                    ...prev,
                                                    search: undefined,
                                                    page: 1,
                                                }));
                                            });
                                        }}
                                    >
                                        <X size={14} style={{ color: 'var(--text-muted)' }} />
                                    </button>
                                )}
                            </div>

                            <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                                {/* Type filter */}
                                <select
                                    value={filters.template_type ?? ''}
                                    onChange={(e) => handleTypeFilter(e.target.value)}
                                    style={{
                                        height: '40px',
                                        padding: '0 12px',
                                        fontSize: '13px',
                                        background: 'var(--bg-surface)',
                                        border: '1px solid var(--border-default)',
                                        borderRadius: 'var(--radius-md)',
                                        color: 'var(--text-primary)',
                                        fontFamily: 'var(--font-sans)',
                                        colorScheme: 'dark',
                                        outline: 'none',
                                        minWidth: '140px',
                                    }}
                                    aria-label="Filter by type"
                                >
                                    <option value="">All Types</option>
                                    {DOCUMENT_TEMPLATE_TYPES.map((t) => (
                                        <option key={t.value} value={t.value}>
                                            {t.label}
                                        </option>
                                    ))}
                                </select>

                                <DataTableDateRangeFilter
                                    dateFrom={filters.date_from}
                                    dateTo={filters.date_to}
                                    onChange={(range) =>
                                        setFilters((prev) => ({
                                            ...prev,
                                            date_from: range.dateFrom,
                                            date_to: range.dateTo,
                                            page: 1,
                                        }))
                                    }
                                />

                                <div
                                    className="hidden h-8 w-px sm:block"
                                    style={{ background: 'var(--border-subtle)' }}
                                />

                                <PermissionGuard permissions={['READ_DOCUMENT_TEMPLATE']}>
                                    <ExportButton
                                        onExport={handleExport}
                                        isExporting={isPendingExport}
                                    />
                                </PermissionGuard>
                            </div>
                        </div>
                    </div>

                    {/* Table */}
                    <div className="card overflow-hidden p-0">
                        <DocumentTemplatesTable
                            data={items}
                            isPending={isPending}
                        />
                    </div>

                    {/* Sliding Paginator */}
                    {lastPage > 1 ? (
                        <div className="flex items-center justify-between px-2">
                            <p
                                className="text-sm"
                                style={{
                                    color: 'var(--text-muted)',
                                    fontFamily: 'var(--font-sans)',
                                }}
                            >
                                Page {currentPage} of {lastPage}
                            </p>
                            <div className="flex items-center gap-1">
                                <button
                                    type="button"
                                    onClick={() => goToPage(currentPage - 1)}
                                    disabled={currentPage === 1}
                                    className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-50"
                                    aria-label="Previous page"
                                >
                                    <ChevronLeft size={16} />
                                </button>

                                {currentPage > 3 && (
                                    <>
                                        <button
                                            type="button"
                                            onClick={() => goToPage(1)}
                                            className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 text-sm"
                                        >
                                            1
                                        </button>
                                        {currentPage > 4 && (
                                            <span
                                                className="px-1 text-sm"
                                                style={{ color: 'var(--text-muted)' }}
                                            >
                                                …
                                            </span>
                                        )}
                                    </>
                                )}

                                {buildSlidingPages().map((p) => (
                                    <button
                                        key={p}
                                        type="button"
                                        onClick={() => goToPage(p)}
                                        className="inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 text-sm font-medium transition-colors"
                                        style={{
                                            background:
                                                p === currentPage
                                                    ? 'var(--accent-primary)'
                                                    : 'transparent',
                                            color:
                                                p === currentPage
                                                    ? 'var(--color-white)'
                                                    : 'var(--text-muted)',
                                            border:
                                                p === currentPage
                                                    ? 'none'
                                                    : '1px solid var(--border-default)',
                                            fontFamily: 'var(--font-sans)',
                                        }}
                                        aria-current={p === currentPage ? 'page' : undefined}
                                    >
                                        {p}
                                    </button>
                                ))}

                                {currentPage < lastPage - 2 && (
                                    <>
                                        {currentPage < lastPage - 3 && (
                                            <span
                                                className="px-1 text-sm"
                                                style={{ color: 'var(--text-muted)' }}
                                            >
                                                …
                                            </span>
                                        )}
                                        <button
                                            type="button"
                                            onClick={() => goToPage(lastPage)}
                                            className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 text-sm"
                                        >
                                            {lastPage}
                                        </button>
                                    </>
                                )}

                                <button
                                    type="button"
                                    onClick={() => goToPage(currentPage + 1)}
                                    disabled={currentPage === lastPage}
                                    className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-50"
                                    aria-label="Next page"
                                >
                                    <ChevronRight size={16} />
                                </button>
                            </div>
                        </div>
                    ) : null}
                </div>
            </AppLayout>
        </>
    );
}
