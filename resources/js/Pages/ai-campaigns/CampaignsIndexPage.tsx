import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import {
  Bot,
  ChevronLeft,
  ChevronRight,
  Facebook,
  Image,
  Instagram,
  Search,
  Trash2,
  RotateCcw,
  ExternalLink,
} from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { useCampaigns } from '@/modules/ai-campaigns/hooks/useCampaigns';
import { useCampaignMutations } from '@/modules/ai-campaigns/hooks/useCampaignMutations';
import type { CampaignFilters, CampaignListItem, CampaignPlatform } from '@/modules/ai-campaigns/types';
import { PLATFORM_LABELS, STATUS_LABELS } from '@/modules/ai-campaigns/types';

const PLATFORM_ICONS: Record<CampaignPlatform, React.ReactNode> = {
  tiktok:    <Bot size={14} />,
  instagram: <Instagram size={14} />,
  facebook:  <Facebook size={14} />,
};

const STATUS_COLORS: Record<string, string> = {
  draft:     'var(--text-disabled)',
  generated: 'var(--accent-primary)',
  published: 'var(--accent-success)',
  deleted:   'var(--accent-error)',
};

export default function CampaignsIndexPage(): React.JSX.Element {
  const [filters, setFilters] = useRemember<CampaignFilters>(
    { page: 1, per_page: 15, status: '' },
    'ai-campaigns-filters',
  );
  const [search, setSearch] = React.useState<string>(filters.search ?? '');
  const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
  const [pendingDelete, setPendingDelete] = React.useState<CampaignListItem | null>(null);
  const [pendingRestore, setPendingRestore] = React.useState<CampaignListItem | null>(null);
  const [, startSearchTransition] = React.useTransition();

  const { data, isPending, isError } = useCampaigns(filters);
  const { deleteCampaign, restoreCampaign, bulkDeleteCampaigns } = useCampaignMutations();

  const campaigns = data?.data ?? [];
  const meta = data?.meta ?? { current_page: 1, last_page: 1, per_page: 15, total: 0 };

  const selectedUuids = React.useMemo(
    () => Object.keys(rowSelection).filter((k) => rowSelection[k]),
    [rowSelection],
  );

  function handleSearchChange(event: React.ChangeEvent<HTMLInputElement>): void {
    const value = event.target.value;
    setSearch(value);
    startSearchTransition(() => {
      setFilters((prev) => ({ ...prev, search: value || undefined, page: 1 }));
    });
  }

  async function confirmDelete(): Promise<void> {
    if (!pendingDelete) return;
    await deleteCampaign.mutateAsync(pendingDelete.uuid);
    setPendingDelete(null);
  }

  async function confirmRestore(): Promise<void> {
    if (!pendingRestore) return;
    await restoreCampaign.mutateAsync(pendingRestore.uuid);
    setPendingRestore(null);
  }

  async function handleBulkDelete(): Promise<void> {
    if (selectedUuids.length === 0) return;
    await bulkDeleteCampaigns.mutateAsync(selectedUuids);
    setRowSelection({});
  }

  return (
    <>
      <Head title="AI Campaigns" />
      <AppLayout>
        <div className="flex flex-col gap-6">

          {/* ── Header ── */}
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 className="text-3xl font-extrabold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                AI Campaigns
              </h1>
              <p className="mt-1 text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                AI-generated social media campaigns —{' '}
                <span style={{ color: 'var(--accent-primary)' }}>{meta.total}</span> campaigns.
              </p>
            </div>

            <Link
              href="/ai-campaigns/create"
              className="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all"
              style={{
                background: 'var(--grad-primary)',
                color: 'var(--color-white)',
                boxShadow: '0 10px 24px color-mix(in srgb, var(--accent-primary) 24%, transparent)',
              }}
            >
              <Bot size={18} />
              <span>Generate Campaign</span>
            </Link>
          </div>

          {/* ── Filters ── */}
          <div
            className="flex flex-col gap-4 rounded-3xl px-5 py-4 shadow-sm lg:flex-row lg:items-end lg:justify-between"
            style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
          >
            <div className="flex flex-1 items-center gap-3 rounded-2xl px-4 py-3" style={{ background: 'var(--bg-surface)' }}>
              <Search size={18} style={{ color: 'var(--text-disabled)' }} />
              <input
                type="text"
                value={search}
                onChange={handleSearchChange}
                placeholder="Search by title, niche or caption…"
                className="w-full bg-transparent text-sm outline-none"
                style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}
              />
            </div>

            <div className="flex flex-wrap items-end gap-3">
              <label className="flex flex-col gap-2 text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                Status
                <select
                  value={filters.status ?? ''}
                  onChange={(e) => setFilters((p) => ({ ...p, status: e.target.value as CampaignFilters['status'], page: 1 }))}
                  className="h-10 rounded-xl border px-3 text-sm outline-none"
                  style={{ background: 'var(--bg-surface)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }}
                >
                  <option value="">All</option>
                  <option value="draft">Draft</option>
                  <option value="generated">Generated</option>
                  <option value="published">Published</option>
                  <option value="deleted">Deleted</option>
                </select>
              </label>

              <label className="flex flex-col gap-2 text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                Platform
                <select
                  value={filters.platform ?? ''}
                  onChange={(e) => setFilters((p) => ({ ...p, platform: e.target.value as CampaignFilters['platform'], page: 1 }))}
                  className="h-10 rounded-xl border px-3 text-sm outline-none"
                  style={{ background: 'var(--bg-surface)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }}
                >
                  <option value="">All Platforms</option>
                  <option value="tiktok">TikTok</option>
                  <option value="instagram">Instagram</option>
                  <option value="facebook">Facebook</option>
                </select>
              </label>
            </div>
          </div>

          {/* ── Bulk Actions ── */}
          <DataTableBulkActions
            count={selectedUuids.length}
            onDelete={() => { void handleBulkDelete(); }}
            isDeleting={bulkDeleteCampaigns.isPending}
          />

          {/* ── Grid ── */}
          <div
            className="overflow-hidden rounded-3xl shadow-xl"
            style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
          >
            {isPending && (
              <div className="flex items-center justify-center py-24">
                <div className="h-8 w-8 animate-spin rounded-full border-2 border-transparent" style={{ borderTopColor: 'var(--accent-primary)' }} />
              </div>
            )}

            {isError && (
              <div className="flex items-center justify-center py-24 text-sm font-medium" style={{ color: 'var(--accent-error)' }}>
                Failed to load campaigns. Please try again.
              </div>
            )}

            {!isPending && !isError && campaigns.length === 0 && (
              <div className="flex flex-col items-center justify-center gap-3 py-24">
                <Bot size={40} style={{ color: 'var(--text-disabled)' }} />
                <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                  No campaigns found. Generate your first AI campaign!
                </p>
                <Link
                  href="/ai-campaigns/create"
                  className="mt-2 flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold"
                  style={{ background: 'var(--grad-primary)', color: 'var(--color-white)' }}
                >
                  <Bot size={16} /> Generate Campaign
                </Link>
              </div>
            )}

            {!isPending && !isError && campaigns.length > 0 && (
              <div className="overflow-x-auto">
                <table className="w-full text-sm">
                  <thead>
                    <tr style={{ borderBottom: '1px solid var(--border-subtle)', background: 'var(--bg-surface)' }}>
                      <th className="w-10 py-3 pl-5 text-left">
                        <input
                          type="checkbox"
                          checked={selectedUuids.length === campaigns.length && campaigns.length > 0}
                          onChange={(e) => {
                            if (e.target.checked) {
                              const all: RowSelectionState = {};
                              campaigns.forEach((c) => { all[c.uuid] = true; });
                              setRowSelection(all);
                            } else {
                              setRowSelection({});
                            }
                          }}
                          className="h-4 w-4 rounded"
                          style={{ accentColor: 'var(--accent-primary)' }}
                          aria-label="Select all"
                        />
                      </th>
                      {['Campaign', 'Platform', 'Status', 'Created', 'Actions'].map((h) => (
                        <th
                          key={h}
                          className="py-3 pr-5 text-left text-[11px] font-bold uppercase tracking-widest"
                          style={{ color: 'var(--text-disabled)' }}
                        >
                          {h}
                        </th>
                      ))}
                    </tr>
                  </thead>
                  <tbody>
                    {campaigns.map((campaign) => (
                      <tr
                        key={campaign.uuid}
                        style={{ borderBottom: '1px solid var(--border-subtle)' }}
                        className="transition-colors"
                        onMouseEnter={(e) => { (e.currentTarget as HTMLTableRowElement).style.background = 'var(--bg-hover)'; }}
                        onMouseLeave={(e) => { (e.currentTarget as HTMLTableRowElement).style.background = 'transparent'; }}
                      >
                        <td className="py-3 pl-5">
                          <input
                            type="checkbox"
                            checked={!!rowSelection[campaign.uuid]}
                            onChange={(e) => setRowSelection((prev) => ({ ...prev, [campaign.uuid]: e.target.checked }))}
                            className="h-4 w-4 rounded"
                            style={{ accentColor: 'var(--accent-primary)' }}
                            aria-label={`Select ${campaign.title}`}
                          />
                        </td>

                        <td className="py-3 pr-5">
                          <div className="flex items-center gap-3">
                            {campaign.image_url ? (
                              <img
                                src={campaign.image_url}
                                alt={campaign.title}
                                className="h-10 w-10 rounded-xl object-cover"
                              />
                            ) : (
                              <div className="flex h-10 w-10 items-center justify-center rounded-xl" style={{ background: 'var(--bg-elevated)' }}>
                                <Image size={18} style={{ color: 'var(--text-disabled)' }} />
                              </div>
                            )}
                            <div>
                              <Link
                                href={`/ai-campaigns/${campaign.uuid}`}
                                className="text-sm font-semibold transition-colors"
                                style={{ color: 'var(--text-primary)' }}
                                onMouseEnter={(e) => { e.currentTarget.style.color = 'var(--accent-primary)'; }}
                                onMouseLeave={(e) => { e.currentTarget.style.color = 'var(--text-primary)'; }}
                              >
                                {campaign.title}
                              </Link>
                              <p className="text-[11px]" style={{ color: 'var(--text-disabled)' }}>
                                {campaign.niche}
                              </p>
                            </div>
                          </div>
                        </td>

                        <td className="py-3 pr-5">
                          <span
                            className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold"
                            style={{
                              background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                              color: 'var(--accent-primary)',
                            }}
                          >
                            {PLATFORM_ICONS[campaign.platform]}
                            {PLATFORM_LABELS[campaign.platform]}
                          </span>
                        </td>

                        <td className="py-3 pr-5">
                          <span
                            className="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                            style={{
                              background: `color-mix(in srgb, ${STATUS_COLORS[campaign.deleted_at ? 'deleted' : campaign.status]} 12%, transparent)`,
                              color: STATUS_COLORS[campaign.deleted_at ? 'deleted' : campaign.status],
                            }}
                          >
                            {campaign.deleted_at ? 'Deleted' : STATUS_LABELS[campaign.status]}
                          </span>
                        </td>

                        <td className="py-3 pr-5 text-[11px]" style={{ color: 'var(--text-disabled)' }}>
                          {new Date(campaign.created_at).toLocaleDateString()}
                        </td>

                        <td className="py-3 pr-5">
                          <div className="flex items-center gap-2">
                            <Link
                              href={`/ai-campaigns/${campaign.uuid}`}
                              className="flex h-8 w-8 items-center justify-center rounded-lg border transition-all"
                              style={{ borderColor: 'var(--border-default)', color: 'var(--text-muted)' }}
                              title="View"
                            >
                              <ExternalLink size={14} />
                            </Link>

                            {campaign.deleted_at ? (
                              <button
                                type="button"
                                onClick={() => setPendingRestore(campaign)}
                                className="flex h-8 w-8 items-center justify-center rounded-lg border transition-all"
                                style={{ borderColor: 'var(--border-default)', color: 'var(--accent-primary)' }}
                                title="Restore"
                              >
                                <RotateCcw size={14} />
                              </button>
                            ) : (
                              <button
                                type="button"
                                onClick={() => setPendingDelete(campaign)}
                                className="flex h-8 w-8 items-center justify-center rounded-lg border transition-all"
                                style={{ borderColor: 'var(--border-default)', color: 'var(--accent-error)' }}
                                title="Delete"
                              >
                                <Trash2 size={14} />
                              </button>
                            )}
                          </div>
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            )}

            {/* ── Pagination ── */}
            {meta.last_page > 1 && (
              <div
                className="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                style={{ background: 'var(--bg-surface)', borderTop: '1px solid var(--border-subtle)' }}
              >
                <span className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                  Page {meta.current_page} / {meta.last_page} • {meta.total} total
                </span>
                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={() => setFilters((p) => ({ ...p, page: (p.page ?? 1) - 1 }))}
                    disabled={meta.current_page <= 1}
                    className="flex h-9 w-9 items-center justify-center rounded-xl border transition-all disabled:opacity-40"
                    style={{ background: 'var(--bg-card)', borderColor: 'var(--border-default)', color: 'var(--text-muted)' }}
                  >
                    <ChevronLeft size={18} />
                  </button>
                  <button
                    type="button"
                    onClick={() => setFilters((p) => ({ ...p, page: (p.page ?? 1) + 1 }))}
                    disabled={meta.current_page >= meta.last_page}
                    className="flex h-9 w-9 items-center justify-center rounded-xl border transition-all disabled:opacity-40"
                    style={{ background: 'var(--bg-card)', borderColor: 'var(--border-default)', color: 'var(--text-muted)' }}
                  >
                    <ChevronRight size={18} />
                  </button>
                </div>
              </div>
            )}
          </div>
        </div>

        <DeleteConfirmModal
          open={pendingDelete !== null}
          entityLabel={pendingDelete?.title ?? ''}
          onConfirm={() => { void confirmDelete(); }}
          onCancel={() => setPendingDelete(null)}
          isDeleting={deleteCampaign.isPending}
        />

        <RestoreConfirmModal
          isOpen={pendingRestore !== null}
          entityLabel="campaign"
          entityName={pendingRestore?.title}
          onConfirm={() => { void confirmRestore(); }}
          onCancel={() => setPendingRestore(null)}
          isPending={restoreCampaign.isPending}
        />
      </AppLayout>
    </>
  );
}
