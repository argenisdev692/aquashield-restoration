/* ══════════════════════════════════════════════════════════════════
   Shared React Prop Utility Types
   Per ARQUITECTURE-REACT-INERTIA.md — types/props.ts
   ══════════════════════════════════════════════════════════════════ */
import type { ClassValue } from 'clsx';

export type PropsWithClassName<T = unknown> = T & { className?: ClassValue };
export type PropsWithChildren<T = unknown> = T & { children: React.ReactNode };
export type PropsWithOptionalChildren<T = unknown> = T & { children?: React.ReactNode };
