import * as React from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import type { AuthPageProps } from '@/types/auth';

// ══════════════════════════════════════════════════════════════════
// Theme Hook
// ══════════════════════════════════════════════════════════════════
type Theme = 'dark' | 'light';

function useTheme(): [Theme, () => void] {
  const [theme, setTheme] = React.useState<Theme>(() => {
    if (typeof window === 'undefined') return 'dark';
    return (localStorage.getItem('aq-theme') as Theme) ?? 'dark';
  });

  const toggle = React.useCallback(() => {
    setTheme((prev) => {
      const next: Theme = prev === 'dark' ? 'light' : 'dark';
      localStorage.setItem('aq-theme', next);
      const root = document.documentElement;
      root.setAttribute('data-theme', next);
      next === 'dark' ? root.classList.add('dark') : root.classList.remove('dark');
      return next;
    });
  }, []);

  return [theme, toggle];
}

// ══════════════════════════════════════════════════════════════════
// Icons
// ══════════════════════════════════════════════════════════════════
const ic = {
  w: 18, h: 18, viewBox: '0 0 24 24', fill: 'none',
  stroke: 'currentColor', strokeWidth: 2,
  strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const,
};

const IconGrid    = () => <svg {...ic}><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg>;
const IconUsers   = () => <svg {...ic}><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>;
const IconSun     = () => <svg {...ic}><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>;
const IconMoon    = () => <svg {...ic}><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>;
const IconLogout  = () => <svg {...ic} width={16} height={16}><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>;
const IconSearch  = () => <svg {...ic} width={14} height={14}><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>;
const IconCaret   = () => <svg width={10} height={10} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2.5} strokeLinecap="round" strokeLinejoin="round"><polyline points="6 9 12 15 18 9"/></svg>;
const IconShield  = () => <svg width={18} height={18} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2.5} strokeLinecap="round" strokeLinejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>;
const IconMenu    = () => <svg {...ic} width={20} height={20}><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>;
const IconSettings = () => <svg {...ic} width={16} height={16}><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>;
const IconArrowLeft = () => <svg width={16} height={16} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>;
const IconClose = () => <svg width={16} height={16} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={2} strokeLinecap="round" strokeLinejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>;
const IconBuilding = () => <svg {...ic}><rect x="4" y="2" width="16" height="20" rx="2" ry="2"/><path d="M9 22v-4h6v4"/><path d="M8 6h.01"/><path d="M16 6h.01"/><path d="M12 6h.01"/><path d="M12 10h.01"/><path d="M12 14h.01"/><path d="M16 10h.01"/><path d="M16 14h.01"/><path d="M8 10h.01"/><path d="M8 14h.01"/></svg>;

// ══════════════════════════════════════════════════════════════════
// Nav Items — Profile removed (accessible via avatar dropdown)
// ══════════════════════════════════════════════════════════════════
interface NavItem { label: string; href: string; icon: React.ReactNode; description: string; }

const NAV_ITEMS: NavItem[] = [
  { label: 'Dashboard', href: '/dashboard', icon: <IconGrid />, description: 'Overview & metrics' },
  { label: 'Users', href: '/users', icon: <IconUsers />, description: 'Manage system users' },
  { label: 'Company Profiles', href: '/company-data', icon: <IconBuilding />, description: 'Corporate entities' },
];

// ══════════════════════════════════════════════════════════════════
// ExpandableSearch — Desktop: expands to 320px. Mobile: full-width overlay.
// ══════════════════════════════════════════════════════════════════
function ExpandableSearch(): React.JSX.Element {
  const [expanded, setExpanded] = React.useState<boolean>(false);
  const [value, setValue] = React.useState<string>('');
  const inputRef = React.useRef<HTMLInputElement>(null);
  const mobileInputRef = React.useRef<HTMLInputElement>(null);

  function open(): void {
    setExpanded(true);
    setTimeout(() => {
      inputRef.current?.focus();
      mobileInputRef.current?.focus();
    }, 80);
  }
  function close(): void { if (!value) setExpanded(false); }
  function dismiss(): void { setValue(''); setExpanded(false); }

  return (
    <>
      {/* ── Desktop expandable (hidden on mobile) ── */}
      <div
        className="relative hidden items-center transition-all duration-300 lg:flex"
        style={{ width: expanded ? 320 : 36 }}
      >
        <button
          onClick={open}
          className="absolute left-0 z-10 flex h-9 w-9 items-center justify-center rounded-lg transition-all duration-200"
          style={{
            color: expanded ? 'var(--color-aqua)' : 'var(--text-muted)',
            background: expanded ? 'color-mix(in srgb, var(--color-aqua) 10%, transparent)' : 'var(--bg-card)',
            border: '1px solid var(--border-default)',
          }}
          aria-label="Search"
        >
          <IconSearch />
        </button>
        <input
          ref={inputRef}
          type="text"
          value={value}
          onChange={(e) => setValue(e.target.value)}
          onBlur={close}
          onKeyDown={(e) => { if (e.key === 'Escape') dismiss(); }}
          placeholder="Search..."
          className="h-9 w-full rounded-lg pl-10 pr-3 text-sm outline-none transition-all duration-300"
          style={{
            background: 'var(--bg-card)',
            border: '1px solid var(--border-default)',
            color: 'var(--text-primary)',
            fontFamily: 'var(--font-sans)',
            opacity: expanded ? 1 : 0,
            pointerEvents: expanded ? 'auto' : 'none',
          }}
        />
      </div>

      {/* ── Mobile: icon trigger (visible <lg) ── */}
      <button
        onClick={open}
        className="flex h-9 w-9 items-center justify-center rounded-lg transition-all duration-200 lg:hidden"
        style={{
          color: 'var(--text-muted)',
          background: 'var(--bg-card)',
          border: '1px solid var(--border-default)',
        }}
        aria-label="Search"
      >
        <IconSearch />
      </button>

      {/* ── Mobile: full-width overlay bar ── */}
      {expanded && (
        <div
          className="fixed inset-x-0 top-0 z-60 flex h-[60px] items-center gap-3 px-4 lg:hidden"
          style={{
            background: 'var(--bg-surface)',
            borderBottom: '1px solid var(--border-subtle)',
            boxShadow: '0 4px 20px color-mix(in srgb, #000 30%, transparent)',
          }}
        >
          <span style={{ color: 'var(--color-aqua)' }}><IconSearch /></span>
          <input
            ref={mobileInputRef}
            type="text"
            value={value}
            onChange={(e) => setValue(e.target.value)}
            onKeyDown={(e) => { if (e.key === 'Escape') dismiss(); }}
            placeholder="Search..."
            className="h-9 flex-1 rounded-lg px-3 text-sm outline-none"
            style={{
              background: 'var(--bg-card)',
              border: '1px solid var(--border-default)',
              color: 'var(--text-primary)',
              fontFamily: 'var(--font-sans)',
            }}
          />
          <button
            onClick={dismiss}
            className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-all"
            style={{ color: 'var(--text-muted)', background: 'var(--bg-hover)', border: '1px solid var(--border-default)' }}
            aria-label="Close search"
          >
            <IconClose />
          </button>
        </div>
      )}
    </>
  );
}

// ══════════════════════════════════════════════════════════════════
// Avatar Dropdown — Simplified: Settings + Sign out only
// ══════════════════════════════════════════════════════════════════
function AvatarDropdown(): React.JSX.Element {
  const { auth } = usePage<AuthPageProps>().props;
  const user = auth.user;
  const [open, setOpen] = React.useState<boolean>(false);
  const ref = React.useRef<HTMLDivElement>(null);

  // Close on outside click
  React.useEffect(() => {
    function handler(e: MouseEvent): void {
      if (ref.current && !ref.current.contains(e.target as Node)) setOpen(false);
    }
    document.addEventListener('mousedown', handler);
    return () => document.removeEventListener('mousedown', handler);
  }, []);

  const initials = [
    (user?.name?.[0] ?? 'U').toUpperCase(),
    (user?.last_name?.[0] ?? '').toUpperCase(),
  ].join('');

  const hasPhoto = !!user?.profile_photo_path;

  return (
    <div ref={ref} className="relative">
      <button
        onClick={() => setOpen((p) => !p)}
        className="flex items-center gap-2 rounded-lg p-1 pr-2 transition-all duration-150"
        style={{
          background: open ? 'var(--bg-hover)' : 'transparent',
          border: '1px solid',
          borderColor: open ? 'var(--border-hover)' : 'var(--border-subtle)',
        }}
        aria-label="Account menu"
      >
        {/* Avatar */}
        {hasPhoto ? (
          <img
            src={user!.profile_photo_path!}
            alt={user?.name}
            className="h-7 w-7 rounded-md object-cover"
          />
        ) : (
          <div
            className="flex h-7 w-7 shrink-0 items-center justify-center rounded-md text-[11px] font-bold"
            style={{
              background: 'linear-gradient(135deg, var(--color-aqua) 0%, var(--color-aqua-dark) 100%)',
              color: 'var(--color-white)',
            }}
          >
            {initials}
          </div>
        )}
        {/* Name */}
        <span
          className="hidden text-[12px] font-semibold sm:block"
          style={{ color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}
        >
          {user?.name}
        </span>
        <span style={{ color: 'var(--text-disabled)' }}><IconCaret /></span>
      </button>

      {/* Dropdown */}
      {open && (
        <div
          className="absolute right-0 top-full z-50 mt-2 w-48 rounded-xl p-1"
          style={{
            background: 'var(--bg-surface)',
            border: '1px solid var(--border-default)',
            boxShadow: '0 8px 32px color-mix(in srgb, #000 24%, transparent)',
          }}
        >
          {/* User info header */}
          <div
            className="mb-1 rounded-lg px-3 py-2.5"
            style={{ background: 'var(--bg-card)' }}
          >
            <p className="text-[13px] font-semibold truncate" style={{ color: 'var(--text-primary)' }}>
              {user?.name} {user?.last_name ?? ''}
            </p>
            <p className="text-[11px] truncate" style={{ color: 'var(--text-disabled)' }}>
              {user?.email}
            </p>
          </div>

          {/* Settings → /profile */}
          <Link
            href="/profile"
            onClick={() => setOpen(false)}
            className="flex items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] font-medium transition-colors"
            style={{ color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}
            onMouseEnter={(e) => { (e.currentTarget as HTMLAnchorElement).style.background = 'var(--bg-hover)'; }}
            onMouseLeave={(e) => { (e.currentTarget as HTMLAnchorElement).style.background = 'transparent'; }}
          >
            <IconSettings />
            Settings
          </Link>

          {/* Divider */}
          <div className="my-1 h-px" style={{ background: 'var(--border-subtle)' }} />

          {/* Logout */}
          <button
            onClick={() => router.post('/logout')}
            className="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-[13px] font-medium transition-colors"
            style={{ color: 'var(--accent-error)', fontFamily: 'var(--font-sans)' }}
            onMouseEnter={(e) => { (e.currentTarget as HTMLButtonElement).style.background = 'color-mix(in srgb, var(--accent-error) 10%, transparent)'; }}
            onMouseLeave={(e) => { (e.currentTarget as HTMLButtonElement).style.background = 'transparent'; }}
          >
            <IconLogout />
            Sign out
          </button>
        </div>
      )}
    </div>
  );
}

// ══════════════════════════════════════════════════════════════════
// Theme Toggle Button (shared between desktop sidebar & mobile drawer)
// ══════════════════════════════════════════════════════════════════
function ThemeToggle({ theme, onToggle }: { theme: Theme; onToggle: () => void }): React.JSX.Element {
  return (
    <button
      onClick={onToggle}
      className="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 transition-all duration-150"
      style={{ color: 'var(--text-muted)', border: '1px solid transparent', fontFamily: 'var(--font-sans)' }}
      onMouseEnter={(e) => { (e.currentTarget as HTMLButtonElement).style.background = 'var(--bg-hover)'; }}
      onMouseLeave={(e) => { (e.currentTarget as HTMLButtonElement).style.background = 'transparent'; }}
    >
      <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-md" style={{ background: 'var(--bg-hover)' }}>
        {theme === 'dark' ? <IconSun /> : <IconMoon />}
      </span>
      <div className="flex-1 text-left">
        <span className="block text-[13px] font-semibold leading-none"
          style={{ color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}>
          {theme === 'dark' ? 'Light Mode' : 'Dark Mode'}
        </span>
        <span className="block text-[11px] leading-none mt-0.5" style={{ color: 'var(--text-disabled)' }}>
          {theme === 'dark' ? 'Switch to light' : 'Switch to dark'}
        </span>
      </div>
      {/* Animated pill */}
      <span className="relative inline-flex h-5 w-9 shrink-0 rounded-full transition-colors duration-200"
        style={{
          background: theme === 'light' ? 'color-mix(in srgb, var(--color-aqua) 30%, transparent)' : 'var(--bg-hover)',
          border: '1px solid var(--border-default)',
        }}>
        <span className="inline-block h-3.5 w-3.5 rounded-full transition-transform duration-200"
          style={{
            background: theme === 'light' ? 'var(--color-aqua)' : 'var(--text-disabled)',
            transform: theme === 'light' ? 'translate(18px, 2px)' : 'translate(2px, 2px)',
          }} />
      </span>
    </button>
  );
}

// ══════════════════════════════════════════════════════════════════
// Sidebar Content (shared between desktop and mobile)
// ══════════════════════════════════════════════════════════════════
function SidebarContent({ onClose }: { onClose?: () => void }): React.JSX.Element {
  const currentPath = typeof window !== 'undefined' ? window.location.pathname : '';

  return (
    <>
      {/* Logo + close arrow (arrow only visible on mobile) */}
      <div className="flex h-[60px] items-center justify-between px-5 shrink-0"
        style={{ borderBottom: '1px solid var(--border-subtle)' }}>
        <div className="flex items-center gap-3">
          <div className="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg"
            style={{ background: 'linear-gradient(135deg, var(--color-aqua) 0%, var(--color-aqua-dark) 100%)' }}>
            <IconShield />
          </div>
          <div>
            <span className="block text-[13px] font-bold tracking-tight leading-none"
              style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
              AquaShield
            </span>
            <span className="block text-[10px] font-semibold uppercase tracking-widest leading-none mt-0.5"
              style={{ color: 'var(--color-aqua)' }}>
              CRM
            </span>
          </div>
        </div>

        {/* Close arrow — only rendered in mobile drawer */}
        {onClose && (
          <button
            onClick={onClose}
            className="flex h-7 w-7 items-center justify-center rounded-lg transition-all duration-150"
            style={{
              color: 'var(--text-muted)',
              background: 'var(--bg-hover)',
              border: '1px solid var(--border-default)',
            }}
            onMouseEnter={(e) => { (e.currentTarget as HTMLButtonElement).style.color = 'var(--color-aqua)'; }}
            onMouseLeave={(e) => { (e.currentTarget as HTMLButtonElement).style.color = 'var(--text-muted)'; }}
            aria-label="Close menu"
          >
            <IconArrowLeft />
          </button>
        )}
      </div>

      {/* Section label */}
      <div className="px-4 pt-5 pb-2">
        <span className="text-[10px] font-semibold uppercase tracking-[1.8px]"
          style={{ color: 'var(--text-disabled)' }}>
          Navigation
        </span>
      </div>

      {/* Nav items */}
      <nav className="flex-1 space-y-1 px-3">
        {NAV_ITEMS.map((item) => {
          const active = currentPath === item.href || currentPath.startsWith(item.href + '/');
          return (
            <Link
              key={item.href}
              href={item.href}
              onClick={onClose}
              className="group flex items-center gap-3 rounded-lg px-3 py-2.5 transition-all duration-150"
              style={{
                background: active ? 'color-mix(in srgb, var(--color-aqua) 10%, transparent)' : 'transparent',
                color: active ? 'var(--color-aqua)' : 'var(--text-muted)',
                border: active ? '1px solid color-mix(in srgb, var(--color-aqua) 20%, transparent)' : '1px solid transparent',
              }}
            >
              <span className="flex h-7 w-7 shrink-0 items-center justify-center rounded-md transition-all duration-150"
                style={{
                  background: active ? 'color-mix(in srgb, var(--color-aqua) 15%, transparent)' : 'var(--bg-hover)',
                  color: active ? 'var(--color-aqua)' : 'var(--text-muted)',
                }}>
                {item.icon}
              </span>
              <div className="min-w-0 flex-1">
                <span className="block text-[13px] font-semibold leading-none"
                  style={{ color: active ? 'var(--color-aqua)' : 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}>
                  {item.label}
                </span>
                <span className="block text-[11px] leading-none mt-0.5" style={{ color: 'var(--text-disabled)' }}>
                  {item.description}
                </span>
              </div>
              {active && <span className="h-1.5 w-1.5 shrink-0 rounded-full" style={{ background: 'var(--color-aqua)' }} />}
            </Link>
          );
        })}
      </nav>
    </>
  );
}

// ══════════════════════════════════════════════════════════════════
// AppLayout
// ══════════════════════════════════════════════════════════════════
interface AppLayoutProps { children: React.ReactNode; }

export default function AppLayout({ children }: AppLayoutProps): React.JSX.Element {
  const [theme, toggleTheme] = useTheme();
  const [mobileOpen, setMobileOpen] = React.useState<boolean>(false);

  return (
    <div className="min-h-screen" style={{ background: 'var(--bg-app)', fontFamily: 'var(--font-sans)' }}>

      {/* ── Desktop Sidebar (hidden on mobile) ── */}
      <aside
        className="fixed left-0 top-0 z-40 hidden h-screen w-64 flex-col lg:flex"
        style={{ background: 'var(--bg-surface)', borderRight: '1px solid var(--border-subtle)' }}
      >
        <div className="flex flex-1 flex-col overflow-y-auto">
          <SidebarContent />
        </div>

        {/* Desktop theme toggle */}
        <div className="shrink-0 space-y-2 px-3 pb-4 pt-3" style={{ borderTop: '1px solid var(--border-subtle)' }}>
          <ThemeToggle theme={theme} onToggle={toggleTheme} />
        </div>
      </aside>

      {/* ── Mobile Drawer Overlay (always in DOM, animated via CSS) ── */}
      <div
        className="fixed inset-0 z-50 lg:hidden"
        style={{
          pointerEvents: mobileOpen ? 'auto' : 'none',
          visibility: mobileOpen ? 'visible' : 'hidden',
        }}
      >
        {/* Backdrop — fade in */}
        <div
          className="absolute inset-0"
          onClick={() => setMobileOpen(false)}
          style={{
            background: 'color-mix(in srgb, #000 60%, transparent)',
            opacity: mobileOpen ? 1 : 0,
            transition: 'opacity 300ms cubic-bezier(0.4, 0, 0.2, 1)',
          }}
        />

        {/* Drawer — slide in from left */}
        <aside
          className="absolute left-0 top-0 flex h-full w-72 flex-col"
          style={{
            background: 'var(--bg-surface)',
            borderRight: '1px solid var(--border-subtle)',
            transform: mobileOpen ? 'translateX(0)' : 'translateX(-100%)',
            transition: 'transform 300ms cubic-bezier(0.4, 0, 0.2, 1)',
            willChange: 'transform',
          }}
        >
          <div className="flex flex-1 flex-col overflow-y-auto">
            <SidebarContent onClose={() => setMobileOpen(false)} />
          </div>
          {/* Mobile theme toggle */}
          <div className="shrink-0 px-3 pb-4 pt-3" style={{ borderTop: '1px solid var(--border-subtle)' }}>
            <ThemeToggle theme={theme} onToggle={toggleTheme} />
          </div>
        </aside>
      </div>

      {/* ── Main content area ── */}
      <div className="flex flex-col min-h-screen lg:pl-64">


        {/* ── Top Bar ── */}
        <header
          className="sticky top-0 z-30 flex h-[60px] items-center justify-between gap-4 px-4 md:px-6"
          style={{
            background: 'var(--bg-surface)',
            borderBottom: '1px solid var(--border-subtle)',
            backdropFilter: 'blur(12px)',
          }}
        >
          {/* Left: hamburger (mobile) */}
          <button
            className="flex h-9 w-9 items-center justify-center rounded-lg lg:hidden transition-all"
            style={{ color: 'var(--text-muted)', border: '1px solid var(--border-default)', background: 'var(--bg-card)' }}
            onClick={() => setMobileOpen(true)}
            aria-label="Open menu"
          >
            <IconMenu />
          </button>

          {/* Center: logo on mobile */}
          <div className="flex items-center gap-2 lg:hidden">
            <span className="text-[13px] font-bold" style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
              AquaShield
            </span>
          </div>

          {/* Right side: search + avatar */}
          <div className="ml-auto flex items-center gap-3">
            <ExpandableSearch />
            <AvatarDropdown />
          </div>
        </header>

        {/* ── Page Content ── */}
        <main className="flex-1" style={{ background: 'var(--bg-app)' }}>

          <div className="p-4 md:p-6 lg:p-8">{children}</div>
        </main>
      </div>
    </div>
  );
}
