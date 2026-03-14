import * as React from 'react';
import { WaveBackground, GradientMesh, StormParticlesBackground } from '@/common/backgrounds';

interface AuthLayoutProps {
  children: React.ReactNode;
}

/**
 * AuthLayout — Unauthenticated layout for Login, Register, Forgot Password.
 * Full-screen centered card with AquaShield branding.
 */
export default function AuthLayout({ children }: AuthLayoutProps): React.JSX.Element {
  return (
    <div
      className="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-8"
      style={{
        background: 'radial-gradient(circle at top right, color-mix(in srgb, var(--accent-secondary) 18%, transparent), transparent 40%), radial-gradient(circle at bottom left, color-mix(in srgb, var(--accent-primary) 20%, transparent), var(--bg-app) 55%)',
      }}
    >
      {/* Gradient mesh — blurred orbs (Vercel / Vite style) */}
      <GradientMesh variant="auth" className="fixed" />

      <StormParticlesBackground className="fixed" />

      {/* Animated SVG waves at bottom */}
      <WaveBackground variant="auth" className="fixed" />

      <div className="relative z-10 w-full max-w-md">
        {/* Logo / Brand */}
        <div className="mb-8 text-center">
          <div
            className="mx-auto flex h-20 w-20 items-center justify-center rounded-2xl"
            style={{
              background: 'var(--bg-surface)',
              boxShadow: '0 8px 32px color-mix(in srgb, var(--bg-base) 50%, transparent)',
              border: '1px solid var(--border-strong)',
            }}
          >
            <img 
              src="/img/Logo PNG-WHITE.png" 
              alt="AquaShield Logo" 
              className="h-14 w-auto object-contain drop-shadow-md"
            />
          </div>
          <h1
            className="mt-4 text-2xl font-bold tracking-tight"
            style={{ color: 'var(--text-primary)' }}
          >
            AquaShield
          </h1>
          <p
            className="mt-1 text-sm"
            style={{ color: 'var(--text-secondary)' }}
          >
            Customer Relationship Management
          </p>
        </div>

        {/* Auth Card */}
        <div
          className="card rounded-2xl p-8"
          style={{
            backdropFilter: 'blur(24px)',
            boxShadow: '0 24px 64px color-mix(in srgb, var(--bg-base) 40%, transparent)',
          }}
        >
          {children}
        </div>

        {/* Footer */}
        <p
          className="mt-6 text-center text-xs font-medium"
          style={{
            background: 'var(--grad-text)',
            WebkitBackgroundClip: 'text',
            WebkitTextFillColor: 'transparent',
            backgroundClip: 'text',
            opacity: 0.7,
          }}
        >
          © {new Date().getFullYear()} AquaShield Restoration. All rights reserved.
        </p>
      </div>
    </div>
  );
}
