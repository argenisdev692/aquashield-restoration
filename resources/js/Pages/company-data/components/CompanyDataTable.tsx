import * as React from 'react';
import { createColumnHelper, type ColumnDef } from '@tanstack/react-table';
import { Link } from '@inertiajs/react';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { formatUsPhoneInput } from '@/common/helpers/phone';
import { DataTable } from '@/shadcn/data-table';
import type { CompanyDataListItem } from '@/modules/company-data/types';
import { formatDateShort } from '@/utils/dateFormatter';

import { Building2, CheckCircle, Pencil } from 'lucide-react';

// ══════════════════════════════════════════════════════════════
// Props
// ══════════════════════════════════════════════════════════════
interface CompanyDataTableProps {
  data: CompanyDataListItem[];
  isLoading: boolean;
  isError: boolean;
  onRestore: (uuid: string) => void;
}

const columnHelper = createColumnHelper<CompanyDataListItem>();

export default function CompanyDataTable({
  data,
  isLoading,
  isError,
  onRestore,
}: CompanyDataTableProps): React.JSX.Element {
  const columns = React.useMemo(() => [
    columnHelper.accessor('company_name', {
      header: 'Company',
      cell: (info) => {
        const company = info.row.original;
        return (
          <div className="flex items-center justify-center gap-3 text-left">
            <div
              className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg text-xs font-bold"
              style={{
                background: 'color-mix(in srgb, var(--accent-primary) 15%, transparent)',
                color: 'var(--accent-primary)',
              }}
            >
              <Building2 size={16} />
            </div>
            <div className="min-w-0">
              <p className="truncate text-sm font-semibold" style={{ color: 'var(--text-primary)' }}>
                {company.company_name}
              </p>
              {company.name && (
                <p className="truncate text-[11px]" style={{ color: 'var(--text-secondary)' }}>
                  Rep: {company.name}
                </p>
              )}
            </div>
          </div>
        );
      },
    }),
    columnHelper.accessor('email', {
      header: 'Contact Email',
      cell: (info) => <span className="text-sm" style={{ color: 'var(--text-secondary)' }}>{info.getValue() ?? '—'}</span>,
    }),
    columnHelper.accessor('phone', {
      header: 'Phone',
      cell: (info) => (
        <span className="text-sm" style={{ color: 'var(--text-secondary)' }}>
          {formatUsPhoneInput(info.getValue() ?? '') || '—'}
        </span>
      ),
    }),
    columnHelper.accessor('created_at', {
      header: 'Created',
      cell: (info) => (
        <span className="text-sm" style={{ color: 'var(--text-muted)' }}>
          {formatDateShort(info.getValue())}
        </span>
      ),
    }),
    columnHelper.display({
      id: 'actions',
      header: 'Actions',
      cell: (info) => {
        const company = info.row.original;
        const isDeleted = Boolean(company.deleted_at);

        return (
          <div className="flex items-center justify-center gap-2">
            {!isDeleted ? (
              <PermissionGuard permissions={['UPDATE_COMPANY_DATA']}>
                <Link
                  href={`/company-data/${company.uuid}/edit`}
                  className="btn-action btn-action-edit"
                  aria-label={`Edit ${company.company_name}`}
                  title="Edit"
                >
                  <Pencil size={14} />
                </Link>
              </PermissionGuard>
            ) : (
              <PermissionGuard permissions={['RESTORE_COMPANY_DATA']}>
                <button
                  onClick={() => onRestore(company.uuid)}
                  className="btn-action btn-action-restore"
                  aria-label={`Restore ${company.company_name}`}
                  title="Restore"
                >
                  <CheckCircle size={14} />
                </button>
              </PermissionGuard>
            )}
          </div>
        );
      },
    }),
  ], [onRestore]);

  return (
    <DataTable
      columns={columns as ColumnDef<CompanyDataListItem, unknown>[]}
      data={data}
      getRowId={(row) => row.uuid}
      isLoading={isLoading}
      isError={isError}
      noDataMessage="No companies found"
    />
  );
}
