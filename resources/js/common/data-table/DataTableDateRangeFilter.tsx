import * as React from 'react';

interface DataTableDateRangeFilterProps {
  dateFrom: string | undefined;
  dateTo: string | undefined;
  onChange: (range: { dateFrom?: string; dateTo?: string }) => void;
  className?: string;
}

export function DataTableDateRangeFilter({
  dateFrom,
  dateTo,
  onChange,
  className = '',
}: DataTableDateRangeFilterProps): React.JSX.Element {
  const inputStyle: React.CSSProperties = {
    borderColor: 'var(--border-default)',
    background: 'var(--bg-surface)',
    color: 'var(--text-primary)',
    fontFamily: 'var(--font-sans)',
    colorScheme: 'dark',
  };

  return (
    <div className={`grid grid-cols-1 gap-3 sm:grid-cols-2 ${className}`.trim()}>
      <div className="flex flex-col gap-1.5">
        <label className="text-[11px] font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-secondary)' }}>
          From
        </label>
        <input
          type="date"
          value={dateFrom || ''}
          onChange={(e) => onChange({ dateFrom: e.target.value || undefined, dateTo })}
          aria-label="From date"
          className="h-10 rounded-lg border px-3 text-sm font-medium shadow-sm transition-colors focus:border-(--accent-primary) focus:outline-none"
          style={inputStyle}
        />
      </div>
      <div className="flex flex-col gap-1.5">
        <label className="text-[11px] font-semibold uppercase tracking-[1.5px]" style={{ color: 'var(--text-secondary)' }}>
          To
        </label>
        <input
          type="date"
          value={dateTo || ''}
          onChange={(e) => onChange({ dateFrom, dateTo: e.target.value || undefined })}
          aria-label="To date"
          className="h-10 rounded-lg border px-3 text-sm font-medium shadow-sm transition-colors focus:border-(--accent-primary) focus:outline-none"
          style={inputStyle}
        />
      </div>
    </div>
  );
}
