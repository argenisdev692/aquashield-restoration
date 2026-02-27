import * as React from 'react';
import { cn } from '@/lib/utils';

interface OtpInputProps {
  length?: number;
  value: string;
  onChange: (value: string) => void;
  disabled?: boolean;
  hasError?: boolean;
}

/**
 * OtpInput — Individual digit inputs with auto-focus, paste support,
 * and keyboard navigation. Follows WCAG 2.2 accessibility guidelines.
 */
export function OtpInput({
  length = 6,
  value,
  onChange,
  disabled = false,
  hasError = false,
}: OtpInputProps): React.JSX.Element {
  const inputsRef = React.useRef<(HTMLInputElement | null)[]>([]);
  const digits = value.split('').concat(Array(length).fill('')).slice(0, length);

  function focusInput(index: number): void {
    if (index >= 0 && index < length) {
      inputsRef.current[index]?.focus();
    }
  }

  function handleChange(index: number, char: string): void {
    if (!/^\d?$/.test(char)) return;

    const newDigits = [...digits];
    newDigits[index] = char;
    const joined = newDigits.join('').slice(0, length);
    onChange(joined);

    if (char && index < length - 1) {
      focusInput(index + 1);
    }
  }

  function handleKeyDown(index: number, e: React.KeyboardEvent<HTMLInputElement>): void {
    if (e.key === 'Backspace') {
      e.preventDefault();
      if (digits[index]) {
        handleChange(index, '');
      } else if (index > 0) {
        handleChange(index - 1, '');
        focusInput(index - 1);
      }
    } else if (e.key === 'ArrowLeft') {
      e.preventDefault();
      focusInput(index - 1);
    } else if (e.key === 'ArrowRight') {
      e.preventDefault();
      focusInput(index + 1);
    }
  }

  function handlePaste(e: React.ClipboardEvent<HTMLInputElement>): void {
    e.preventDefault();
    const pasted = e.clipboardData.getData('text').replace(/\D/g, '').slice(0, length);
    if (pasted.length > 0) {
      onChange(pasted);
      focusInput(Math.min(pasted.length, length - 1));
    }
  }

  return (
    <div className="flex items-center justify-center gap-3">
      {digits.map((digit, i) => (
        <input
          key={i}
          ref={(el) => { inputsRef.current[i] = el; }}
          type="text"
          inputMode="numeric"
          maxLength={1}
          value={digit}
          disabled={disabled}
          autoComplete="one-time-code"
          aria-label={`Digit ${i + 1} of ${length}`}
          onChange={(e) => handleChange(i, e.target.value)}
          onKeyDown={(e) => handleKeyDown(i, e)}
          onPaste={handlePaste}
          onFocus={(e) => e.target.select()}
          className={cn(
            'h-14 w-12 rounded-lg border text-center text-xl font-bold transition-all duration-200',
            'focus:outline-none focus:ring-2 focus:ring-offset-1',
            hasError
              ? 'border-red-500 text-red-400 focus:ring-red-500/30'
              : 'focus:ring-(--color-aqua)/30 focus:border-(--color-aqua)',
            disabled && 'cursor-not-allowed opacity-50',
          )}
          style={{
            background: 'rgba(255, 255, 255, 0.06)',
            borderColor: hasError ? 'var(--accent-error)' : 'rgba(255, 255, 255, 0.12)',
            color: 'var(--color-white)',
            fontFamily: 'var(--font-mono)',
          }}
        />
      ))}
    </div>
  );
}
