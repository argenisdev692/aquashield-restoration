import * as React from 'react';
import { invoiceStatusConfig } from '../helpers/invoiceStatusColor';
import type { InvoiceStatus } from '../types';

interface InvoiceStatusBadgeProps {
    status: InvoiceStatus;
}

export function InvoiceStatusBadge({ status }: InvoiceStatusBadgeProps): React.JSX.Element {
    const { label, accent } = invoiceStatusConfig(status);

    return (
        <span
            style={{
                display: 'inline-flex',
                alignItems: 'center',
                gap: 5,
                padding: '2px 10px',
                borderRadius: 999,
                fontSize: 11,
                fontWeight: 700,
                fontFamily: 'var(--font-sans)',
                letterSpacing: '0.04em',
                background: `color-mix(in srgb, ${accent} 15%, transparent)`,
                color: accent,
                border: `1px solid color-mix(in srgb, ${accent} 30%, transparent)`,
                whiteSpace: 'nowrap',
            }}
        >
            <span
                style={{
                    width: 6,
                    height: 6,
                    borderRadius: '50%',
                    background: accent,
                    flexShrink: 0,
                }}
            />
            {label}
        </span>
    );
}
