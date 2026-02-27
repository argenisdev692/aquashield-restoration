import type { PasswordStrength } from '@/types/auth';

interface PasswordStrengthResult {
  level: PasswordStrength;
  score: number; // 0-100
  label: string;
  color: string; // CSS variable reference
}

export function evaluatePasswordStrength(password: string): PasswordStrengthResult {
  let score = 0;

  if (password.length === 0) {
    return { level: 'weak', score: 0, label: 'Too short', color: 'var(--accent-error)' };
  }

  // Length scoring
  if (password.length >= 8) score += 20;
  if (password.length >= 12) score += 15;
  if (password.length >= 16) score += 10;

  // Character variety
  if (/[a-z]/.test(password)) score += 10;
  if (/[A-Z]/.test(password)) score += 15;
  if (/[0-9]/.test(password)) score += 15;
  if (/[^a-zA-Z0-9]/.test(password)) score += 15;

  // Not a common pattern
  const commonPatterns = ['123456', 'password', 'qwerty', 'abc123'];
  const isCommon = commonPatterns.some((p) => password.toLowerCase().includes(p));
  if (!isCommon) score = Math.min(score, 100);
  else score = Math.max(score - 30, 0);

  if (score < 30) {
    return { level: 'weak', score, label: 'Weak', color: 'var(--accent-error)' };
  }
  if (score < 55) {
    return { level: 'fair', score, label: 'Fair', color: 'var(--accent-warning)' };
  }
  if (score < 80) {
    return { level: 'good', score, label: 'Good', color: 'var(--accent-info)' };
  }
  return { level: 'strong', score, label: 'Strong', color: 'var(--accent-success)' };
}
