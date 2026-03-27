import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Bot, Facebook, Image, Instagram, Loader2, Sparkles, Zap } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { useCampaignMutations } from '@/modules/ai-campaigns/hooks/useCampaignMutations';
import type { CampaignDetail, CampaignPlatform, GenerateCampaignPayload } from '@/modules/ai-campaigns/types';
import { PLATFORM_DIMENSIONS, PLATFORM_LABELS } from '@/modules/ai-campaigns/types';

const PLATFORM_OPTIONS: { value: CampaignPlatform; label: string; dimensions: string; icon: React.ReactNode }[] = [
  { value: 'tiktok',    label: 'TikTok',    dimensions: PLATFORM_DIMENSIONS.tiktok,    icon: <Bot size={20} /> },
  { value: 'instagram', label: 'Instagram', dimensions: PLATFORM_DIMENSIONS.instagram, icon: <Instagram size={20} /> },
  { value: 'facebook',  label: 'Facebook',  dimensions: PLATFORM_DIMENSIONS.facebook,  icon: <Facebook size={20} /> },
];

interface GenerateForm {
  title: string;
  niche: string;
  platform: CampaignPlatform;
}

export default function CampaignCreatePage(): React.JSX.Element {
  const [form, setForm] = React.useState<GenerateForm>({
    title:    '',
    niche:    '',
    platform: 'instagram',
  });
  const [errors, setErrors] = React.useState<Partial<Record<keyof GenerateForm, string>>>({});
  const [result, setResult] = React.useState<CampaignDetail | null>(null);

  const { generateCampaign } = useCampaignMutations();

  function validate(): boolean {
    const next: Partial<Record<keyof GenerateForm, string>> = {};
    if (!form.title.trim()) next.title = 'Campaign title is required.';
    if (!form.niche.trim()) next.niche = 'Niche is required.';
    setErrors(next);
    return Object.keys(next).length === 0;
  }

  function handleChange(field: keyof GenerateForm, value: string): void {
    setForm((prev) => ({ ...prev, [field]: value }));
    if (errors[field]) setErrors((prev) => ({ ...prev, [field]: '' }));
  }

  async function handleGenerate(event: React.FormEvent): Promise<void> {
    event.preventDefault();
    if (!validate()) return;

    const payload: GenerateCampaignPayload = {
      title:    form.title.trim(),
      niche:    form.niche.trim(),
      platform: form.platform,
    };

    generateCampaign.mutate(payload, {
      onSuccess: (campaign) => {
        setResult(campaign);
      },
      onError: (error: unknown) => {
        if (typeof error === 'object' && error !== null && 'response' in error) {
          const resp = (error as { response?: { data?: { errors?: Record<string, string[]> } } }).response;
          const serverErrors = resp?.data?.errors ?? {};
          const mapped: Partial<Record<keyof GenerateForm, string>> = {};
          for (const [k, v] of Object.entries(serverErrors)) {
            mapped[k as keyof GenerateForm] = Array.isArray(v) ? (v[0] ?? '') : String(v);
          }
          setErrors(mapped);
        }
      },
    });
  }

  return (
    <>
      <Head title="Generate AI Campaign" />
      <AppLayout>
        <div className="mx-auto flex max-w-3xl flex-col gap-6">

          {/* ── Page Header ── */}
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
                Generate AI Campaign
              </h1>
              <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                Tavily research + Anthropic text + Replicate image — all in one click.
              </p>
            </div>
          </div>

          {/* ── Form ── */}
          <form onSubmit={(e) => { void handleGenerate(e); }} className="flex flex-col gap-5">
            <div
              className="flex flex-col gap-5 rounded-3xl p-6"
              style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
            >
              {/* Title */}
              <div className="flex flex-col gap-2">
                <label className="text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                  Campaign Title <span style={{ color: 'var(--accent-error)' }}>*</span>
                </label>
                <input
                  type="text"
                  value={form.title}
                  onChange={(e) => handleChange('title', e.target.value)}
                  placeholder="e.g. Summer Water Damage Prevention Tips"
                  className="h-11 w-full rounded-xl border px-4 text-sm outline-none transition-all"
                  style={{
                    background: 'var(--bg-surface)',
                    borderColor: errors.title ? 'var(--accent-error)' : 'var(--border-default)',
                    color: 'var(--text-primary)',
                    fontFamily: 'var(--font-sans)',
                  }}
                />
                {errors.title && (
                  <p className="text-[11px] font-medium" style={{ color: 'var(--accent-error)' }}>{errors.title}</p>
                )}
              </div>

              {/* Niche */}
              <div className="flex flex-col gap-2">
                <label className="text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                  Niche / Industry <span style={{ color: 'var(--accent-error)' }}>*</span>
                </label>
                <input
                  type="text"
                  value={form.niche}
                  onChange={(e) => handleChange('niche', e.target.value)}
                  placeholder="e.g. Water damage restoration, home insurance, roofing"
                  className="h-11 w-full rounded-xl border px-4 text-sm outline-none transition-all"
                  style={{
                    background: 'var(--bg-surface)',
                    borderColor: errors.niche ? 'var(--accent-error)' : 'var(--border-default)',
                    color: 'var(--text-primary)',
                    fontFamily: 'var(--font-sans)',
                  }}
                />
                {errors.niche && (
                  <p className="text-[11px] font-medium" style={{ color: 'var(--accent-error)' }}>{errors.niche}</p>
                )}
              </div>

              {/* Platform */}
              <div className="flex flex-col gap-3">
                <label className="text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                  Target Platform <span style={{ color: 'var(--accent-error)' }}>*</span>
                </label>
                <div className="grid grid-cols-1 gap-3 sm:grid-cols-3">
                  {PLATFORM_OPTIONS.map((opt) => {
                    const isActive = form.platform === opt.value;
                    return (
                      <button
                        key={opt.value}
                        type="button"
                        onClick={() => handleChange('platform', opt.value)}
                        className="flex flex-col items-center gap-2 rounded-2xl border px-4 py-4 text-center transition-all"
                        style={{
                          background: isActive
                            ? 'color-mix(in srgb, var(--accent-primary) 10%, transparent)'
                            : 'var(--bg-surface)',
                          borderColor: isActive ? 'var(--accent-primary)' : 'var(--border-default)',
                          color: isActive ? 'var(--accent-primary)' : 'var(--text-secondary)',
                          boxShadow: isActive
                            ? '0 0 0 2px color-mix(in srgb, var(--accent-primary) 18%, transparent)'
                            : 'none',
                        }}
                      >
                        {opt.icon}
                        <span className="text-sm font-bold">{opt.label}</span>
                        <span className="text-[10px] font-medium" style={{ color: 'var(--text-disabled)' }}>
                          {opt.dimensions}
                        </span>
                      </button>
                    );
                  })}
                </div>
              </div>

              {/* AI Pipeline Info */}
              <div
                className="flex flex-col gap-2 rounded-2xl px-4 py-3"
                style={{ background: 'color-mix(in srgb, var(--accent-primary) 6%, transparent)', border: '1px solid color-mix(in srgb, var(--accent-primary) 20%, transparent)' }}
              >
                <p className="text-xs font-bold" style={{ color: 'var(--accent-primary)' }}>
                  AI Pipeline for {PLATFORM_LABELS[form.platform]}
                </p>
                <ol className="flex flex-col gap-1">
                  {[
                    '1. Tavily researches trending topics for your niche',
                    `2. Anthropic generates caption, hashtags & call-to-action`,
                    `3. Replicate generates a ${PLATFORM_DIMENSIONS[form.platform]} image → stored in R2`,
                  ].map((step) => (
                    <li key={step} className="flex items-start gap-2 text-[11px] font-medium" style={{ color: 'var(--text-secondary)' }}>
                      <Sparkles size={11} className="mt-0.5 shrink-0" style={{ color: 'var(--accent-primary)' }} />
                      {step}
                    </li>
                  ))}
                </ol>
              </div>
            </div>

            {/* Submit */}
            <button
              type="submit"
              disabled={generateCampaign.isPending}
              className="flex items-center justify-center gap-2 rounded-2xl px-6 py-3.5 text-sm font-bold transition-all disabled:opacity-60"
              style={{
                background: 'var(--grad-primary)',
                color: 'var(--color-white)',
                boxShadow: '0 12px 28px color-mix(in srgb, var(--accent-primary) 28%, transparent)',
                fontFamily: 'var(--font-sans)',
              }}
            >
              {generateCampaign.isPending ? (
                <>
                  <Loader2 size={18} className="animate-spin" />
                  Generating campaign… this may take ~30s
                </>
              ) : (
                <>
                  <Zap size={18} />
                  Generate AI Campaign
                </>
              )}
            </button>
          </form>

          {/* ── Result Preview ── */}
          {result && (
            <div
              className="flex flex-col gap-5 rounded-3xl p-6"
              style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)' }}
            >
              <div className="flex items-center justify-between">
                <h2 className="text-lg font-bold" style={{ color: 'var(--text-primary)' }}>
                  Campaign Generated!
                </h2>
                <button
                  type="button"
                  onClick={() => router.visit(`/ai-campaigns/${result.uuid}`)}
                  className="flex items-center gap-2 rounded-xl px-3 py-1.5 text-xs font-semibold transition-all"
                  style={{ background: 'var(--grad-primary)', color: 'var(--color-white)' }}
                >
                  View Campaign
                </button>
              </div>

              {result.image_url && (
                <img
                  src={result.image_url}
                  alt={result.title}
                  className="w-full rounded-2xl object-cover"
                  style={{ maxHeight: 320 }}
                />
              )}

              {!result.image_url && (
                <div
                  className="flex h-32 items-center justify-center rounded-2xl"
                  style={{ background: 'var(--bg-surface)', border: '1px dashed var(--border-default)' }}
                >
                  <Image size={32} style={{ color: 'var(--text-disabled)' }} />
                </div>
              )}

              <div className="flex flex-col gap-4">
                <ResultField label="Caption" value={result.caption} />
                <ResultField label="Hashtags" value={result.hashtags} mono />
                <ResultField label="Call to Action" value={result.call_to_action} />
              </div>
            </div>
          )}
        </div>
      </AppLayout>
    </>
  );
}

function ResultField({ label, value, mono = false }: { label: string; value: string | null; mono?: boolean }): React.JSX.Element | null {
  if (!value) return null;
  return (
    <div className="flex flex-col gap-1.5">
      <span className="text-[10px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-disabled)' }}>
        {label}
      </span>
      <p
        className="rounded-xl px-4 py-3 text-sm leading-relaxed"
        style={{
          background: 'var(--bg-surface)',
          color: 'var(--text-primary)',
          fontFamily: mono ? 'var(--font-mono, monospace)' : 'var(--font-sans)',
          border: '1px solid var(--border-subtle)',
        }}
      >
        {value}
      </p>
    </div>
  );
}
