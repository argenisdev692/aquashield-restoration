import * as React from 'react';

interface MeshOrb {
  color: string;
  size: string;
  top: string;
  left: string;
  blur: string;
  animation: string;
}

interface GradientMeshProps {
  variant?: 'auth' | 'dashboard';
  className?: string;
}

const AUTH_ORBS: MeshOrb[] = [
  {
    color: 'var(--accent-primary)',
    size: '45%',
    top: '-15%',
    left: '60%',
    blur: '120px',
    animation: 'mesh-float-1 12s ease-in-out infinite',
  },
  {
    color: 'var(--accent-secondary)',
    size: '40%',
    top: '55%',
    left: '-10%',
    blur: '140px',
    animation: 'mesh-float-2 15s ease-in-out infinite',
  },
  {
    color: 'var(--accent-primary)',
    size: '35%',
    top: '30%',
    left: '40%',
    blur: '100px',
    animation: 'mesh-float-3 18s ease-in-out infinite',
  },
];

const DASHBOARD_ORBS: MeshOrb[] = [
  {
    color: 'var(--accent-primary)',
    size: '30%',
    top: '-10%',
    left: '70%',
    blur: '140px',
    animation: 'mesh-float-1 14s ease-in-out infinite',
  },
  {
    color: 'var(--accent-secondary)',
    size: '25%',
    top: '60%',
    left: '-5%',
    blur: '120px',
    animation: 'mesh-float-2 18s ease-in-out infinite',
  },
  {
    color: 'var(--accent-success)',
    size: '20%',
    top: '20%',
    left: '30%',
    blur: '100px',
    animation: 'mesh-float-3 20s ease-in-out infinite',
  },
];

export function GradientMesh({ variant = 'auth', className = '' }: GradientMeshProps): React.JSX.Element {
  const orbs = variant === 'auth' ? AUTH_ORBS : DASHBOARD_ORBS;

  return (
    <div
      className={`pointer-events-none absolute inset-0 overflow-hidden ${className}`}
      aria-hidden="true"
    >
      {orbs.map((orb, i) => (
        <div
          key={i}
          className="absolute rounded-full"
          style={{
            width: orb.size,
            height: orb.size,
            top: orb.top,
            left: orb.left,
            background: orb.color,
            filter: `blur(${orb.blur})`,
            opacity: 0.15,
            animation: orb.animation,
            willChange: 'transform, opacity',
          }}
        />
      ))}

      {/* Noise / grain overlay for depth — Vercel-style */}
      <div
        className="absolute inset-0"
        style={{
          backdropFilter: 'blur(60px)',
          WebkitBackdropFilter: 'blur(60px)',
        }}
      />
    </div>
  );
}
