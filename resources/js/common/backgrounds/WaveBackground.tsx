import * as React from 'react';

interface WaveLayer {
  color: string;
  opacity: number;
  duration: string;
  yOffset: number;
  amplitude: number;
}

interface WaveBackgroundProps {
  variant?: 'auth' | 'dashboard';
  className?: string;
}

const AUTH_LAYERS: WaveLayer[] = [
  { color: 'var(--accent-primary)',   opacity: 0.08, duration: '18s', yOffset: 0,  amplitude: 20 },
  { color: 'var(--accent-secondary)', opacity: 0.06, duration: '14s', yOffset: 10, amplitude: 15 },
  { color: 'var(--accent-primary)',   opacity: 0.04, duration: '22s', yOffset: 20, amplitude: 25 },
];

const DASHBOARD_LAYERS: WaveLayer[] = [
  { color: 'var(--accent-primary)',   opacity: 0.05, duration: '20s', yOffset: 0,  amplitude: 18 },
  { color: 'var(--accent-secondary)', opacity: 0.04, duration: '16s', yOffset: 8,  amplitude: 12 },
  { color: 'var(--accent-success)',   opacity: 0.03, duration: '24s', yOffset: 16, amplitude: 22 },
];

function buildSinePath(amplitude: number, yOffset: number): string {
  const w = 1440;
  const h = 320;
  const mid = h / 2 + yOffset;
  const a = amplitude;

  return [
    `M0 ${mid}`,
    `C${w * 0.125} ${mid - a}, ${w * 0.25} ${mid + a}, ${w * 0.375} ${mid}`,
    `C${w * 0.5} ${mid - a}, ${w * 0.625} ${mid + a}, ${w * 0.75} ${mid}`,
    `C${w * 0.875} ${mid - a}, ${w} ${mid + a}, ${w * 1.125} ${mid}`,
    `C${w * 1.25} ${mid - a}, ${w * 1.375} ${mid + a}, ${w * 1.5} ${mid}`,
    `C${w * 1.625} ${mid - a}, ${w * 1.75} ${mid + a}, ${w * 1.875} ${mid}`,
    `C${w * 2} ${mid - a}, ${w * 2} ${mid}, ${w * 2} ${mid}`,
    `V${h} H0 Z`,
  ].join(' ');
}

export function WaveBackground({ variant = 'auth', className = '' }: WaveBackgroundProps): React.JSX.Element {
  const layers = variant === 'auth' ? AUTH_LAYERS : DASHBOARD_LAYERS;

  return (
    <div
      className={`pointer-events-none absolute inset-0 overflow-hidden ${className}`}
      aria-hidden="true"
    >
      {layers.map((layer, i) => (
        <div
          key={i}
          className="absolute bottom-0 left-0 h-[320px]"
          style={{ width: '200%' }}
        >
          <svg
            viewBox="0 0 2880 320"
            preserveAspectRatio="none"
            className="h-full w-full"
            style={{
              animation: `wave-slide ${layer.duration} linear infinite`,
            }}
          >
            <path
              d={buildSinePath(layer.amplitude, layer.yOffset)}
              fill={layer.color}
              fillOpacity={layer.opacity}
            />
          </svg>
        </div>
      ))}
    </div>
  );
}
