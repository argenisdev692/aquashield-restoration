import * as React from 'react';
import { Eye, Trash2, RefreshCw, GripVertical } from 'lucide-react';
import { motion } from 'framer-motion';

interface PhotoCardProps {
    src: string;
    caption?: string;
    isDragging?: boolean;
    dragHandleProps?: React.HTMLAttributes<HTMLDivElement>;
    onView: () => void;
    onReplace: () => void;
    onDelete: () => void;
    children?: React.ReactNode;
}

export function PhotoCard({
    src,
    caption,
    isDragging = false,
    dragHandleProps,
    onView,
    onReplace,
    onDelete,
    children,
}: PhotoCardProps): React.JSX.Element {
    const [hovered, setHovered] = React.useState(false);

    return (
        <motion.div
            layout
            initial={{ opacity: 0, scale: 0.95 }}
            animate={{ opacity: 1, scale: 1 }}
            exit={{ opacity: 0, scale: 0.9 }}
            transition={{ duration: 0.2 }}
            onMouseEnter={() => setHovered(true)}
            onMouseLeave={() => setHovered(false)}
            style={{
                position: 'relative',
                borderRadius: 'var(--radius-lg)',
                overflow: 'hidden',
                border: isDragging
                    ? '2px solid var(--accent-primary)'
                    : '1px solid var(--border-default)',
                background: 'var(--bg-card)',
                boxShadow: isDragging
                    ? '0 12px 40px color-mix(in srgb, var(--accent-primary) 25%, transparent)'
                    : hovered
                        ? '0 4px 20px color-mix(in srgb, var(--bg-void) 30%, transparent)'
                        : '0 2px 8px color-mix(in srgb, var(--bg-void) 15%, transparent)',
                transition: 'box-shadow 0.2s ease, border-color 0.2s ease',
                aspectRatio: '4/3',
                cursor: 'grab',
                opacity: isDragging ? 0.7 : 1,
            }}
        >
            {/* Drag handle */}
            {dragHandleProps && (
                <div
                    {...dragHandleProps}
                    title="Drag to reorder"
                    aria-label="Drag to reorder photo"
                    style={{
                        position: 'absolute',
                        top: 8,
                        left: 8,
                        zIndex: 10,
                        width: 28,
                        height: 28,
                        borderRadius: 'var(--radius-sm)',
                        background: 'color-mix(in srgb, var(--bg-void) 55%, transparent)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        cursor: 'grab',
                        color: 'var(--text-primary)',
                    }}
                >
                    <GripVertical size={14} />
                </div>
            )}

            {/* Image */}
            <img
                src={src}
                alt={caption ?? 'Photo'}
                style={{
                    width: '100%',
                    height: '100%',
                    objectFit: 'cover',
                    display: 'block',
                    transition: 'transform 0.25s ease',
                    transform: hovered ? 'scale(1.03)' : 'scale(1)',
                }}
            />

            {/* Hover overlay with actions */}
            <div
                style={{
                    position: 'absolute',
                    inset: 0,
                    background: 'linear-gradient(to top, color-mix(in srgb, var(--bg-void) 75%, transparent) 0%, transparent 50%)',
                    opacity: hovered ? 1 : 0,
                    transition: 'opacity 0.2s ease',
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'flex-end',
                    padding: 10,
                    gap: 8,
                }}
            >
                {/* Caption */}
                {caption && (
                    <span
                        style={{
                            fontSize: 11,
                            fontWeight: 600,
                            color: 'var(--text-primary)',
                            fontFamily: 'var(--font-sans)',
                            textTransform: 'uppercase',
                            letterSpacing: '0.06em',
                        }}
                    >
                        {caption}
                    </span>
                )}

                {/* Action buttons */}
                <div style={{ display: 'flex', gap: 6 }}>
                    <ActionBtn icon={<Eye size={13} />} label="View photo" color="var(--accent-primary)" onClick={onView} />
                    <ActionBtn icon={<RefreshCw size={13} />} label="Replace photo" color="var(--accent-warning)" onClick={onReplace} />
                    <ActionBtn icon={<Trash2 size={13} />} label="Delete photo" color="var(--accent-error)" onClick={onDelete} />
                </div>
            </div>

            {/* Extra content (e.g., type selector) */}
            {children}
        </motion.div>
    );
}

interface ActionBtnProps {
    icon: React.ReactNode;
    label: string;
    color: string;
    onClick: () => void;
}

function ActionBtn({ icon, label, color, onClick }: ActionBtnProps): React.JSX.Element {
    return (
        <button
            type="button"
            aria-label={label}
            title={label}
            onClick={(e) => { e.stopPropagation(); onClick(); }}
            style={{
                display: 'flex',
                alignItems: 'center',
                justifyContent: 'center',
                gap: 4,
                padding: '4px 10px',
                borderRadius: 'var(--radius-sm)',
                border: `1px solid color-mix(in srgb, ${color} 40%, transparent)`,
                background: `color-mix(in srgb, ${color} 18%, var(--bg-void))`,
                color,
                fontSize: 11,
                fontWeight: 700,
                fontFamily: 'var(--font-sans)',
                cursor: 'pointer',
                transition: 'all 0.15s ease',
                backdropFilter: 'blur(4px)',
            }}
        >
            {icon}
            {label.split(' ')[0]}
        </button>
    );
}
