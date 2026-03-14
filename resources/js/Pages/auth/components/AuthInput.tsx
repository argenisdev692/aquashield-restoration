import * as React from 'react';
import { cn } from '@/lib/utils';

interface AuthInputProps extends React.ComponentProps<'input'> {
  label: string;
  error?: string;
  rightElement?: React.ReactNode;
}

/**
 * AuthInput — Styled input for auth forms with floating-label aesthetic,
 * error states, and optional right-side element (e.g., show/hide toggle).
 */
export function AuthInput({
  label,
  error,
  rightElement,
  className,
  id,
  ...props
}: AuthInputProps): React.JSX.Element {
  const inputId = id ?? label.toLowerCase().replace(/\s+/g, '-');

  return (
    <div className="space-y-1.5">
      <label
        htmlFor={inputId}
        className="block text-xs font-semibold uppercase tracking-wider cursor-pointer"
        style={{ color: 'var(--text-secondary)' }}
      >
        {label}
      </label>
      <div className="relative">
        <input
          id={inputId}
          className={cn(
            'h-11 w-full rounded-lg border px-4 text-sm transition-all duration-200',
            'placeholder:text-(--text-secondary)',
            'focus:outline-none focus:ring-2 focus:ring-offset-1',
            error
              ? 'border-(--accent-error) focus:ring-(--accent-error)/20'
              : 'focus:border-(--accent-primary) focus:ring-(--accent-primary)/20',
            rightElement && 'pr-12',
            className,
          )}
          style={{
            background: 'var(--input-bg)',
            borderColor: error ? 'var(--accent-error)' : 'var(--input-border)',
            color: 'var(--text-primary)',
            fontFamily: 'var(--font-sans)',
          }}
          aria-invalid={!!error}
          aria-describedby={error ? `${inputId}-error` : undefined}
          {...props}
        />
        {rightElement && (
          <div className="absolute inset-y-0 right-0 flex items-center pr-3">
            {rightElement}
          </div>
        )}
      </div>
      {error && (
        <p
          id={`${inputId}-error`}
          className="flex items-center gap-1 text-xs font-medium"
          style={{ color: 'var(--accent-error)' }}
          role="alert"
        >
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
            <circle cx="12" cy="12" r="10" />
            <line x1="12" y1="8" x2="12" y2="12" />
            <line x1="12" y1="16" x2="12.01" y2="16" />
          </svg>
          {error}
        </p>
      )}
    </div>
  );
}
