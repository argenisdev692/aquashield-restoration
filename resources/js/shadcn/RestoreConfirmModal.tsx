import * as React from "react";
import { AnimatePresence, motion } from 'framer-motion';
import { Loader2 } from 'lucide-react';

interface RestoreConfirmModalProps {
    isOpen: boolean;
    entityLabel: string;
    entityName?: string;
    onConfirm: () => void;
    onCancel: () => void;
    isPending?: boolean;
}

const IconRestore = () => (
    <svg
        width={28}
        height={28}
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        strokeWidth={1.8}
        strokeLinecap="round"
        strokeLinejoin="round"
    >
        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8" />
        <path d="M21 3v5h-5" />
        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16" />
        <path d="M3 21v-5h5" />
    </svg>
);

export function RestoreConfirmModal({
    isOpen,
    entityLabel,
    entityName,
    onConfirm,
    onCancel,
    isPending = false,
}: RestoreConfirmModalProps): React.JSX.Element | null {
    const confirmButtonRef = React.useRef<HTMLButtonElement | null>(null);

    React.useEffect(() => {
        if (!isOpen) return;
        function onKey(e: KeyboardEvent): void {
            if (e.key === "Escape" && !isPending) onCancel();
        }
        window.addEventListener("keydown", onKey);
        return () => window.removeEventListener("keydown", onKey);
    }, [isOpen, isPending, onCancel]);

    React.useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = "hidden";
        } else {
            document.body.style.overflow = "";
        }
        return () => {
            document.body.style.overflow = "";
        }
    }, [isOpen]);

    React.useEffect(() => {
        if (!isOpen) return;

        const timer = window.setTimeout(() => {
            confirmButtonRef.current?.focus();
        }, 0);

        return () => window.clearTimeout(timer);
    }, [isOpen]);

    return (
        <AnimatePresence>
            {isOpen && (
                <motion.div
                    key="restore-confirm-backdrop"
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.18, ease: 'easeOut' }}
                    onClick={!isPending ? onCancel : undefined}
                    style={{
                        position: 'fixed',
                        inset: 0,
                        zIndex: 9999,
                        display: 'grid',
                        placeItems: 'center',
                        padding: '1rem',
                        background: 'color-mix(in srgb, var(--bg-void) 66%, transparent)',
                        backdropFilter: 'blur(10px)',
                        WebkitBackdropFilter: 'blur(10px)',
                    }}
                    aria-modal="true"
                    role="dialog"
                    aria-labelledby="rcm-title"
                    aria-describedby="rcm-description"
                >
                    <motion.div
                        key="restore-confirm-card"
                        initial={{ opacity: 0, y: 18, scale: 0.96 }}
                        animate={{ opacity: 1, y: 0, scale: 1 }}
                        exit={{ opacity: 0, y: 12, scale: 0.98 }}
                        transition={{ type: 'spring', stiffness: 280, damping: 24 }}
                        onClick={(e) => e.stopPropagation()}
                        style={{
                            width: '100%',
                            maxWidth: 520,
                            overflow: 'hidden',
                            borderRadius: 'var(--radius-xl)',
                            fontFamily: 'var(--font-sans)',
                            background: 'linear-gradient(180deg, color-mix(in srgb, var(--bg-card) 96%, transparent) 0%, color-mix(in srgb, var(--bg-surface) 96%, transparent) 100%)',
                            border: '1px solid color-mix(in srgb, var(--success-primary) 28%, var(--border-default))',
                            boxShadow: '0 30px 90px color-mix(in srgb, var(--bg-void) 42%, transparent), 0 0 0 1px color-mix(in srgb, var(--success-primary) 12%, transparent)',
                        }}
                    >
                        <div
                            style={{
                                height: 4,
                                background: 'linear-gradient(90deg, var(--success-primary) 0%, color-mix(in srgb, var(--accent-primary) 65%, var(--success-primary)) 100%)',
                            }}
                        />

                        <div style={{ padding: '1.5rem 1.5rem 1.25rem' }}>
                            <div style={{ display: 'flex', gap: 14, alignItems: 'flex-start' }}>
                                <div
                                    style={{
                                        width: 56,
                                        height: 56,
                                        borderRadius: 'var(--radius-lg)',
                                        display: 'flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        flexShrink: 0,
                                        background: 'color-mix(in srgb, var(--success-primary) 12%, transparent)',
                                        color: 'var(--success-primary)',
                                        border: '1px solid color-mix(in srgb, var(--success-primary) 24%, transparent)',
                                        boxShadow: '0 0 0 6px color-mix(in srgb, var(--success-primary) 6%, transparent)',
                                    }}
                                >
                                    <IconRestore />
                                </div>

                                <div style={{ minWidth: 0, flex: 1 }}>
                                    <p
                                        style={{
                                            margin: 0,
                                            fontSize: 11,
                                            fontWeight: 700,
                                            letterSpacing: '0.18em',
                                            textTransform: 'uppercase',
                                            color: 'var(--success-primary)',
                                        }}
                                    >
                                        Recovery action
                                    </p>
                                    <h2
                                        id="rcm-title"
                                        style={{
                                            margin: '0.35rem 0 0',
                                            fontSize: '1.125rem',
                                            fontWeight: 800,
                                            color: 'var(--text-primary)',
                                            letterSpacing: '-0.02em',
                                        }}
                                    >
                                        Restore confirmation
                                    </h2>
                                    <p
                                        id="rcm-description"
                                        style={{
                                            margin: '0.65rem 0 0',
                                            fontSize: '0.95rem',
                                            color: 'var(--text-secondary)',
                                            lineHeight: 1.7,
                                        }}
                                    >
                                        Are you sure you want to restore{' '}
                                        <span
                                            style={{
                                                display: 'inline-flex',
                                                alignItems: 'center',
                                                maxWidth: '100%',
                                                margin: '0 4px',
                                                padding: '2px 10px',
                                                borderRadius: 999,
                                                fontSize: '0.8125rem',
                                                fontWeight: 700,
                                                color: 'var(--success-primary)',
                                                background: 'color-mix(in srgb, var(--success-primary) 10%, transparent)',
                                                border: '1px solid color-mix(in srgb, var(--success-primary) 20%, transparent)',
                                                wordBreak: 'break-all',
                                                verticalAlign: 'middle',
                                            }}
                                        >
                                            {entityName || entityLabel}
                                        </span>
                                        ? This will make it active again.
                                    </p>
                                </div>
                            </div>

                            <div
                                style={{
                                    marginTop: '1.25rem',
                                    display: 'flex',
                                    gap: '0.75rem',
                                    justifyContent: 'flex-end',
                                    flexWrap: 'wrap',
                                }}
                            >
                                <button
                                    type="button"
                                    onClick={onCancel}
                                    disabled={isPending}
                                    style={{
                                        minWidth: 96,
                                        padding: '0.75rem 1rem',
                                        borderRadius: 'var(--radius-md)',
                                        fontSize: '0.875rem',
                                        fontWeight: 700,
                                        cursor: isPending ? 'not-allowed' : 'pointer',
                                        border: '1px solid var(--border-default)',
                                        background: 'transparent',
                                        color: 'var(--text-secondary)',
                                        fontFamily: 'var(--font-sans)',
                                        transition: 'all 0.15s ease',
                                        opacity: isPending ? 0.5 : 1,
                                    }}
                                    onMouseEnter={(e) => {
                                        if (!isPending) {
                                            (e.currentTarget as HTMLButtonElement).style.background = 'var(--bg-surface)';
                                            (e.currentTarget as HTMLButtonElement).style.color = 'var(--text-primary)';
                                        }
                                    }}
                                    onMouseLeave={(e) => {
                                        (e.currentTarget as HTMLButtonElement).style.background = 'transparent';
                                        (e.currentTarget as HTMLButtonElement).style.color = 'var(--text-secondary)';
                                    }}
                                >
                                    Cancel
                                </button>

                                <button
                                    ref={confirmButtonRef}
                                    type="button"
                                    onClick={onConfirm}
                                    disabled={isPending}
                                    style={{
                                        minWidth: 116,
                                        padding: '0.75rem 1rem',
                                        borderRadius: 'var(--radius-md)',
                                        fontSize: '0.875rem',
                                        fontWeight: 800,
                                        cursor: isPending ? 'not-allowed' : 'pointer',
                                        border: '1px solid color-mix(in srgb, var(--success-primary) 50%, transparent)',
                                        background: 'color-mix(in srgb, var(--success-primary) 16%, transparent)',
                                        color: 'var(--success-primary)',
                                        fontFamily: 'var(--font-sans)',
                                        transition: 'all 0.15s ease',
                                        display: 'inline-flex',
                                        alignItems: 'center',
                                        justifyContent: 'center',
                                        gap: '0.5rem',
                                        opacity: isPending ? 0.8 : 1,
                                        boxShadow: '0 10px 24px color-mix(in srgb, var(--success-primary) 10%, transparent)',
                                    }}
                                    onMouseEnter={(e) => {
                                        if (!isPending) {
                                            (e.currentTarget as HTMLButtonElement).style.background = 'color-mix(in srgb, var(--success-primary) 24%, transparent)';
                                        }
                                    }}
                                    onMouseLeave={(e) => {
                                        (e.currentTarget as HTMLButtonElement).style.background = 'color-mix(in srgb, var(--success-primary) 16%, transparent)';
                                    }}
                                >
                                    {isPending ? (
                                        <>
                                            <Loader2 size={14} style={{ animation: 'spin 0.8s linear infinite' }} />
                                            Restoring…
                                        </>
                                    ) : (
                                        <>
                                            <IconRestore />
                                            Restore
                                        </>
                                    )}
                                </button>
                            </div>
                        </div>
                    </motion.div>
                </motion.div>
            )}
        </AnimatePresence>
    );
}
