import * as React from 'react';
import { Search, X, SlidersHorizontal } from 'lucide-react';
import type { ClaimFilters } from '@/modules/claims/types';

interface ClaimFiltersProps {
    filters: ClaimFilters;
    onChange: (partial: Partial<ClaimFilters>) => void;
    onReset: () => void;
}

const inputStyle: React.CSSProperties = {
    height: 36,
    padding: '0 12px',
    background: 'var(--input-bg)',
    border: '1px solid var(--input-border)',
    borderRadius: 'var(--input-radius)',
    color: 'var(--text-primary)',
    fontSize: 13,
    fontFamily: 'var(--font-sans)',
    outline: 'none',
    transition: 'border-color 0.2s ease',
    boxSizing: 'border-box',
};

const selectStyle: React.CSSProperties = {
    ...inputStyle,
    cursor: 'pointer',
    paddingRight: 32,
    appearance: 'none',
    backgroundImage: `url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 24 24' fill='none' stroke='%237a7a90' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E")`,
    backgroundRepeat: 'no-repeat',
    backgroundPosition: 'right 10px center',
};

export function ClaimFiltersBar({ filters, onChange, onReset }: ClaimFiltersProps): React.JSX.Element {
    const hasActive =
        (filters.search?.length ?? 0) > 0 ||
        filters.status !== undefined ||
        (filters.date_from?.length ?? 0) > 0 ||
        (filters.date_to?.length ?? 0) > 0;

    return (
        <div
            style={{
                display: 'flex',
                flexWrap: 'wrap',
                alignItems: 'center',
                gap: 10,
                padding: '12px 16px',
                background: 'var(--bg-elevated)',
                borderRadius: 'var(--radius-lg)',
                border: '1px solid var(--border-default)',
            }}
        >
            <SlidersHorizontal size={14} style={{ color: 'var(--text-muted)', flexShrink: 0 }} />

            {/* Search */}
            <div style={{ position: 'relative', flex: '1 1 200px', minWidth: 160 }}>
                <Search size={13} style={{ position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)', pointerEvents: 'none' }} />
                <input
                    type="text"
                    value={filters.search ?? ''}
                    onChange={(e) => onChange({ search: e.target.value || undefined, page: 1 })}
                    placeholder="Search claims..."
                    aria-label="Search claims"
                    style={{ ...inputStyle, paddingLeft: 32, width: '100%' }}
                />
            </div>

            {/* Status */}
            <select
                value={filters.status ?? ''}
                onChange={(e) => onChange({ status: (e.target.value as ClaimFilters['status']) || undefined, page: 1 })}
                aria-label="Filter by status"
                style={{ ...selectStyle, flex: '0 0 140px' }}
            >
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="deleted">Deleted</option>
            </select>

            {/* Date From */}
            <input
                type="date"
                value={filters.date_from ?? ''}
                onChange={(e) => onChange({ date_from: e.target.value || undefined, page: 1 })}
                aria-label="From date"
                style={{ ...inputStyle, flex: '0 0 140px' }}
            />

            {/* Date To */}
            <input
                type="date"
                value={filters.date_to ?? ''}
                onChange={(e) => onChange({ date_to: e.target.value || undefined, page: 1 })}
                aria-label="To date"
                style={{ ...inputStyle, flex: '0 0 140px' }}
            />

            {/* Reset */}
            {hasActive && (
                <button
                    type="button"
                    onClick={onReset}
                    aria-label="Reset filters"
                    style={{
                        display: 'flex', alignItems: 'center', gap: 5,
                        padding: '0 12px', height: 36, borderRadius: 'var(--radius-md)',
                        border: '1px solid var(--border-default)', background: 'transparent',
                        color: 'var(--accent-error)', fontSize: 12,
                        fontFamily: 'var(--font-sans)', cursor: 'pointer', fontWeight: 600,
                        flexShrink: 0,
                    }}
                >
                    <X size={12} /> Reset
                </button>
            )}
        </div>
    );
}
