export function toUsPhoneDigits(value: string): string {
  const digits = value.replace(/\D/g, '');

  if (digits.startsWith('1') && digits.length > 10) {
    return digits.slice(1, 11);
  }

  return digits.slice(0, 10);
}

export function formatUsPhoneInput(value: string): string {
  const digits = toUsPhoneDigits(value);

  if (digits.length === 0) {
    return '';
  }

  if (digits.length < 4) {
    return `(${digits}`;
  }

  if (digits.length < 7) {
    return `(${digits.slice(0, 3)}) ${digits.slice(3)}`;
  }

  return `(${digits.slice(0, 3)}) ${digits.slice(3, 6)}-${digits.slice(6, 10)}`;
}

export function normalizeUsPhoneForPayload(value: string): string | null {
  const digits = toUsPhoneDigits(value);

  if (digits.length !== 10) {
    return null;
  }

  return `+1${digits}`;
}

export function hasCompleteUsPhone(value: string): boolean {
  return toUsPhoneDigits(value).length === 10;
}
