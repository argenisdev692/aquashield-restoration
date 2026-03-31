import * as React from 'react';
import { Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useClaims } from '@/modules/claims/hooks/useClaims';
import type { ClaimFilters } from '@/modules/claims/types';
import { ClaimFiltersBar } from './components/ClaimFilters';
import { ClaimExportBar } from './components/ClaimExportBar';
import { ClaimsTable } from './components/ClaimsTable';

const DEFAULT_FILTERS: ClaimFilters = {
    page: 1,
    per_page: 15,
    status: 'active',
};

export default function ClaimsIndexPage(): React.JSX.Element {
    const [filters, setFilters] = React.useState<ClaimFilters>(DEFAULT_FILTERS);

    const { data, isPending } = useClaims(filters);

    function handleFilterChange(partial: Partial<ClaimFilters>): void {
        setFilters((prev) => ({ ...prev, ...partial }));
    }

    function handleReset(): void {
        setFilters(DEFAULT_FILTERS);
    }

    return (
        <AppLayout>
            <div
                style={{
                    display: 'flex',
                    flexDirection: 'column',
                    gap: 20,
                    padding: '24px 28px',
                    maxWidth: 1280,
                    margin: '0 auto',
                    width: '100%',
                    fontFamily: 'var(--font-sans)',
                }}
            >
                {/* Page header */}
                <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12 }}>
                    <div>
                        <h1 style={{ margin: 0, fontSize: 24, fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>
                            Claims
                        </h1>
                        <p style={{ margin: '4px 0 0', fontSize: 13, color: 'var(--text-muted)' }}>
                            Manage and track all insurance claims.
                        </p>
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                        <ClaimExportBar filters={filters} />
                        <PermissionGuard permissions={['CREATE_CLAIM']}>
                            <Link
                                href="/claims/create"
                                className="btn-modern btn-modern-primary"
                                style={{ textDecoration: 'none', whiteSpace: 'nowrap' }}
                            >
                                <Plus size={14} /> New Claim
                            </Link>
                        </PermissionGuard>
                    </div>
                </div>

                {/* Filters */}
                <ClaimFiltersBar
                    filters={filters}
                    onChange={handleFilterChange}
                    onReset={handleReset}
                />

                {/* Table */}
                <PermissionGuard permissions={['VIEW_CLAIM']}>
                    <ClaimsTable
                        data={data?.data ?? []}
                        isLoading={isPending}
                        currentPage={data?.meta.currentPage ?? 1}
                        lastPage={data?.meta.lastPage ?? 1}
                        total={data?.meta.total ?? 0}
                        onPageChange={(page) => handleFilterChange({ page })}
                    />
                </PermissionGuard>
            </div>
        </AppLayout>
    );
}
