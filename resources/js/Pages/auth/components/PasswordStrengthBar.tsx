import * as React from 'react';

interface PasswordStrengthBarProps {
  password: string;
}

interface StrengthResult {
  score: number;
  label: string;
  color: string;
}

function evaluate(password: string): StrengthResult {
  if (!password) return { score: 0, label: '', color: '' };

  let score = 0;
  if (password.length >= 8) score += 20;
  if (password.length >= 12) score += 15;
  if (password.length >= 16) score += 10;
  if (/[a-z]/.test(password)) score += 10;
  if (/[A-Z]/.test(password)) score += 15;
  if (/[0-9]/.test(password)) score += 15;
  if (/[^a-zA-Z0-9]/.test(password)) score += 15;

  if (score < 30) return { score, label: 'Weak', color: 'var(--accent-error)' };
  if (score < 55) return { score, label: 'Fair', color: 'var(--accent-warning)' };
  if (score < 80) return { score, label: 'Good', color: 'var(--accent-info)' };
  return { score: Math.min(score, 100), label: 'Strong', color: 'var(--accent-success)' };
}

/**
 * PasswordStrengthBar — Visual strength indicator for password fields.
 * Shows a gradient bar + label that updates as the user types.
 */
export function PasswordStrengthBar({ password }: PasswordStrengthBarProps): React.JSX.Element {
  const { score, label, color } = evaluate(password);

  if (!password) return <></>;

  return (
    <div className="mt-2 space-y-1">
      <div
        className="h-1.5 w-full overflow-hidden rounded-full"
        style={{ background: 'rgba(255, 255, 255, 0.08)' }}
      >
        <div
          className="h-full rounded-full transition-all duration-300"
          style={{
            width: `${score}%`,
            background: color,
          }}
        />
      </div>
      <p
        className="text-xs font-medium"
        style={{ color }}
      >
        {label}
      </p>
    </div>
  );
}
