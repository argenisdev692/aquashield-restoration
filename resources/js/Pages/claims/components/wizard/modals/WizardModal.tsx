import * as React from 'react';
import { createPortal } from 'react-dom';
import { motion, AnimatePresence } from 'framer-motion';
import { X } from 'lucide-react';

export interface WizardModalProps {
    open: boolean;
    onClose: () => void;
    title: string;
    subtitle?: string;
    icon?: React.ReactNode;
    accentColor?: string;
    children: React.ReactNode;
    maxWidth?: number;
}

export function WizardModal({
    open,
    onClose,
    title,
    subtitle,
    icon,
    accentColor = 'var(--accent-primary)',
    children,
    maxWidth = 520,
}: WizardModalProps): React.ReactPortal {
    React.useEffect(() => {
        if (!open) return;
        const onKey = (e: KeyboardEvent): void => { if (e.key === 'Escape') onClose(); };
        document.addEventListener('keydown', onKey);
        return () => document.removeEventListener('keydown', onKey);
    }, [open, onClose]);

    React.useEffect(() => {
        document.body.style.overflow = open ? 'hidden' : '';
        return () => { document.body.style.overflow = ''; };
    }, [open]);

    const el = document.getElementById('app') ?? document.body;

    return createPortal(
        <AnimatePresence>
            {open && (
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.18 }}
                    onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
                    style={{
                        position: 'fixed',
                        inset: 0,
                        zIndex: 9000,
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        padding: '16px',
                        background: 'rgba(0,0,0,0.65)',
                        backdropFilter: 'blur(4px)',
                    }}
                    aria-modal="true"
                    role="dialog"
                    aria-label={title}
                >
                    <motion.div
                        initial={{ opacity: 0, scale: 0.93, y: 16 }}
                        animate={{ opacity: 1, scale: 1, y: 0 }}
                        exit={{ opacity: 0, scale: 0.93, y: 16 }}
                        transition={{ duration: 0.22, ease: [0.25, 0.46, 0.45, 0.94] }}
                        style={{
                            width: '100%',
                            maxWidth,
                            background: 'var(--bg-surface)',
                            borderRadius: 'var(--radius-lg)',
                            border: `1px solid color-mix(in srgb, ${accentColor} 25%, var(--border-default))`,
                            boxShadow: `0 24px 64px rgba(0,0,0,0.5), 0 0 0 1px color-mix(in srgb, ${accentColor} 15%, transparent)`,
                            overflow: 'hidden',
                            fontFamily: 'var(--font-sans)',
                        }}
                    >
                        {/* Header */}
                        <div
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'space-between',
                                padding: '18px 20px 14px',
                                borderBottom: '1px solid var(--border-subtle)',
                                background: `color-mix(in srgb, ${accentColor} 6%, var(--bg-elevated))`,
                            }}
                        >
                            <div style={{ display: 'flex', alignItems: 'center', gap: 10 }}>
                                {icon && (
                                    <div
                                        style={{
                                            width: 36,
                                            height: 36,
                                            borderRadius: 'var(--radius-md)',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            background: `color-mix(in srgb, ${accentColor} 15%, var(--bg-card))`,
                                            color: accentColor,
                                            flexShrink: 0,
                                        }}
                                    >
                                        {icon}
                                    </div>
                                )}
                                <div>
                                    <h2
                                        style={{
                                            margin: 0,
                                            fontSize: 15,
                                            fontWeight: 800,
                                            color: 'var(--text-primary)',
                                            letterSpacing: '-0.01em',
                                        }}
                                    >
                                        {title}
                                    </h2>
                                    {subtitle && (
                                        <p
                                            style={{
                                                margin: 0,
                                                fontSize: 12,
                                                color: 'var(--text-muted)',
                                                marginTop: 1,
                                            }}
                                        >
                                            {subtitle}
                                        </p>
                                    )}
                                </div>
                            </div>
                            <button
                                type="button"
                                onClick={onClose}
                                aria-label="Close modal"
                                style={{
                                    width: 30,
                                    height: 30,
                                    borderRadius: 'var(--radius-sm)',
                                    border: '1px solid var(--border-default)',
                                    background: 'transparent',
                                    color: 'var(--text-muted)',
                                    cursor: 'pointer',
                                    display: 'flex',
                                    alignItems: 'center',
                                    justifyContent: 'center',
                                    flexShrink: 0,
                                    transition: 'all 0.15s ease',
                                }}
                            >
                                <X size={14} />
                            </button>
                        </div>

                        {/* Body */}
                        <div style={{ padding: '20px' }}>
                            {children}
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>,
        el,
    );
}
