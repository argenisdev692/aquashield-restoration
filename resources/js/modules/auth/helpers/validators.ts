/* ══════════════════════════════════════════════════════════════════
   Auth Form Validators
   Client-side validation before submit
   ══════════════════════════════════════════════════════════════════ */

export interface ValidationResult {
  valid: boolean;
  errors: Record<string, string>;
}

const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PHONE_REGEX = /^\+?[1-9]\d{6,14}$/;

export function isEmail(value: string): boolean {
  return EMAIL_REGEX.test(value.trim());
}

export function isPhone(value: string): boolean {
  return PHONE_REGEX.test(value.replace(/[\s\-()]/g, ''));
}

export function validateLoginPassword(email: string, password: string): ValidationResult {
  const errors: Record<string, string> = {};

  if (!email.trim()) {
    errors.email = 'Email is required';
  } else if (!isEmail(email)) {
    errors.email = 'Enter a valid email address';
  }

  if (!password) {
    errors.password = 'Password is required';
  } else if (password.length < 6) {
    errors.password = 'Password must be at least 6 characters';
  }

  return { valid: Object.keys(errors).length === 0, errors };
}

export function validateOtpRequest(identifier: string): ValidationResult {
  const errors: Record<string, string> = {};

  if (!identifier.trim()) {
    errors.identifier = 'Email is required';
  } else if (!isEmail(identifier)) {
    errors.identifier = 'Enter a valid email address';
  }

  return { valid: Object.keys(errors).length === 0, errors };
}

export function validateOtpCode(code: string): ValidationResult {
  const errors: Record<string, string> = {};

  if (!code || code.length !== 6) {
    errors.otp = 'Enter the complete 6-digit code';
  } else if (!/^\d{6}$/.test(code)) {
    errors.otp = 'Code must contain only digits';
  }

  return { valid: Object.keys(errors).length === 0, errors };
}

export function validateForgotPasswordEmail(email: string): ValidationResult {
  const errors: Record<string, string> = {};

  if (!email.trim()) {
    errors.email = 'Email is required';
  } else if (!isEmail(email)) {
    errors.email = 'Enter a valid email address';
  }

  return { valid: Object.keys(errors).length === 0, errors };
}

export function validateNewPassword(password: string, confirmation: string): ValidationResult {
  const errors: Record<string, string> = {};

  if (!password) {
    errors.password = 'Password is required';
  } else if (password.length < 8) {
    errors.password = 'Password must be at least 8 characters';
  }

  if (!confirmation) {
    errors.password_confirmation = 'Please confirm your password';
  } else if (password !== confirmation) {
    errors.password_confirmation = 'Passwords do not match';
  }

  return { valid: Object.keys(errors).length === 0, errors };
}

export function validateProfile(data: {
  name: string;
  email: string;
  phone?: string | null;
}): ValidationResult {
  const errors: Record<string, string> = {};

  if (!data.name.trim()) {
    errors.name = 'Name is required';
  } else if (data.name.trim().length < 2) {
    errors.name = 'Name must be at least 2 characters';
  }

  if (!data.email.trim()) {
    errors.email = 'Email is required';
  } else if (!isEmail(data.email)) {
    errors.email = 'Enter a valid email address';
  }

  if (data.phone && !isPhone(data.phone)) {
    errors.phone = 'Enter a valid phone number';
  }

  return { valid: Object.keys(errors).length === 0, errors };
}
