import * as React from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { Loader2 } from 'lucide-react';

// ══════════════════════════════════════════════════════════════════
// DeleteConfirmModal
//
// Usage:
//   <DeleteConfirmModal
//     open={pendingDelete !== null}
//     entityLabel={pendingDelete?.name ?? ''}
//     onConfirm={handleConfirmDelete}
//     onCancel={() => setPendingDelete(null)}
//     isDeleting={mutation.isPending}
//   />
// ══════════════════════════════════════════════════════════════════

interface DeleteConfirmModalProps {
  open: boolean;
  /** The name / identifier of the item being deleted — shown inside a highlighted chip */
  entityLabel: string;
  onConfirm: () => void;
  onCancel: () => void;
  isDeleting?: boolean;
}

const IconTrash = () => (
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
    <polyline points="3 6 5 6 21 6" />
    <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2" />
    <line x1="10" y1="11" x2="10" y2="17" />
    <line x1="14" y1="11" x2="14" y2="17" />
  </svg>
);

export function DeleteConfirmModal({
  open,
  entityLabel,
  onConfirm,
  onCancel,
  isDeleting = false,
}: DeleteConfirmModalProps): React.JSX.Element | null {
  const confirmButtonRef = React.useRef<HTMLButtonElement | null>(null);

  // ── Close on Escape ──────────────────────────────────────────
  React.useEffect(() => {
    if (!open) return;
    function onKey(e: KeyboardEvent): void {
      if (e.key === 'Escape' && !isDeleting) onCancel();
    }
    window.addEventListener('keydown', onKey);
    return () => window.removeEventListener('keydown', onKey);
  }, [open, isDeleting, onCancel]);

  // ── Prevent scroll when open ─────────────────────────────────
  React.useEffect(() => {
    if (open) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
    return () => { document.body.style.overflow = ''; };
  }, [open]);

  React.useEffect(() => {
    if (!open) return;

    const timer = window.setTimeout(() => {
      confirmButtonRef.current?.focus();
    }, 0);

    return () => window.clearTimeout(timer);
  }, [open]);

  return (
    <AnimatePresence>
      {open && (
        <motion.div
          key="delete-confirm-backdrop"
          initial={{ opacity: 0 }}
          animate={{ opacity: 1 }}
          exit={{ opacity: 0 }}
          transition={{ duration: 0.18, ease: 'easeOut' }}
          onClick={!isDeleting ? onCancel : undefined}
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
          aria-labelledby="dcm-title"
          aria-describedby="dcm-description"
        >
          <motion.div
            key="delete-confirm-card"
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
              border: '1px solid color-mix(in srgb, var(--accent-error) 28%, var(--border-default))',
              boxShadow: '0 30px 90px color-mix(in srgb, var(--bg-void) 42%, transparent), 0 0 0 1px color-mix(in srgb, var(--accent-error) 12%, transparent)',
            }}
          >
            <div
              style={{
                height: 4,
                background: 'linear-gradient(90deg, var(--accent-error) 0%, color-mix(in srgb, var(--accent-warning) 70%, var(--accent-error)) 100%)',
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
                    background: 'color-mix(in srgb, var(--accent-error) 12%, transparent)',
                    color: 'var(--accent-error)',
                    border: '1px solid color-mix(in srgb, var(--accent-error) 24%, transparent)',
                    boxShadow: '0 0 0 6px color-mix(in srgb, var(--accent-error) 6%, transparent)',
                  }}
                >
                  <IconTrash />
                </div>

                <div style={{ minWidth: 0, flex: 1 }}>
                  <p
                    style={{
                      margin: 0,
                      fontSize: 11,
                      fontWeight: 700,
                      letterSpacing: '0.18em',
                      textTransform: 'uppercase',
                      color: 'var(--accent-error)',
                    }}
                  >
                    Destructive action
                  </p>
                  <h2
                    id="dcm-title"
                    style={{
                      margin: '0.35rem 0 0',
                      fontSize: '1.125rem',
                      fontWeight: 800,
                      color: 'var(--text-primary)',
                      letterSpacing: '-0.02em',
                    }}
                  >
                    Delete confirmation
                  </h2>
                  <p
                    id="dcm-description"
                    style={{
                      margin: '0.65rem 0 0',
                      fontSize: '0.95rem',
                      color: 'var(--text-secondary)',
                      lineHeight: 1.7,
                    }}
                  >
                    Are you sure you want to delete{' '}
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
                        color: 'var(--accent-error)',
                        background: 'color-mix(in srgb, var(--accent-error) 10%, transparent)',
                        border: '1px solid color-mix(in srgb, var(--accent-error) 20%, transparent)',
                        wordBreak: 'break-all',
                        verticalAlign: 'middle',
                      }}
                    >
                      {entityLabel}
                    </span>
                    ? This action cannot be undone.
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
                  disabled={isDeleting}
                  style={{
                    minWidth: 96,
                    padding: '0.75rem 1rem',
                    borderRadius: 'var(--radius-md)',
                    fontSize: '0.875rem',
                    fontWeight: 700,
                    cursor: isDeleting ? 'not-allowed' : 'pointer',
                    border: '1px solid var(--border-default)',
                    background: 'transparent',
                    color: 'var(--text-secondary)',
                    fontFamily: 'var(--font-sans)',
                    transition: 'all 0.15s ease',
                    opacity: isDeleting ? 0.5 : 1,
                  }}
                  onMouseEnter={(e) => {
                    if (!isDeleting) {
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
                  disabled={isDeleting}
                  style={{
                    minWidth: 116,
                    padding: '0.75rem 1rem',
                    borderRadius: 'var(--radius-md)',
                    fontSize: '0.875rem',
                    fontWeight: 800,
                    cursor: isDeleting ? 'not-allowed' : 'pointer',
                    border: '1px solid color-mix(in srgb, var(--accent-error) 50%, transparent)',
                    background: 'color-mix(in srgb, var(--accent-error) 16%, transparent)',
                    color: 'var(--accent-error)',
                    fontFamily: 'var(--font-sans)',
                    transition: 'all 0.15s ease',
                    display: 'inline-flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    gap: '0.5rem',
                    opacity: isDeleting ? 0.8 : 1,
                    boxShadow: '0 10px 24px color-mix(in srgb, var(--accent-error) 10%, transparent)',
                  }}
                  onMouseEnter={(e) => {
                    if (!isDeleting) {
                      (e.currentTarget as HTMLButtonElement).style.background = 'color-mix(in srgb, var(--accent-error) 24%, transparent)';
                    }
                  }}
                  onMouseLeave={(e) => {
                    (e.currentTarget as HTMLButtonElement).style.background = 'color-mix(in srgb, var(--accent-error) 16%, transparent)';
                  }}
                >
                  {isDeleting ? (
                    <>
                      <Loader2 size={14} style={{ animation: 'spin 0.8s linear infinite' }} />
                      Deleting…
                    </>
                  ) : (
                    <>
                      <IconTrash />
                      Delete
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
