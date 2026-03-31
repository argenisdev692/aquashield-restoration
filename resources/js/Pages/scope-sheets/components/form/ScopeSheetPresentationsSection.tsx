import * as React from 'react';
import { AnimatePresence } from 'framer-motion';
import { Images, GripVertical, ChevronUp, ChevronDown } from 'lucide-react';
import { PhotoCard } from './PhotoCard';
import { PhotoDropzone } from './PhotoDropzone';
import { PhotoViewModal } from './PhotoViewModal';
import type { ScopeSheetPresentation } from '@/modules/scope-sheets/types';
import { PRESENTATION_PHOTO_TYPE_LABELS } from '@/modules/scope-sheets/types';

interface Props {
    presentations: ScopeSheetPresentation[];
    onChange: (presentations: ScopeSheetPresentation[]) => void;
}

export function ScopeSheetPresentationsSection({ presentations, onChange }: Props): React.JSX.Element {
    const [viewingIdx, setViewingIdx] = React.useState<number | null>(null);
    const replaceInputRefs = React.useRef<(HTMLInputElement | null)[]>([]);
    const [dragIdx, setDragIdx] = React.useState<number | null>(null);
    const [dragOverIdx, setDragOverIdx] = React.useState<number | null>(null);

    function handleDropFiles(files: File[]): void {
        const newItems: ScopeSheetPresentation[] = files.map((file, i) => ({
            photo_type: 'other',
            photo_path: '',
            photo_order: presentations.length + i,
            _preview: URL.createObjectURL(file),
            _file: file,
        }));
        onChange([...presentations, ...newItems].map((p, i) => ({ ...p, photo_order: i })));
    }

    function handleReplace(idx: number, file: File): void {
        const updated = [...presentations];
        if (updated[idx]._preview && updated[idx]._file) {
            URL.revokeObjectURL(updated[idx]._preview!);
        }
        updated[idx] = {
            ...updated[idx],
            photo_path: '',
            _preview: URL.createObjectURL(file),
            _file: file,
        };
        onChange(updated);
    }

    function handleDelete(idx: number): void {
        const updated = [...presentations];
        if (updated[idx]._preview && updated[idx]._file) {
            URL.revokeObjectURL(updated[idx]._preview!);
        }
        updated.splice(idx, 1);
        onChange(updated.map((p, i) => ({ ...p, photo_order: i })));
    }

    function handleTypeChange(idx: number, type: string): void {
        const updated = [...presentations];
        updated[idx] = { ...updated[idx], photo_type: type };
        onChange(updated);
    }

    function moveItem(from: number, to: number): void {
        if (to < 0 || to >= presentations.length) return;
        const updated = [...presentations];
        const [item] = updated.splice(from, 1);
        updated.splice(to, 0, item);
        onChange(updated.map((p, i) => ({ ...p, photo_order: i })));
    }

    /* ── Drag & Drop handlers ── */
    function handleDragStart(idx: number): void { setDragIdx(idx); }
    function handleDragEnter(idx: number): void { setDragOverIdx(idx); }
    function handleDragEnd(): void {
        if (dragIdx !== null && dragOverIdx !== null && dragIdx !== dragOverIdx) {
            moveItem(dragIdx, dragOverIdx);
        }
        setDragIdx(null);
        setDragOverIdx(null);
    }

    const viewingItem = viewingIdx !== null ? presentations[viewingIdx] : null;

    return (
        <section aria-labelledby="presentations-heading">
            {/* Section header */}
            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 10,
                    marginBottom: 16,
                    paddingBottom: 12,
                    borderBottom: '1px solid var(--border-subtle)',
                }}
            >
                <div
                    style={{
                        width: 36,
                        height: 36,
                        borderRadius: 'var(--radius-md)',
                        background: 'color-mix(in srgb, var(--accent-primary) 15%, var(--bg-card))',
                        border: '1px solid color-mix(in srgb, var(--accent-primary) 30%, transparent)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        color: 'var(--accent-primary)',
                        flexShrink: 0,
                    }}
                >
                    <Images size={18} />
                </div>
                <div>
                    <h3
                        id="presentations-heading"
                        style={{ margin: 0, fontSize: 15, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}
                    >
                        Presentation Photos
                    </h3>
                    <p style={{ margin: 0, fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                        Add up to 4+ property overview photos. Drag to reorder. First image used as cover.
                    </p>
                </div>
                <span
                    style={{
                        marginLeft: 'auto',
                        fontSize: 11,
                        fontWeight: 700,
                        color: 'var(--accent-primary)',
                        background: 'color-mix(in srgb, var(--accent-primary) 12%, var(--bg-card))',
                        border: '1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)',
                        borderRadius: 999,
                        padding: '2px 10px',
                        fontFamily: 'var(--font-sans)',
                    }}
                >
                    {presentations.length} photo{presentations.length !== 1 ? 's' : ''}
                </span>
            </div>

            {/* Photo grid */}
            <div
                style={{
                    display: 'grid',
                    gridTemplateColumns: 'repeat(auto-fill, minmax(200px, 1fr))',
                    gap: 14,
                    marginBottom: 14,
                }}
            >
                <AnimatePresence>
                    {presentations.map((p, idx) => {
                        const previewSrc = p._preview ?? p.photo_path;
                        const isDragging = dragIdx === idx;
                        const isDragOver = dragOverIdx === idx && dragIdx !== null && dragIdx !== idx;

                        return (
                            <div
                                key={p.uuid ?? `pres-${idx}`}
                                draggable
                                onDragStart={() => handleDragStart(idx)}
                                onDragEnter={() => handleDragEnter(idx)}
                                onDragEnd={handleDragEnd}
                                onDragOver={(e) => e.preventDefault()}
                                style={{
                                    outline: isDragOver ? '2px solid var(--accent-primary)' : 'none',
                                    borderRadius: 'var(--radius-lg)',
                                    transition: 'outline 0.15s ease',
                                }}
                            >
                                <PhotoCard
                                    src={previewSrc}
                                    caption={PRESENTATION_PHOTO_TYPE_LABELS[p.photo_type] ?? p.photo_type}
                                    isDragging={isDragging}
                                    dragHandleProps={{
                                        style: {
                                            position: 'absolute',
                                            top: 8,
                                            left: 8,
                                            zIndex: 10,
                                            width: 28,
                                            height: 28,
                                            borderRadius: 'var(--radius-sm)',
                                            background: 'rgba(0,0,0,0.55)',
                                            display: 'flex',
                                            alignItems: 'center',
                                            justifyContent: 'center',
                                            cursor: 'grab',
                                            color: 'rgba(255,255,255,0.8)',
                                        },
                                    }}
                                    onView={() => setViewingIdx(idx)}
                                    onReplace={() => replaceInputRefs.current[idx]?.click()}
                                    onDelete={() => handleDelete(idx)}
                                >
                                    {/* Type selector + order controls */}
                                    <div
                                        style={{
                                            position: 'absolute',
                                            bottom: 0,
                                            left: 0,
                                            right: 0,
                                            padding: '6px 8px',
                                            background: 'rgba(0,0,0,0.65)',
                                            backdropFilter: 'blur(4px)',
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: 6,
                                        }}
                                    >
                                        <select
                                            value={p.photo_type}
                                            onChange={(e) => handleTypeChange(idx, e.target.value)}
                                            aria-label="Photo type"
                                            onClick={(e) => e.stopPropagation()}
                                            style={{
                                                flex: 1,
                                                fontSize: 11,
                                                background: 'rgba(12,35,64,0.9)',
                                                border: '1px solid var(--border-default)',
                                                borderRadius: 'var(--radius-sm)',
                                                color: 'var(--text-primary)',
                                                padding: '3px 6px',
                                                fontFamily: 'var(--font-sans)',
                                                cursor: 'pointer',
                                                colorScheme: 'dark',
                                            }}
                                        >
                                            {Object.entries(PRESENTATION_PHOTO_TYPE_LABELS).map(([val, label]) => (
                                                <option key={val} value={val}>{label}</option>
                                            ))}
                                        </select>
                                        <button
                                            type="button"
                                            aria-label="Move up"
                                            onClick={(e) => { e.stopPropagation(); moveItem(idx, idx - 1); }}
                                            disabled={idx === 0}
                                            style={reorderBtnStyle}
                                        >
                                            <ChevronUp size={12} />
                                        </button>
                                        <button
                                            type="button"
                                            aria-label="Move down"
                                            onClick={(e) => { e.stopPropagation(); moveItem(idx, idx + 1); }}
                                            disabled={idx === presentations.length - 1}
                                            style={reorderBtnStyle}
                                        >
                                            <ChevronDown size={12} />
                                        </button>
                                    </div>

                                    {/* Hidden replace input */}
                                    <input
                                        type="file"
                                        accept="image/*"
                                        className="sr-only"
                                        aria-hidden="true"
                                        ref={(el) => { replaceInputRefs.current[idx] = el; }}
                                        onChange={(e) => {
                                            const file = e.target.files?.[0];
                                            if (file) handleReplace(idx, file);
                                            e.target.value = '';
                                        }}
                                    />

                                    {/* Cover badge */}
                                    {idx === 0 && (
                                        <div
                                            style={{
                                                position: 'absolute',
                                                top: 8,
                                                right: 8,
                                                fontSize: 9,
                                                fontWeight: 800,
                                                color: 'var(--accent-primary)',
                                                background: 'rgba(0,0,0,0.65)',
                                                border: '1px solid var(--accent-primary)',
                                                borderRadius: 999,
                                                padding: '2px 8px',
                                                fontFamily: 'var(--font-sans)',
                                                letterSpacing: '0.06em',
                                                textTransform: 'uppercase',
                                                backdropFilter: 'blur(4px)',
                                            }}
                                        >
                                            Cover
                                        </div>
                                    )}
                                </PhotoCard>
                            </div>
                        );
                    })}
                </AnimatePresence>

                {/* Drag hint */}
                {presentations.length > 0 && (
                    <div
                        style={{
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            gap: 6,
                            fontSize: 11,
                            color: 'var(--text-disabled)',
                            fontFamily: 'var(--font-sans)',
                            padding: '0 8px',
                        }}
                    >
                        <GripVertical size={14} />
                        Drag cards to reorder
                    </div>
                )}
            </div>

            {/* Dropzone for adding more */}
            <PhotoDropzone
                onFiles={handleDropFiles}
                multiple
                label="Drop presentation photos here or click to add"
                compact={presentations.length > 0}
            />

            {/* Photo view modal */}
            <PhotoViewModal
                open={viewingIdx !== null}
                src={viewingItem ? (viewingItem._preview ?? viewingItem.photo_path) : ''}
                caption={viewingItem ? (PRESENTATION_PHOTO_TYPE_LABELS[viewingItem.photo_type] ?? viewingItem.photo_type) : undefined}
                onClose={() => setViewingIdx(null)}
            />
        </section>
    );
}

const reorderBtnStyle: React.CSSProperties = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: 22,
    height: 22,
    borderRadius: 'var(--radius-sm)',
    border: '1px solid var(--border-default)',
    background: 'rgba(0,0,0,0.5)',
    color: 'var(--text-secondary)',
    cursor: 'pointer',
    flexShrink: 0,
};
