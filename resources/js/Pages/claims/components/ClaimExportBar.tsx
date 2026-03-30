import * as React from 'react';
import { Download, FileText, FileSpreadsheet, ChevronDown, Loader2 } from 'lucide-react';
import type { ClaimFilters } from '@/modules/claims/types';

interface ClaimExportBarProps {
    filters: ClaimFilters;
}

export function ClaimExportBar({ filters }: ClaimExportBarProps): React.JSX.Element {
    const [open, setOpen] = React.useState(false);
    const [exporting, setExporting] = React.useState<'pdf' | 'excel' | null>(null);
    const ref = React.useRef<HTMLDivElement>(null);

    React.useEffect(() => {
        function handleClickOutside(e: MouseEvent): void {
            if (ref.current && !ref.current.contains(e.target as Node)) {
                setOpen(false);
            }
        }
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    function buildExportUrl(format: 'pdf' | 'excel'): string {
        const params = new URLSearchParams();
        params.set('format', format);
        if (filters.search) params.set('search', filters.search);
        if (filters.status) params.set('status', filters.status);
        if (filters.date_from) params.set('date_from', filters.date_from);
        if (filters.date_to) params.set('date_to', filters.date_to);
        return `/claims/data/admin/export?${params.toString()}`;
    }

    async function handleExport(format: 'pdf' | 'excel'): Promise<void> {
        setExporting(format);
        setOpen(false);
        try {
            const url = buildExportUrl(format);
            const link = document.createElement('a');
            link.href = url;
            link.download = `claims-export.${format === 'pdf' ? 'pdf' : 'xlsx'}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        } finally {
            setTimeout(() => setExporting(null), 1500);
        }
    }

    return (
        <div ref={ref} style={{ position: 'relative' }}>
            <button
                type="button"
                onClick={() => setOpen((v) => !v)}
                aria-haspopup="true"
                aria-expanded={open}
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 7,
                    height: 36,
                    padding: '0 14px',
                    borderRadius: 'var(--radius-md)',
                    border: '1px solid var(--border-default)',
                    background: 'var(--bg-elevated)',
                    color: 'var(--text-secondary)',
                    fontSize: 13,
                    fontFamily: 'var(--font-sans)',
                    fontWeight: 600,
                    cursor: 'pointer',
                    transition: 'all 0.15s ease',
                    whiteSpace: 'nowrap',
                }}
            >
                {exporting ? (
                    <Loader2 size={13} className="animate-spin" />
                ) : (
                    <Download size={13} />
                )}
                Export
                <ChevronDown size={12} style={{ opacity: 0.7 }} />
            </button>

            {open && (
                <div
                    style={{
                        position: 'absolute',
                        top: 'calc(100% + 6px)',
                        right: 0,
                        minWidth: 180,
                        background: 'var(--bg-elevated)',
                        border: '1px solid var(--border-default)',
                        borderRadius: 'var(--radius-md)',
                        boxShadow: '0 8px 24px rgba(0,0,0,0.3)',
                        zIndex: 50,
                        overflow: 'hidden',
                    }}
                >
                    <button
                        type="button"
                        onClick={() => void handleExport('pdf')}
                        style={{
                            width: '100%',
                            padding: '10px 14px',
                            display: 'flex',
                            alignItems: 'center',
                            gap: 10,
                            background: 'transparent',
                            border: 'none',
                            borderBottom: '1px solid var(--border-subtle)',
                            color: 'var(--text-primary)',
                            fontSize: 13,
                            fontFamily: 'var(--font-sans)',
                            cursor: 'pointer',
                            textAlign: 'left',
                            transition: 'background 0.15s ease',
                        }}
                        onMouseEnter={(e) => { (e.currentTarget as HTMLButtonElement).style.background = 'var(--bg-hover)'; }}
                        onMouseLeave={(e) => { (e.currentTarget as HTMLButtonElement).style.background = 'transparent'; }}
                    >
                        <FileText size={14} style={{ color: 'var(--accent-error)' }} />
                        Export as PDF
                    </button>
                    <button
                        type="button"
                        onClick={() => void handleExport('excel')}
                        style={{
                            width: '100%',
                            padding: '10px 14px',
                            display: 'flex',
                            alignItems: 'center',
                            gap: 10,
                            background: 'transparent',
                            border: 'none',
                            color: 'var(--text-primary)',
                            fontSize: 13,
                            fontFamily: 'var(--font-sans)',
                            cursor: 'pointer',
                            textAlign: 'left',
                            transition: 'background 0.15s ease',
                        }}
                        onMouseEnter={(e) => { (e.currentTarget as HTMLButtonElement).style.background = 'var(--bg-hover)'; }}
                        onMouseLeave={(e) => { (e.currentTarget as HTMLButtonElement).style.background = 'transparent'; }}
                    >
                        <FileSpreadsheet size={14} style={{ color: 'var(--accent-success)' }} />
                        Export as Excel
                    </button>
                </div>
            )}
        </div>
    );
}
