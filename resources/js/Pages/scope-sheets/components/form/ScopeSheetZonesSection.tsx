import * as React from 'react';
import { AnimatePresence } from 'framer-motion';
import { LayoutGrid, Plus, Search } from 'lucide-react';
import { keepPreviousData, useQuery } from '@tanstack/react-query';
import axios from 'axios';
import { ZoneCard } from './ZoneCard';
import type { ScopeSheetZone } from '@/modules/scope-sheets/types';
import type { PaginatedResponse } from '@/types/api';

interface ZoneCatalogItem {
    id: number;
    uuid: string;
    zone_name: string;
    zone_type: string;
}

type PaginatedZoneCatalog = PaginatedResponse<ZoneCatalogItem>;

function useZoneCatalog(search: string) {
    return useQuery<PaginatedZoneCatalog, Error>({
        queryKey: ['zones', 'catalog', search],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedZoneCatalog>('/zones/data/admin', {
                params: { search: search || undefined, per_page: 50, status: 'active' },
            });
            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 5,
    });
}

interface Props {
    zones: ScopeSheetZone[];
    onChange: (zones: ScopeSheetZone[]) => void;
}

export function ScopeSheetZonesSection({ zones, onChange }: Props): React.JSX.Element {
    const [search, setSearch] = React.useState('');
    const [showPicker, setShowPicker] = React.useState(false);
    const [dragIdx, setDragIdx] = React.useState<number | null>(null);
    const [dragOverIdx, setDragOverIdx] = React.useState<number | null>(null);
    const pickerRef = React.useRef<HTMLDivElement | null>(null);

    const { data: catalogData, isPending } = useZoneCatalog(search);
    const catalogZones = catalogData?.data ?? [];

    const usedZoneIds = React.useMemo(() => new Set(zones.map((z) => z.zone_id)), [zones]);

    function handleAddZone(catalogItem: ZoneCatalogItem): void {
        if (usedZoneIds.has(catalogItem.id)) return;
        const newZone: ScopeSheetZone = {
            zone_id: catalogItem.id,
            zone_name: catalogItem.zone_name,
            zone_order: zones.length,
            zone_notes: '',
            photos: [],
        };
        onChange([...zones, newZone]);
        setShowPicker(false);
        setSearch('');
    }

    function handleUpdateZone(idx: number, zone: ScopeSheetZone): void {
        const updated = [...zones];
        updated[idx] = zone;
        onChange(updated);
    }

    function handleDeleteZone(idx: number): void {
        const updated = [...zones];
        updated.splice(idx, 1);
        onChange(updated.map((z, i) => ({ ...z, zone_order: i })));
    }

    function moveZone(from: number, to: number): void {
        if (to < 0 || to >= zones.length) return;
        const updated = [...zones];
        const [item] = updated.splice(from, 1);
        updated.splice(to, 0, item);
        onChange(updated.map((z, i) => ({ ...z, zone_order: i })));
    }

    /* ── Drag & Drop ── */
    function handleDragStart(idx: number): void { setDragIdx(idx); }
    function handleDragEnter(idx: number): void { setDragOverIdx(idx); }
    function handleDragEnd(): void {
        if (dragIdx !== null && dragOverIdx !== null && dragIdx !== dragOverIdx) {
            moveZone(dragIdx, dragOverIdx);
        }
        setDragIdx(null);
        setDragOverIdx(null);
    }

    /* ── Close picker on outside click ── */
    React.useEffect(() => {
        if (!showPicker) return;
        function handle(e: MouseEvent): void {
            if (pickerRef.current && !pickerRef.current.contains(e.target as Node)) {
                setShowPicker(false);
            }
        }
        document.addEventListener('mousedown', handle);
        return () => document.removeEventListener('mousedown', handle);
    }, [showPicker]);

    return (
        <section aria-labelledby="zones-heading">
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
                        background: 'color-mix(in srgb, var(--accent-secondary) 15%, var(--bg-card))',
                        border: '1px solid color-mix(in srgb, var(--accent-secondary) 30%, transparent)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        color: 'var(--accent-secondary)',
                        flexShrink: 0,
                    }}
                >
                    <LayoutGrid size={18} />
                </div>
                <div>
                    <h3
                        id="zones-heading"
                        style={{ margin: 0, fontSize: 15, fontWeight: 700, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}
                    >
                        Damage Zones
                    </h3>
                    <p style={{ margin: 0, fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                        Add zones from the catalog. Each zone can have notes and photos. Drag to reorder.
                    </p>
                </div>
                <span
                    style={{
                        marginLeft: 'auto',
                        fontSize: 11,
                        fontWeight: 700,
                        color: 'var(--accent-secondary)',
                        background: 'color-mix(in srgb, var(--accent-secondary) 12%, var(--bg-card))',
                        border: '1px solid color-mix(in srgb, var(--accent-secondary) 25%, transparent)',
                        borderRadius: 999,
                        padding: '2px 10px',
                        fontFamily: 'var(--font-sans)',
                    }}
                >
                    {zones.length} zone{zones.length !== 1 ? 's' : ''}
                </span>
            </div>

            {/* Zone list */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 12, marginBottom: 14 }}>
                <AnimatePresence>
                    {zones.map((zone, idx) => (
                        <ZoneCard
                            key={zone.uuid ?? `zone-${zone.zone_id}-${idx}`}
                            zone={zone}
                            index={idx}
                            total={zones.length}
                            isDragging={dragIdx === idx}
                            isDragOver={dragOverIdx === idx && dragIdx !== null && dragIdx !== idx}
                            onDragStart={() => handleDragStart(idx)}
                            onDragEnter={() => handleDragEnter(idx)}
                            onDragEnd={handleDragEnd}
                            onUpdate={(z) => handleUpdateZone(idx, z)}
                            onDelete={() => handleDeleteZone(idx)}
                            onMoveUp={() => moveZone(idx, idx - 1)}
                            onMoveDown={() => moveZone(idx, idx + 1)}
                        />
                    ))}
                </AnimatePresence>
            </div>

            {/* Add zone picker */}
            <div style={{ position: 'relative' }} ref={pickerRef}>
                <button
                    type="button"
                    aria-label="Add zone"
                    aria-haspopup="listbox"
                    aria-expanded={showPicker}
                    onClick={() => setShowPicker((v) => !v)}
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 8,
                        width: '100%',
                        padding: '12px 16px',
                        borderRadius: 'var(--radius-lg)',
                        border: '2px dashed var(--border-default)',
                        background: 'transparent',
                        color: 'var(--text-muted)',
                        fontSize: 13,
                        fontWeight: 600,
                        fontFamily: 'var(--font-sans)',
                        cursor: 'pointer',
                        transition: 'all 0.2s ease',
                        justifyContent: 'center',
                    }}
                    onMouseEnter={(e) => {
                        e.currentTarget.style.borderColor = 'var(--accent-primary)';
                        e.currentTarget.style.color = 'var(--accent-primary)';
                        e.currentTarget.style.background = 'color-mix(in srgb, var(--accent-primary) 6%, transparent)';
                    }}
                    onMouseLeave={(e) => {
                        e.currentTarget.style.borderColor = 'var(--border-default)';
                        e.currentTarget.style.color = 'var(--text-muted)';
                        e.currentTarget.style.background = 'transparent';
                    }}
                >
                    <Plus size={16} />
                    Add Zone from Catalog
                </button>

                {/* Zone picker dropdown */}
                {showPicker && (
                    <div
                        role="listbox"
                        aria-label="Select zone"
                        style={{
                            position: 'absolute',
                            top: 'calc(100% + 6px)',
                            left: 0,
                            right: 0,
                            zIndex: 100,
                            background: 'var(--bg-elevated)',
                            border: '1px solid var(--border-default)',
                            borderRadius: 'var(--radius-lg)',
                            boxShadow: '0 12px 40px color-mix(in srgb, var(--bg-void) 35%, transparent)',
                            overflow: 'hidden',
                            maxHeight: 340,
                            display: 'flex',
                            flexDirection: 'column',
                        }}
                    >
                        {/* Search */}
                        <div
                            style={{
                                padding: '10px 12px',
                                borderBottom: '1px solid var(--border-subtle)',
                                display: 'flex',
                                alignItems: 'center',
                                gap: 8,
                            }}
                        >
                            <Search size={14} style={{ color: 'var(--text-muted)', flexShrink: 0 }} />
                            <input
                                type="search"
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="Search zones..."
                                autoFocus
                                style={{
                                    flex: 1,
                                    background: 'transparent',
                                    border: 'none',
                                    outline: 'none',
                                    color: 'var(--text-primary)',
                                    fontSize: 13,
                                    fontFamily: 'var(--font-sans)',
                                }}
                            />
                        </div>

                        {/* List */}
                        <div style={{ overflowY: 'auto', flex: 1 }}>
                            {isPending && (
                                <div style={{ padding: '20px', textAlign: 'center', fontSize: 13, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                    Loading zones…
                                </div>
                            )}
                            {!isPending && catalogZones.length === 0 && (
                                <div style={{ padding: '20px', textAlign: 'center', fontSize: 13, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                    No zones found
                                </div>
                            )}
                            {catalogZones.map((z) => {
                                const alreadyAdded = usedZoneIds.has(z.id);
                                return (
                                    <button
                                        key={z.uuid}
                                        type="button"
                                        role="option"
                                        aria-selected={alreadyAdded}
                                        disabled={alreadyAdded}
                                        onClick={() => handleAddZone(z)}
                                        style={{
                                            display: 'flex',
                                            alignItems: 'center',
                                            gap: 10,
                                            width: '100%',
                                            padding: '10px 14px',
                                            background: alreadyAdded ? 'color-mix(in srgb, var(--accent-success) 8%, var(--bg-elevated))' : 'transparent',
                                            border: 'none',
                                            borderBottom: '1px solid var(--border-subtle)',
                                            cursor: alreadyAdded ? 'default' : 'pointer',
                                            textAlign: 'left',
                                            transition: 'background 0.15s ease',
                                            opacity: alreadyAdded ? 0.6 : 1,
                                        }}
                                        onMouseEnter={(e) => { if (!alreadyAdded) e.currentTarget.style.background = 'var(--bg-hover)'; }}
                                        onMouseLeave={(e) => { if (!alreadyAdded) e.currentTarget.style.background = 'transparent'; }}
                                    >
                                        <div
                                            style={{
                                                width: 8,
                                                height: 8,
                                                borderRadius: '50%',
                                                background: alreadyAdded ? 'var(--accent-success)' : 'var(--accent-primary)',
                                                flexShrink: 0,
                                            }}
                                        />
                                        <div style={{ flex: 1, minWidth: 0 }}>
                                            <div style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                                                {z.zone_name}
                                            </div>
                                            <div style={{ fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)', textTransform: 'capitalize' }}>
                                                {z.zone_type}
                                            </div>
                                        </div>
                                        {alreadyAdded && (
                                            <span style={{ fontSize: 10, fontWeight: 700, color: 'var(--accent-success)', fontFamily: 'var(--font-sans)', textTransform: 'uppercase', letterSpacing: '0.06em' }}>
                                                Added
                                            </span>
                                        )}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                )}
            </div>
        </section>
    );
}
