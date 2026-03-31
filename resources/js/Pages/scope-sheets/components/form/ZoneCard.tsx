import * as React from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { MapPin, Trash2, ChevronDown, ChevronUp, GripVertical, StickyNote } from 'lucide-react';
import { PhotoCard } from './PhotoCard';
import { PhotoDropzone } from './PhotoDropzone';
import { PhotoViewModal } from './PhotoViewModal';
import type { ScopeSheetZone, ScopeSheetZonePhoto } from '@/modules/scope-sheets/types';

interface ZoneCardProps {
    zone: ScopeSheetZone;
    index: number;
    total: number;
    isDragging?: boolean;
    isDragOver?: boolean;
    onDragStart: () => void;
    onDragEnter: () => void;
    onDragEnd: () => void;
    onUpdate: (zone: ScopeSheetZone) => void;
    onDelete: () => void;
    onMoveUp: () => void;
    onMoveDown: () => void;
}

export function ZoneCard({
    zone,
    index,
    total,
    isDragging = false,
    isDragOver = false,
    onDragStart,
    onDragEnter,
    onDragEnd,
    onUpdate,
    onDelete,
    onMoveUp,
    onMoveDown,
}: ZoneCardProps): React.JSX.Element {
    const [expanded, setExpanded] = React.useState(true);
    const [viewingPhotoIdx, setViewingPhotoIdx] = React.useState<number | null>(null);
    const replaceInputRefs = React.useRef<(HTMLInputElement | null)[]>([]);

    function handleAddPhotos(files: File[]): void {
        const newPhotos: ScopeSheetZonePhoto[] = files.map((file, i) => ({
            photo_path: '',
            photo_order: zone.photos.length + i,
            _preview: URL.createObjectURL(file),
            _file: file,
        }));
        onUpdate({ ...zone, photos: [...zone.photos, ...newPhotos].map((p, i) => ({ ...p, photo_order: i })) });
    }

    function handleReplacePhoto(idx: number, file: File): void {
        const updated = [...zone.photos];
        if (updated[idx]._preview && updated[idx]._file) URL.revokeObjectURL(updated[idx]._preview!);
        updated[idx] = { ...updated[idx], photo_path: '', _preview: URL.createObjectURL(file), _file: file };
        onUpdate({ ...zone, photos: updated });
    }

    function handleDeletePhoto(idx: number): void {
        const updated = [...zone.photos];
        if (updated[idx]._preview && updated[idx]._file) URL.revokeObjectURL(updated[idx]._preview!);
        updated.splice(idx, 1);
        onUpdate({ ...zone, photos: updated.map((p, i) => ({ ...p, photo_order: i })) });
    }

    function handleMovePhoto(from: number, to: number): void {
        if (to < 0 || to >= zone.photos.length) return;
        const updated = [...zone.photos];
        const [item] = updated.splice(from, 1);
        updated.splice(to, 0, item);
        onUpdate({ ...zone, photos: updated.map((p, i) => ({ ...p, photo_order: i })) });
    }

    const viewingPhoto = viewingPhotoIdx !== null ? zone.photos[viewingPhotoIdx] : null;

    return (
        <motion.div
            layout
            initial={{ opacity: 0, y: 12 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0, y: -8 }}
            transition={{ duration: 0.25 }}
            draggable
            onDragStart={onDragStart}
            onDragEnter={onDragEnter}
            onDragEnd={onDragEnd}
            onDragOver={(e) => e.preventDefault()}
            style={{
                borderRadius: 'var(--radius-lg)',
                border: isDragging
                    ? '2px solid var(--accent-primary)'
                    : isDragOver
                        ? '2px dashed var(--accent-primary)'
                        : '1px solid var(--border-default)',
                background: 'var(--bg-card)',
                overflow: 'hidden',
                opacity: isDragging ? 0.6 : 1,
                boxShadow: isDragOver ? '0 0 0 4px color-mix(in srgb, var(--accent-primary) 15%, transparent)' : 'none',
                transition: 'border-color 0.15s ease, box-shadow 0.15s ease',
            }}
        >
            {/* Zone Header */}
            <div
                style={{
                    display: 'flex',
                    alignItems: 'center',
                    gap: 10,
                    padding: '12px 16px',
                    borderBottom: expanded ? '1px solid var(--border-subtle)' : 'none',
                    background: 'var(--bg-elevated)',
                    cursor: 'pointer',
                }}
                onClick={() => setExpanded((v) => !v)}
            >
                {/* Drag handle */}
                <div
                    title="Drag to reorder zone"
                    aria-label="Drag zone"
                    onClick={(e) => e.stopPropagation()}
                    style={{
                        cursor: 'grab',
                        color: 'var(--text-disabled)',
                        display: 'flex',
                        alignItems: 'center',
                        padding: '2px 4px',
                        borderRadius: 'var(--radius-sm)',
                        transition: 'color 0.15s ease',
                    }}
                >
                    <GripVertical size={16} />
                </div>

                {/* Zone number badge */}
                <div
                    style={{
                        width: 28,
                        height: 28,
                        borderRadius: '50%',
                        background: 'color-mix(in srgb, var(--accent-primary) 18%, var(--bg-card))',
                        border: '1px solid color-mix(in srgb, var(--accent-primary) 35%, transparent)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        fontSize: 12,
                        fontWeight: 800,
                        color: 'var(--accent-primary)',
                        fontFamily: 'var(--font-sans)',
                        flexShrink: 0,
                    }}
                >
                    {index + 1}
                </div>

                <MapPin size={15} style={{ color: 'var(--accent-primary)', flexShrink: 0 }} />

                <div style={{ flex: 1, minWidth: 0 }}>
                    <div style={{ fontSize: 14, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>
                        {zone.zone_name ?? `Zone ${index + 1}`}
                    </div>
                    <div style={{ fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)', marginTop: 1 }}>
                        {zone.photos.length} photo{zone.photos.length !== 1 ? 's' : ''}
                        {zone.zone_notes.trim() && ' · Has notes'}
                    </div>
                </div>

                {/* Move controls */}
                <div style={{ display: 'flex', gap: 4 }} onClick={(e) => e.stopPropagation()}>
                    <button
                        type="button"
                        aria-label="Move zone up"
                        disabled={index === 0}
                        onClick={onMoveUp}
                        style={smallBtnStyle}
                    >
                        <ChevronUp size={13} />
                    </button>
                    <button
                        type="button"
                        aria-label="Move zone down"
                        disabled={index === total - 1}
                        onClick={onMoveDown}
                        style={smallBtnStyle}
                    >
                        <ChevronDown size={13} />
                    </button>
                </div>

                {/* Delete */}
                <button
                    type="button"
                    aria-label={`Delete zone ${zone.zone_name ?? index + 1}`}
                    onClick={(e) => { e.stopPropagation(); onDelete(); }}
                    style={{
                        ...smallBtnStyle,
                        color: 'var(--accent-error)',
                        borderColor: 'color-mix(in srgb, var(--accent-error) 30%, transparent)',
                        background: 'color-mix(in srgb, var(--accent-error) 10%, var(--bg-card))',
                    }}
                >
                    <Trash2 size={13} />
                </button>

                {/* Expand toggle */}
                <div style={{ color: 'var(--text-muted)', transition: 'transform 0.2s ease', transform: expanded ? 'rotate(0deg)' : 'rotate(-90deg)' }}>
                    <ChevronDown size={16} />
                </div>
            </div>

            {/* Zone body */}
            <AnimatePresence initial={false}>
                {expanded && (
                    <motion.div
                        initial={{ height: 0, opacity: 0 }}
                        animate={{ height: 'auto', opacity: 1 }}
                        exit={{ height: 0, opacity: 0 }}
                        transition={{ duration: 0.25, ease: 'easeInOut' }}
                        style={{ overflow: 'hidden' }}
                    >
                        <div style={{ padding: '16px', display: 'flex', flexDirection: 'column', gap: 16 }}>
                            {/* Notes */}
                            <div>
                                <label
                                    htmlFor={`zone-notes-${index}`}
                                    style={{
                                        display: 'flex',
                                        alignItems: 'center',
                                        gap: 6,
                                        fontSize: 12,
                                        fontWeight: 700,
                                        color: 'var(--text-secondary)',
                                        fontFamily: 'var(--font-sans)',
                                        textTransform: 'uppercase',
                                        letterSpacing: '0.06em',
                                        marginBottom: 8,
                                    }}
                                >
                                    <StickyNote size={13} /> Inspector Notes
                                </label>
                                <textarea
                                    id={`zone-notes-${index}`}
                                    value={zone.zone_notes}
                                    onChange={(e) => onUpdate({ ...zone, zone_notes: e.target.value })}
                                    placeholder="Add notes about damage, moisture levels, affected areas..."
                                    rows={3}
                                    style={{
                                        width: '100%',
                                        resize: 'vertical',
                                        background: 'var(--bg-elevated)',
                                        border: '1px solid var(--border-default)',
                                        borderRadius: 'var(--radius-md)',
                                        color: 'var(--text-primary)',
                                        fontFamily: 'var(--font-sans)',
                                        fontSize: 13,
                                        padding: '10px 12px',
                                        outline: 'none',
                                        boxSizing: 'border-box',
                                        transition: 'border-color 0.2s ease',
                                    }}
                                    onFocus={(e) => { e.target.style.borderColor = 'var(--accent-primary)'; }}
                                    onBlur={(e) => { e.target.style.borderColor = 'var(--border-default)'; }}
                                />
                            </div>

                            {/* Photos grid */}
                            {zone.photos.length > 0 && (
                                <div>
                                    <div
                                        style={{
                                            fontSize: 12,
                                            fontWeight: 700,
                                            color: 'var(--text-secondary)',
                                            fontFamily: 'var(--font-sans)',
                                            textTransform: 'uppercase',
                                            letterSpacing: '0.06em',
                                            marginBottom: 10,
                                        }}
                                    >
                                        Zone Photos ({zone.photos.length})
                                    </div>
                                    <div
                                        style={{
                                            display: 'grid',
                                            gridTemplateColumns: 'repeat(auto-fill, minmax(160px, 1fr))',
                                            gap: 10,
                                        }}
                                    >
                                        <AnimatePresence>
                                            {zone.photos.map((photo, pIdx) => {
                                                const src = photo._preview ?? photo.photo_path;
                                                return (
                                                    <div key={photo.uuid ?? `photo-${pIdx}`}>
                                                        <PhotoCard
                                                            src={src}
                                                            caption={`Photo ${pIdx + 1}`}
                                                            onView={() => setViewingPhotoIdx(pIdx)}
                                                            onReplace={() => replaceInputRefs.current[pIdx]?.click()}
                                                            onDelete={() => handleDeletePhoto(pIdx)}
                                                        >
                                                            {/* Order controls */}
                                                            <div
                                                                style={{
                                                                    position: 'absolute',
                                                                    bottom: 0,
                                                                    left: 0,
                                                                    right: 0,
                                                                    display: 'flex',
                                                                    justifyContent: 'flex-end',
                                                                    gap: 4,
                                                                    padding: '4px 6px',
                                                                    background: 'rgba(0,0,0,0.55)',
                                                                }}
                                                            >
                                                                <button type="button" aria-label="Move photo left" onClick={(e) => { e.stopPropagation(); handleMovePhoto(pIdx, pIdx - 1); }} disabled={pIdx === 0} style={reorderBtnStyle}>
                                                                    <ChevronUp size={11} />
                                                                </button>
                                                                <button type="button" aria-label="Move photo right" onClick={(e) => { e.stopPropagation(); handleMovePhoto(pIdx, pIdx + 1); }} disabled={pIdx === zone.photos.length - 1} style={reorderBtnStyle}>
                                                                    <ChevronDown size={11} />
                                                                </button>
                                                            </div>
                                                            {/* Hidden replace input */}
                                                            <input
                                                                type="file"
                                                                accept="image/*"
                                                                className="sr-only"
                                                                aria-hidden="true"
                                                                ref={(el) => { replaceInputRefs.current[pIdx] = el; }}
                                                                onChange={(e) => {
                                                                    const file = e.target.files?.[0];
                                                                    if (file) handleReplacePhoto(pIdx, file);
                                                                    e.target.value = '';
                                                                }}
                                                            />
                                                        </PhotoCard>
                                                    </div>
                                                );
                                            })}
                                        </AnimatePresence>
                                    </div>
                                </div>
                            )}

                            {/* Add photos dropzone */}
                            <PhotoDropzone
                                onFiles={handleAddPhotos}
                                multiple
                                label={`Drop zone photos here or click to add`}
                                compact={zone.photos.length > 0}
                            />
                        </div>
                    </motion.div>
                )}
            </AnimatePresence>

            {/* Photo viewer modal */}
            <PhotoViewModal
                open={viewingPhotoIdx !== null}
                src={viewingPhoto ? (viewingPhoto._preview ?? viewingPhoto.photo_path) : ''}
                caption={`${zone.zone_name ?? `Zone ${index + 1}`} — Photo ${(viewingPhotoIdx ?? 0) + 1}`}
                onClose={() => setViewingPhotoIdx(null)}
            />
        </motion.div>
    );
}

const smallBtnStyle: React.CSSProperties = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: 26,
    height: 26,
    borderRadius: 'var(--radius-sm)',
    border: '1px solid var(--border-default)',
    background: 'var(--bg-card)',
    color: 'var(--text-muted)',
    cursor: 'pointer',
    transition: 'all 0.15s ease',
    flexShrink: 0,
};

const reorderBtnStyle: React.CSSProperties = {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    width: 20,
    height: 20,
    borderRadius: 'var(--radius-sm)',
    border: '1px solid var(--border-default)',
    background: 'rgba(0,0,0,0.5)',
    color: 'var(--text-secondary)',
    cursor: 'pointer',
};
