import * as React from 'react';
import { Search, SlidersHorizontal, X } from 'lucide-react';
import { DataTableDateRangeFilter } from '@/common/data-table/DataTableDateRangeFilter';

export interface CrudFilterOption {
    value: string;
    label: string;
}

export interface CrudFilterSelect {
    value: string;
    onChange: (value: string) => void;
    options: readonly CrudFilterOption[];
    ariaLabel: string;
    label?: string;
    minWidth?: number;
}

interface CrudFilterBarProps {
    searchValue?: string;
    searchPlaceholder?: string;
    searchAriaLabel?: string;
    onSearchChange?: (value: string) => void;
    statusValue?: string;
    statusOptions?: readonly CrudFilterOption[];
    statusAriaLabel?: string;
    onStatusChange?: (value: string) => void;
    dateFrom?: string;
    dateTo?: string;
    onDateRangeChange?: (range: { dateFrom?: string; dateTo?: string }) => void;
    selects?: readonly CrudFilterSelect[];
    actions?: React.ReactNode;
    children?: React.ReactNode;
    hasActiveFilters?: boolean;
    onReset?: () => void;
    className?: string;
}

const defaultStatusOptions: readonly CrudFilterOption[] = [
    { value: '', label: 'All Status' },
    { value: 'active', label: 'Active' },
    { value: 'deleted', label: 'Deleted' },
];

const controlStyle: React.CSSProperties = {
    minHeight: 40,
    border: '1px solid var(--border-default)',
    background: 'var(--bg-surface)',
    color: 'var(--text-primary)',
    borderRadius: 'var(--radius-lg)',
    fontFamily: 'var(--font-sans)',
    colorScheme: 'dark',
};

function CrudFilterSeparator(): React.JSX.Element {
    return (
        <div
            aria-hidden="true"
            className="hidden h-8 w-px shrink-0 sm:block"
            style={{ background: 'var(--border-subtle)' }}
        />
    );
}

function CrudFilterSelectControl({ select }: { select: CrudFilterSelect }): React.JSX.Element {
    return (
        <div className="flex flex-col gap-1.5" style={{ minWidth: select.minWidth ?? 144 }}>
            {select.label !== undefined ? (
                <label className="text-[11px] font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-secondary)' }}>
                    {select.label}
                </label>
            ) : null}
            <select
                value={select.value}
                onChange={(event) => select.onChange(event.target.value)}
                aria-label={select.ariaLabel}
                className="h-10 w-full cursor-pointer px-3 text-sm font-medium outline-none transition-colors"
                style={controlStyle}
            >
                {select.options.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </div>
    );
}

export function CrudFilterBar({
    searchValue,
    searchPlaceholder = 'Search records...',
    searchAriaLabel = 'Search records',
    onSearchChange,
    statusValue,
    statusOptions = defaultStatusOptions,
    statusAriaLabel = 'Filter by status',
    onStatusChange,
    dateFrom,
    dateTo,
    onDateRangeChange,
    selects = [],
    actions,
    children,
    hasActiveFilters,
    onReset,
    className = '',
}: CrudFilterBarProps): React.JSX.Element {
    const showSearch = onSearchChange !== undefined;
    const showStatus = onStatusChange !== undefined;
    const showDateRange = onDateRangeChange !== undefined;
    const showSecondaryControls = showStatus || selects.length > 0 || showDateRange || children !== undefined || actions !== undefined || onReset !== undefined;

    return (
        <div
            className={`flex flex-col gap-4 rounded-2xl border px-4 py-4 shadow-sm sm:px-5 lg:flex-row lg:items-end lg:justify-between ${className}`.trim()}
            style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)', fontFamily: 'var(--font-sans)' }}
        >
            {showSearch && onSearchChange !== undefined ? (
                <div className="flex min-h-10 w-full min-w-0 flex-1 items-center gap-3 rounded-xl border px-4 py-2.5 transition-colors lg:max-w-xl" style={controlStyle}>
                    <Search size={16} style={{ color: 'var(--text-secondary)', flexShrink: 0 }} />
                    <input
                        type="search"
                        value={searchValue ?? ''}
                        onChange={(event) => onSearchChange(event.target.value)}
                        placeholder={searchPlaceholder}
                        aria-label={searchAriaLabel}
                        className="w-full min-w-0 bg-transparent text-sm font-medium outline-none placeholder:text-(--text-muted)"
                        style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}
                    />
                </div>
            ) : (
                <div className="hidden lg:flex lg:flex-1" />
            )}

            {showSecondaryControls ? (
                <div className="flex w-full flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end lg:w-auto lg:justify-end">
                    <div className="flex items-center gap-2 text-xs font-semibold uppercase tracking-[1.5px] sm:hidden" style={{ color: 'var(--text-secondary)' }}>
                        <SlidersHorizontal size={14} />
                        Filters
                    </div>

                    {showStatus && onStatusChange !== undefined ? (
                        <CrudFilterSelectControl
                            select={{
                                value: statusValue ?? '',
                                onChange: onStatusChange,
                                options: statusOptions,
                                ariaLabel: statusAriaLabel,
                                label: 'Status',
                            }}
                        />
                    ) : null}

                    {selects.map((select) => (
                        <CrudFilterSelectControl key={select.ariaLabel} select={select} />
                    ))}

                    {children !== undefined ? children : null}

                    {(showStatus || selects.length > 0 || children !== undefined) && showDateRange ? (
                        <CrudFilterSeparator />
                    ) : null}

                    {showDateRange && onDateRangeChange !== undefined ? (
                        <DataTableDateRangeFilter
                            dateFrom={dateFrom}
                            dateTo={dateTo}
                            onChange={onDateRangeChange}
                        />
                    ) : null}

                    {showDateRange && (actions !== undefined || onReset !== undefined) ? (
                        <CrudFilterSeparator />
                    ) : null}

                    {onReset !== undefined && hasActiveFilters === true ? (
                        <button
                            type="button"
                            onClick={onReset}
                            className="inline-flex h-10 items-center justify-center gap-2 rounded-lg border px-3 text-sm font-semibold transition-colors"
                            style={{ borderColor: 'var(--border-default)', background: 'transparent', color: 'var(--accent-error)', fontFamily: 'var(--font-sans)' }}
                        >
                            <X size={14} />
                            Reset
                        </button>
                    ) : null}

                    {actions !== undefined ? (
                        <div className="flex items-end">{actions}</div>
                    ) : null}
                </div>
            ) : null}
        </div>
    );
}
