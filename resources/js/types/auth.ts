import type { AuthUser } from './api';

// ── Auth modes ─────────────────────────────────────────────────
export type AuthMode = 'otp' | 'password';
export type ForgotPasswordStep = 'email' | 'otp' | 'reset';
export type FormStatus = 'idle' | 'loading' | 'error' | 'success';

// ── Inertia Page Props ──────────────────────────────────────
export interface AuthPageProps {
  auth: { user: AuthUser | null };
  flash: { success?: string; error?: string; warning?: string };
  errors: Record<string, string>;
  ziggy: { url: string; port: number | null; routes: Record<string, unknown> };
  [key: string]: unknown;
}

export interface ProfilePageProps extends AuthPageProps {
  auth: { user: AuthUser };
}

// ── Password Strength ───────────────────────────────────────
export type PasswordStrength = 'weak' | 'fair' | 'good' | 'strong';

