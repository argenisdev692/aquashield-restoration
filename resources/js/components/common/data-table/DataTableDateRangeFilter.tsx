import * as React from 'react';

interface DataTableDateRangeFilterProps {
  dateFrom: string;
  dateTo: string;
  onFromChange: (value: string) => void;
  onToChange: (value: string) => void;
}

export function DataTableDateRangeFilter({
  dateFrom,
  dateTo,
  onFromChange,
  onToChange,
}: DataTableDateRangeFilterProps): React.JSX.Element {
  return (
    <div className="flex items-center gap-2">
      <div className="flex flex-col gap-1">
        <label className="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground" style={{ color: 'var(--text-disabled)' }}>
          From
        </label>
        <input
          type="date"
          value={dateFrom}
          onChange={(e) => onFromChange(e.target.value)}
          className="h-9 rounded-lg border bg-card px-3 shadow-sm transition-all focus:outline-none focus:ring-1"
          style={{
            background: 'var(--bg-card)',
            borderColor: 'var(--border-default)',
            color: 'var(--text-primary)',
          }}
        />
      </div>
      <div className="flex flex-col gap-1">
        <label className="text-[10px] font-semibold uppercase tracking-wider text-muted-foreground" style={{ color: 'var(--text-disabled)' }}>
          To
        </label>
        <input
          type="date"
          value={dateTo}
          onChange={(e) => onToChange(e.target.value)}
          className="h-9 rounded-lg border bg-card px-3 shadow-sm transition-all focus:outline-none focus:ring-1"
          style={{
            background: 'var(--bg-card)',
            borderColor: 'var(--border-default)',
            color: 'var(--text-primary)',
          }}
        />
      </div>
    </div>
  );
}
