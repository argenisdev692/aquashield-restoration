import * as React from 'react';

interface PrecipitationParticle {
  left: string;
  delay: string;
  duration: string;
  width: string;
  height: string;
  opacity: number;
  driftX: string;
}

interface HailParticle {
  left: string;
  delay: string;
  duration: string;
  size: string;
  opacity: number;
  driftX: string;
}

interface StormParticlesBackgroundProps {
  className?: string;
}

type ParticleStyle = React.CSSProperties & {
  '--particle-drift-x': string;
};

const RAIN_PARTICLES: PrecipitationParticle[] = Array.from({ length: 30 }, (_, index) => {
  const particleIndex = index + 1;
  const width = particleIndex % 3 === 0 ? 2 : 1;
  const height = 42 + ((particleIndex * 11) % 34);
  const opacity = 0.18 + ((particleIndex % 5) * 0.06);
  const duration = 7.4 + ((particleIndex * 0.47) % 2.8);
  const delay = (particleIndex * 0.31) % 4.2;
  const drift = -8 + ((particleIndex * 3) % 14);

  return {
    left: `${(particleIndex * 3.17) % 100}%`,
    delay: `-${delay.toFixed(2)}s`,
    duration: `${duration.toFixed(2)}s`,
    width: `${width}px`,
    height: `${height}px`,
    opacity,
    driftX: `${drift}px`,
  };
});

const HAIL_PARTICLES: HailParticle[] = Array.from({ length: 14 }, (_, index) => {
  const particleIndex = index + 1;
  const size = 4 + ((particleIndex * 2) % 4);
  const duration = 6.1 + ((particleIndex * 0.53) % 2.2);
  const delay = (particleIndex * 0.41) % 3.6;
  const drift = -12 + ((particleIndex * 5) % 20);

  return {
    left: `${(particleIndex * 7.9) % 100}%`,
    delay: `-${delay.toFixed(2)}s`,
    duration: `${duration.toFixed(2)}s`,
    size: `${size}px`,
    opacity: 0.28 + ((particleIndex % 4) * 0.08),
    driftX: `${drift}px`,
  };
});

export function StormParticlesBackground({ className = '' }: StormParticlesBackgroundProps): React.JSX.Element {
  return (
    <div
      className={`pointer-events-none absolute inset-0 overflow-hidden ${className}`}
      aria-hidden="true"
    >
      <div
        className="absolute inset-0"
        style={{
          background: 'linear-gradient(180deg, color-mix(in srgb, var(--color-white) 4%, transparent) 0%, transparent 26%, color-mix(in srgb, var(--accent-primary) 8%, transparent) 100%)',
        }}
      />

      {RAIN_PARTICLES.map((particle, index) => (
        <span
          key={`rain-${index}`}
          className="absolute block rounded-full"
          style={{
            top: '-24%',
            left: particle.left,
            width: particle.width,
            height: particle.height,
            opacity: particle.opacity,
            background: 'linear-gradient(180deg, color-mix(in srgb, var(--color-white) 78%, transparent) 0%, color-mix(in srgb, var(--accent-primary) 28%, transparent) 100%)',
            boxShadow: '0 0 12px color-mix(in srgb, var(--accent-primary) 18%, transparent)',
            transform: 'translate3d(0, 0, 0) rotate(11deg)',
            animation: `auth-rain-fall ${particle.duration} linear ${particle.delay} infinite`,
            '--particle-drift-x': particle.driftX,
          } as ParticleStyle}
        />
      ))}

      {HAIL_PARTICLES.map((particle, index) => (
        <span
          key={`hail-${index}`}
          className="absolute block rounded-full"
          style={{
            top: '-16%',
            left: particle.left,
            width: particle.size,
            height: particle.size,
            opacity: particle.opacity,
            background: 'radial-gradient(circle at 30% 30%, color-mix(in srgb, var(--color-white) 96%, transparent) 0%, color-mix(in srgb, var(--accent-secondary) 34%, transparent) 70%, color-mix(in srgb, var(--accent-primary) 12%, transparent) 100%)',
            boxShadow: '0 0 14px color-mix(in srgb, var(--color-white) 18%, transparent)',
            animation: `auth-hail-fall ${particle.duration} linear ${particle.delay} infinite`,
            '--particle-drift-x': particle.driftX,
          } as ParticleStyle}
        />
      ))}
    </div>
  );
}
