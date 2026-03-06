/* ══════════════════════════════════════════════════════════════════
   Inertia PageProps Augmentation
   Per ARQUITECTURE-REACT-INERTIA.md — types/inertia.d.ts
   ══════════════════════════════════════════════════════════════════ */
import type { AxiosStatic } from 'axios';
import type Echo from 'laravel-echo';
import type { PageProps as InertiaPageProps } from '@inertiajs/core';
import type Pusher from 'pusher-js';

interface AuthUser {
  id: number;
  uuid: string;
  name: string;
  last_name: string | null;
  username: string | null;
  email: string;
  email_verified_at: string | null;
  phone: string | null;
  profile_photo_path: string | null;
  roles: string[];
  permissions: string[];
}

declare global {
  interface Window {
    axios: AxiosStatic;
    Pusher: typeof Pusher;
    Echo: Echo;
  }
}

declare module '@inertiajs/core' {
  interface PageProps extends InertiaPageProps {
    auth: { user: AuthUser | null };
    flash: { success?: string; error?: string; warning?: string };
    ziggy: { url: string; port: number | null; routes: Record<string, unknown> };
  }
}

export {};
