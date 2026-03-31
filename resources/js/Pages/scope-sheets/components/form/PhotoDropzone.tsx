import * as React from 'react';
import { useDropzone } from 'react-dropzone';
import { Upload, ImagePlus } from 'lucide-react';
import { motion, AnimatePresence } from 'framer-motion';

interface PhotoDropzoneProps {
    onFiles: (files: File[]) => void;
    multiple?: boolean;
    label?: string;
    compact?: boolean;
    inputRef?: React.RefObject<HTMLInputElement | null>;
}

export function PhotoDropzone({
    onFiles,
    multiple = true,
    label = 'Drop photos here or click to upload',
    compact = false,
    inputRef,
}: PhotoDropzoneProps): React.JSX.Element {
    const { getRootProps, getInputProps, isDragActive } = useDropzone({
        accept: { 'image/*': ['.jpg', '.jpeg', '.png', '.webp', '.heic'] },
        multiple,
        onDrop: onFiles,
    });

    const rootProps = getRootProps();
    const inputProps = getInputProps();

    return (
        <div
            {...rootProps}
            role="button"
            tabIndex={0}
            aria-label={label}
            style={{
                display: 'flex',
                flexDirection: 'column',
                alignItems: 'center',
                justifyContent: 'center',
                gap: compact ? 6 : 12,
                padding: compact ? '16px 12px' : '32px 24px',
                borderRadius: 'var(--radius-lg)',
                border: `2px dashed ${isDragActive ? 'var(--accent-primary)' : 'var(--border-default)'}`,
                background: isDragActive
                    ? 'color-mix(in srgb, var(--accent-primary) 8%, var(--bg-card))'
                    : 'var(--bg-card)',
                cursor: 'pointer',
                transition: 'all 0.2s ease',
                outline: 'none',
                minHeight: compact ? 80 : 140,
                userSelect: 'none',
            }}
        >
            <input {...inputProps} ref={inputRef ?? undefined} />

            <AnimatePresence mode="wait">
                {isDragActive ? (
                    <motion.div
                        key="active"
                        initial={{ opacity: 0, y: 6 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -6 }}
                        transition={{ duration: 0.15 }}
                        style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8 }}
                    >
                        <div
                            style={{
                                width: compact ? 36 : 48,
                                height: compact ? 36 : 48,
                                borderRadius: '50%',
                                background: 'color-mix(in srgb, var(--accent-primary) 20%, var(--bg-elevated))',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                color: 'var(--accent-primary)',
                            }}
                        >
                            <Upload size={compact ? 18 : 22} />
                        </div>
                        <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--accent-primary)', fontFamily: 'var(--font-sans)' }}>
                            Drop to add
                        </span>
                    </motion.div>
                ) : (
                    <motion.div
                        key="idle"
                        initial={{ opacity: 0, y: 6 }}
                        animate={{ opacity: 1, y: 0 }}
                        exit={{ opacity: 0, y: -6 }}
                        transition={{ duration: 0.15 }}
                        style={{ display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8 }}
                    >
                        <div
                            style={{
                                width: compact ? 36 : 48,
                                height: compact ? 36 : 48,
                                borderRadius: '50%',
                                background: 'color-mix(in srgb, var(--accent-primary) 12%, var(--bg-elevated))',
                                display: 'flex',
                                alignItems: 'center',
                                justifyContent: 'center',
                                color: 'var(--accent-primary)',
                                transition: 'all 0.2s ease',
                            }}
                        >
                            <ImagePlus size={compact ? 18 : 22} />
                        </div>
                        {!compact && (
                            <div style={{ textAlign: 'center' }}>
                                <p style={{ margin: 0, fontSize: 13, fontWeight: 600, color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}>
                                    {label}
                                </p>
                                <p style={{ margin: '4px 0 0', fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                    JPG, PNG, WEBP, HEIC — up to 10MB each
                                </p>
                            </div>
                        )}
                        {compact && (
                            <span style={{ fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                Add photo
                            </span>
                        )}
                    </motion.div>
                )}
            </AnimatePresence>
        </div>
    );
}
