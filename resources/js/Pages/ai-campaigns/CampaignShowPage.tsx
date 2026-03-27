import * as React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import type { AuthPageProps } from '@/types/auth';
import {
  ArrowLeft,
  Bot,
  Copy,
  Facebook,
  Hash,
  Image,
  Instagram,
  Loader2,
  Megaphone,
  Pencil,
  Save,
  Trash2,
} from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useCampaign } from '@/modules/ai-campaigns/hooks/useCampaign';
import { useCampaignMutations } from '@/modules/ai-campaigns/hooks/useCampaignMutations';
import type { CampaignPlatform, CampaignStatus, UpdateCampaignPayload } from '@/modules/ai-campaigns/types';
import { PLATFORM_DIMENSIONS, PLATFORM_LABELS, STATUS_LABELS } from '@/modules/ai-campaigns/types';

const PLATFORM_ICONS: Record<CampaignPlatform, React.ReactNode> = {
  tiktok:    <Bot size={16} />,
  instagram: <Instagram size={16} />,
  facebook:  <Facebook size={16} />,
};

export default function CampaignShowPage(): React.JSX.Element {
  const { uuid } = usePage<AuthPageProps & { uuid: string }>().props;
  const { data: campaign, isPending, isError } = useCampaign(uuid ?? null);
  const { updateCampaign, deleteCampaign } = useCampaignMutations();

  const [isEditing, setIsEditing] = React.useState<boolean>(false);
  const [editForm, setEditForm] = React.useState<UpdateCampaignPayload>({});

  React.useEffect(() => {
    if (campaign) {
      setEditForm({
        title:          campaign.title,
        niche:          campaign.niche,
        platform:       campaign.platform,
        caption:        campaign.caption ?? '',
        hashtags:       campaign.hashtags ?? '',
        call_to_action: campaign.call_to_action ?? '',
        status:         campaign.status,
      });
    }
  }, [campaign]);

  async function handleSave(): Promise<void> {
    if (!campaign) return;
    await updateCampaign.mutateAsync({ uuid: campaign.uuid, payload: editForm });
    setIsEditing(false);
  }

  async function handleDelete(): Promise<void> {
    if (!campaign) return;
    if (!window.confirm(`Delete campaign "${campaign.title}"?`)) return;
    await deleteCampaign.mutateAsync(campaign.uuid);
    window.location.href = '/ai-campaigns';
  }

  function copyToClipboard(text: string): void {
    void navigator.clipboard.writeText(text);
  }

  if (isPending) {
    return (
      <AppLayout>
        <div className="flex items-center justify-center py-32">
          <Loader2 size={32} className="animate-spin" style={{ color: 'var(--accent-primary)' }} />
        </div>
      </AppLayout>
    );
  }

  if (isError || !campaign) {
    return (
      <AppLayout>
        <div className="flex flex-col items-center justify-center gap-4 py-32">
          <p className="text-sm font-medium" style={{ color: 'var(--accent-error)' }}>
            Campaign not found or failed to load.
          </p>
          <Link href="/ai-campaigns" className="text-sm font-semibold" style={{ color: 'var(--accent-primary)' }}>
            ← Back to Campaigns
          </Link>
        </div>
      </AppLayout>
    );
  }

  return (
    <>
      <Head title={campaign.title} />
      <AppLayout>
        <div className="mx-auto flex max-w-3xl flex-col gap-6">

          {/* ── Header ── */}
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div className="flex items-center gap-4">
              <Link
                href="/ai-campaigns"
                className="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border transition-all"
                style={{ borderColor: 'var(--border-default)', color: 'var(--text-muted)', background: 'var(--bg-card)' }}
              >
                <ArrowLeft size={16} />
              </Link>
              <div>
                <h1 className="text-2xl font-extrabold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                  {campaign.title}
                </h1>
                <div className="mt-1 flex items-center gap-2">
                  <span
                    className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold"
                    style={{
                      background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                      color: 'var(--accent-primary)',
                    }}
                  >
                    {PLATFORM_ICONS[campaign.platform]}
                    {PLATFORM_LABELS[campaign.platform]} · {PLATFORM_DIMENSIONS[campaign.platform]}
                  </span>
                  <span
                    className="rounded-full px-2.5 py-1 text-[11px] font-semibold"
                    style={{
                      background: 'color-mix(in srgb, var(--accent-success) 12%, transparent)',
                      color: 'var(--accent-success)',
                    }}
                  >
                    {STATUS_LABELS[campaign.status as CampaignStatus]}
                  </span>
                </div>
              </div>
            </div>

            <div className="flex items-center gap-2">
              {isEditing ? (
                <>
                  <button
                    type="button"
                    onClick={() => setIsEditing(false)}
                    className="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-all"
                    style={{ borderColor: 'var(--border-default)', color: 'var(--text-muted)', background: 'var(--bg-card)' }}
                  >
                    Cancel
                  </button>
                  <button
                    type="button"
                    onClick={() => { void handleSave(); }}
                    disabled={updateCampaign.isPending}
                    className="flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-semibold transition-all disabled:opacity-60"
                    style={{ background: 'var(--grad-primary)', color: 'var(--color-white)' }}
                  >
                    {updateCampaign.isPending ? <Loader2 size={16} className="animate-spin" /> : <Save size={16} />}
                    Save
                  </button>
                </>
              ) : (
                <>
                  <button
                    type="button"
                    onClick={() => setIsEditing(true)}
                    className="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-all"
                    style={{ borderColor: 'var(--border-default)', color: 'var(--text-secondary)', background: 'var(--bg-card)' }}
                  >
                    <Pencil size={16} /> Edit
                  </button>
                  <button
                    type="button"
                    onClick={() => { void handleDelete(); }}
                    disabled={deleteCampaign.isPending}
                    className="flex items-center gap-2 rounded-xl border px-4 py-2 text-sm font-semibold transition-all disabled:opacity-60"
                    style={{ borderColor: 'var(--accent-error)', color: 'var(--accent-error)', background: 'var(--bg-card)' }}
                  >
                    {deleteCampaign.isPending ? <Loader2 size={16} className="animate-spin" /> : <Trash2 size={16} />}
                    Delete
                  </button>
                </>
              )}
            </div>
          </div>

          {/* ── Generated Image ── */}
          <div
            className="overflow-hidden rounded-3xl"
            style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
          >
            {campaign.image_url ? (
              <img
                src={campaign.image_url}
                alt={campaign.title}
                className="h-64 w-full object-cover sm:h-80"
              />
            ) : (
              <div className="flex h-48 items-center justify-center" style={{ background: 'var(--bg-surface)' }}>
                <div className="flex flex-col items-center gap-2">
                  <Image size={40} style={{ color: 'var(--text-disabled)' }} />
                  <p className="text-sm" style={{ color: 'var(--text-disabled)' }}>No image generated</p>
                </div>
              </div>
            )}
          </div>

          {/* ── Content Fields ── */}
          <div
            className="flex flex-col gap-5 rounded-3xl p-6"
            style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
          >
            <SectionHeader icon={<Megaphone size={16} />} label="Caption" />
            <ContentField
              icon={<Megaphone size={14} />}
              value={isEditing ? (editForm.caption ?? '') : (campaign.caption ?? '')}
              editing={isEditing}
              onEdit={(v) => setEditForm((p) => ({ ...p, caption: v }))}
              onCopy={() => copyToClipboard(campaign.caption ?? '')}
              placeholder="Post caption will appear here…"
              multiline
            />

            <SectionHeader icon={<Hash size={16} />} label="Hashtags" />
            <ContentField
              icon={<Hash size={14} />}
              value={isEditing ? (editForm.hashtags ?? '') : (campaign.hashtags ?? '')}
              editing={isEditing}
              onEdit={(v) => setEditForm((p) => ({ ...p, hashtags: v }))}
              onCopy={() => copyToClipboard(campaign.hashtags ?? '')}
              placeholder="Hashtags will appear here…"
              mono
            />

            <SectionHeader icon={<Bot size={16} />} label="Call to Action" />
            <ContentField
              icon={<Bot size={14} />}
              value={isEditing ? (editForm.call_to_action ?? '') : (campaign.call_to_action ?? '')}
              editing={isEditing}
              onEdit={(v) => setEditForm((p) => ({ ...p, call_to_action: v }))}
              onCopy={() => copyToClipboard(campaign.call_to_action ?? '')}
              placeholder="Call-to-action will appear here…"
            />

            {isEditing && (
              <>
                <SectionHeader icon={<Bot size={16} />} label="Status" />
                <select
                  value={editForm.status ?? 'draft'}
                  onChange={(e) => setEditForm((p) => ({ ...p, status: e.target.value as CampaignStatus }))}
                  className="h-10 w-full rounded-xl border px-3 text-sm outline-none"
                  style={{
                    background: 'var(--bg-surface)',
                    borderColor: 'var(--border-default)',
                    color: 'var(--text-primary)',
                    fontFamily: 'var(--font-sans)',
                  }}
                >
                  <option value="draft">Draft</option>
                  <option value="generated">Generated</option>
                  <option value="published">Published</option>
                </select>
              </>
            )}
          </div>

          {/* ── Meta ── */}
          <div
            className="grid grid-cols-2 gap-4 rounded-3xl p-5 sm:grid-cols-3"
            style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
          >
            {[
              { label: 'Niche', value: campaign.niche },
              { label: 'Created', value: new Date(campaign.created_at).toLocaleDateString() },
              { label: 'Updated', value: new Date(campaign.updated_at).toLocaleDateString() },
            ].map((item) => (
              <div key={item.label} className="flex flex-col gap-1">
                <span className="text-[10px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-disabled)' }}>
                  {item.label}
                </span>
                <span className="text-sm font-semibold" style={{ color: 'var(--text-primary)' }}>
                  {item.value}
                </span>
              </div>
            ))}
          </div>
        </div>
      </AppLayout>
    </>
  );
}

function SectionHeader({ icon, label }: { icon: React.ReactNode; label: string }): React.JSX.Element {
  return (
    <div className="flex items-center gap-2" style={{ color: 'var(--text-muted)' }}>
      {icon}
      <span className="text-[11px] font-bold uppercase tracking-widest">{label}</span>
    </div>
  );
}

function ContentField({
  value,
  editing,
  onEdit,
  onCopy,
  placeholder,
  multiline = false,
  mono = false,
}: {
  icon: React.ReactNode;
  value: string;
  editing: boolean;
  onEdit: (v: string) => void;
  onCopy: () => void;
  placeholder: string;
  multiline?: boolean;
  mono?: boolean;
}): React.JSX.Element {
  const baseStyle: React.CSSProperties = {
    background: 'var(--bg-surface)',
    color: 'var(--text-primary)',
    fontFamily: mono ? 'var(--font-mono, monospace)' : 'var(--font-sans)',
    border: '1px solid var(--border-subtle)',
    borderRadius: '0.75rem',
    padding: '0.75rem 1rem',
    fontSize: '0.875rem',
    lineHeight: 1.6,
    width: '100%',
    outline: 'none',
  };

  if (editing) {
    return multiline ? (
      <textarea
        value={value}
        onChange={(e) => onEdit(e.target.value)}
        placeholder={placeholder}
        rows={4}
        style={{ ...baseStyle, resize: 'vertical' }}
      />
    ) : (
      <input
        type="text"
        value={value}
        onChange={(e) => onEdit(e.target.value)}
        placeholder={placeholder}
        style={{ ...baseStyle, height: '2.75rem' }}
      />
    );
  }

  return (
    <div className="group relative">
      <div style={{ ...baseStyle, whiteSpace: 'pre-wrap', minHeight: '2.75rem' }}>
        {value || <span style={{ color: 'var(--text-disabled)' }}>{placeholder}</span>}
      </div>
      {value && (
        <button
          type="button"
          onClick={onCopy}
          className="absolute right-3 top-3 flex h-7 w-7 items-center justify-center rounded-lg border opacity-0 transition-opacity group-hover:opacity-100"
          style={{
            background: 'var(--bg-card)',
            borderColor: 'var(--border-default)',
            color: 'var(--text-muted)',
          }}
          title="Copy to clipboard"
        >
          <Copy size={12} />
        </button>
      )}
    </div>
  );
}
