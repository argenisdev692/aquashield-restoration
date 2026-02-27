/* ══════════════════════════════════════════════════════════════════
   Avatar — User avatar with initials fallback
   Per ARQUITECTURE-REACT-INERTIA.md — modules/auth/components/
   ══════════════════════════════════════════════════════════════════ */

interface AvatarProps {
  name: string;
  lastName?: string | null;
  photoUrl?: string | null;
  size?: 'sm' | 'md' | 'lg';
  className?: string;
}

const sizeMap = {
  sm: { container: '32px', font: '12px' },
  md: { container: '40px', font: '14px' },
  lg: { container: '56px', font: '20px' },
} as const;

export function Avatar({
  name,
  lastName,
  photoUrl,
  size = 'md',
  className,
}: AvatarProps): React.JSX.Element {
  const initials = `${name.charAt(0)}${lastName?.charAt(0) ?? ''}`.toUpperCase();
  const dimensions = sizeMap[size];

  if (photoUrl) {
    return (
      <img
        src={photoUrl}
        alt={`${name} ${lastName ?? ''}`.trim()}
        className={className}
        style={{
          width: dimensions.container,
          height: dimensions.container,
          borderRadius: '50%',
          objectFit: 'cover',
        }}
      />
    );
  }

  return (
    <div
      className={className}
      style={{
        width: dimensions.container,
        height: dimensions.container,
        borderRadius: '50%',
        background: 'var(--accent-primary)',
        color: 'var(--bg-app)',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        fontSize: dimensions.font,
        fontWeight: 600,
        fontFamily: 'var(--font-sans)',
      }}
    >
      {initials}
    </div>
  );
}
