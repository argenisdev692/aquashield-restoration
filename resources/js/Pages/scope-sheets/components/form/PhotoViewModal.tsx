import * as React from 'react';
import { X, ZoomIn, ZoomOut, RotateCcw } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';

interface PhotoViewModalProps {
    open: boolean;
    src: string;
    caption?: string;
    onClose: () => void;
}

export function PhotoViewModal({ open, src, caption, onClose }: PhotoViewModalProps): React.JSX.Element {
    const [zoom, setZoom] = React.useState(1);

    React.useEffect(() => {
        if (!open) { setZoom(1); return; }

        function onKey(e: KeyboardEvent): void {
            if (e.key === 'Escape') onClose();
            if (e.key === '+' || e.key === '=') setZoom((z) => Math.min(z + 0.25, 3));
            if (e.key === '-') setZoom((z) => Math.max(z - 0.25, 0.5));
        }
        window.addEventListener('keydown', onKey);
        return () => window.removeEventListener('keydown', onKey);
    }, [open, onClose]);

    return (
        <AnimatePresence>
            {open && (
                <motion.div
                    initial={{ opacity: 0 }}
                    animate={{ opacity: 1 }}
                    exit={{ opacity: 0 }}
                    transition={{ duration: 0.2 }}
                    role="dialog"
                    aria-modal="true"
                    aria-label="Photo viewer"
                    onClick={onClose}
                    style={{
                        position: 'fixed',
                        inset: 0,
                        zIndex: 9999,
                        background: 'rgba(0,0,0,0.88)',
                        display: 'flex',
                        flexDirection: 'column',
                        alignItems: 'center',
                        justifyContent: 'center',
                        padding: 24,
                    }}
                >
                    {/* Controls */}
                    <div
                        onClick={(e) => e.stopPropagation()}
                        style={{
                            position: 'absolute',
                            top: 20,
                            right: 20,
                            display: 'flex',
                            gap: 8,
                        }}
                    >
                        <button
                            type="button"
                            aria-label="Zoom in"
                            onClick={() => setZoom((z) => Math.min(z + 0.25, 3))}
                            style={controlBtnStyle}
                        >
                            <ZoomIn size={16} />
                        </button>
                        <button
                            type="button"
                            aria-label="Zoom out"
                            onClick={() => setZoom((z) => Math.max(z - 0.25, 0.5))}
                            style={controlBtnStyle}
                        >
                            <ZoomOut size={16} />
                        </button>
                        <button
                            type="button"
                            aria-label="Reset zoom"
                            onClick={() => setZoom(1)}
                            style={controlBtnStyle}
                        >
                            <RotateCcw size={16} />
                        </button>
                        <button
                            type="button"
                            aria-label="Close photo viewer"
                            onClick={onClose}
                            style={{ ...controlBtnStyle, background: 'color-mix(in srgb, var(--accent-error) 20%, var(--bg-card))' }}
                        >
                            <X size={16} />
                        </button>
                    </div>

                    {/* Image */}
                    <motion.img
                        initial={{ opacity: 0, scale: 0.92 }}
                        animate={{ opacity: 1, scale: 1 }}
                        transition={{ duration: 0.25 }}
                        src={src}
                        alt={caption ?? 'Photo'}
                        onClick={(e) => e.stopPropagation()}
                        style={{
                            maxWidth: '90vw',
                            maxHeight: '80vh',
                            objectFit: 'contain',
                            transform: `scale(${zoom})`,
                            transition: 'transform 0.2s ease',
                            borderRadius: 'var(--radius-lg)',
                            boxShadow: '0 20px 60px rgba(0,0,0,0.6)',
                        }}
                    />

                    {/* Caption */}
                    {caption && (
                        <div
                            onClick={(e) => e.stopPropagation()}
                            style={{
                                marginTop: 20,
                                fontSize: 13,
                                color: 'var(--text-secondary)',
                                fontFamily: 'var(--font-sans)',
                                background: 'var(--bg-card)',
                                border: '1px solid var(--border-default)',
                                borderRadius: 'var(--radius-md)',
                                padding: '6px 16px',
                            }}
                        >
                            {caption}
                        </div>
                    )}
                </motion.div>
            )}
        </AnimatePresence>
    );
}

const controlBtnStyle: React.CSSProperties = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: 36,
    height: 36,
    borderRadius: 'var(--radius-md)',
    border: '1px solid var(--border-default)',
    background: 'var(--bg-card)',
    color: 'var(--text-primary)',
    cursor: 'pointer',
    transition: 'all 0.15s ease',
};
