import * as React from 'react';

const TYPE_COLORS: Record<string, string> = {
    contract:    'var(--accent-primary)',
    estimate:    'var(--accent-info)',
    invoice:     'var(--accent-warning)',
    report:      'var(--accent-secondary)',
    form:        'var(--accent-success)',
    certificate: 'var(--accent-primary)',
    other:       'var(--text-muted)',
};

interface DocumentTemplateTypeBadgeProps {
    type: string;
}

export default function DocumentTemplateTypeBadge({
    type,
}: DocumentTemplateTypeBadgeProps): React.JSX.Element {
    const normalized = type.toLowerCase();
    const color = TYPE_COLORS[normalized] ?? 'var(--text-muted)';

    return (
        <span
            style={{
                display: 'inline-flex',
                alignItems: 'center',
                padding: '2px 10px',
                borderRadius: 'var(--radius-sm)',
                fontSize: '11px',
                fontWeight: 600,
                fontFamily: 'var(--font-sans)',
                letterSpacing: '0.04em',
                textTransform: 'capitalize',
                background: `color-mix(in srgb, ${color} 15%, transparent)`,
                color,
                border: `1px solid color-mix(in srgb, ${color} 30%, transparent)`,
            }}
        >
            {type}
        </span>
    );
}
