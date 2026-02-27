/* ══════════════════════════════════════════════════════════════════
   cn() — clsx + tailwind-merge utility
   Per ARQUITECTURE-REACT-INERTIA.md — common/helpers/cn.ts
   ══════════════════════════════════════════════════════════════════ */
import { type ClassValue, clsx } from 'clsx';
import { twMerge } from 'tailwind-merge';

export function cn(...inputs: ClassValue[]): string {
  return twMerge(clsx(inputs));
}
