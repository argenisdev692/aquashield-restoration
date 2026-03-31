import * as React from 'react';
import { MapPin, Loader2, AlertCircle, CheckCircle2, Building2, Search, Navigation } from 'lucide-react';
import { useClaimWizardStore } from '@/modules/claims/stores/claimWizardStore';
import { useProperties } from '@/modules/properties/hooks/useProperties';
import type { PropertyListItem } from '@/modules/properties/types';

// ── Google Maps types ────────────────────────────────────────────────────────

interface LatLngLiteral { lat: number; lng: number; }

interface GMapsInstance {
    setCenter: (pos: LatLngLiteral) => void;
    setZoom: (z: number) => void;
    setMapTypeId: (t: string) => void;
}

interface GMarkerInstance { setMap: (m: null) => void; }

interface GMapLibrary {
    Map: new (el: HTMLElement, opts: object) => GMapsInstance;
    MapTypeId: { SATELLITE: string };
}

interface GMarkerLibrary {
    Marker: new (opts: object) => GMarkerInstance;
}

interface GAddressComponent {
    longText: string;
    shortText: string;
    types: string[];
}

interface GLatLng { lat: () => number; lng: () => number; }

interface GPlace {
    addressComponents?: GAddressComponent[];
    formattedAddress?: string;
    location?: GLatLng;
    fetchFields: (opts: { fields: string[] }) => Promise<{ place: GPlace }>;
}

interface GPlacePrediction { toPlace: () => GPlace; }

interface GmpSelectEvent extends Event { placePrediction: GPlacePrediction; }

interface GPlaceAutocompleteEl {
    setAttribute(n: string, v: string): void;
    addEventListener(type: 'gmp-select', fn: (e: GmpSelectEvent) => void): void;
    removeEventListener(type: 'gmp-select', fn: (e: GmpSelectEvent) => void): void;
    remove(): void;
}

interface GPlacesLibrary {
    PlaceAutocompleteElement: new (opts?: { includedRegionCodes?: string[]; includedPrimaryTypes?: string[] }) => GPlaceAutocompleteEl;
}

// ── Shared script loader (same key as useGoogleMapsAddressAutocomplete) ───────

const BOOTSTRAP_ID = 'google-maps-places-script';
let bootstrapPromise: Promise<void> | null = null;

function getApiKey(): string {
    const k1 = import.meta.env.VITE_GOOGLE_MAPS_API_KEY as string | undefined;
    const k2 = import.meta.env.PUBLIC_GOOGLE_MAPS_API_KEY as string | undefined;
    return (k1?.trim() ?? k2?.trim() ?? '');
}

function loadBootstrap(apiKey: string): Promise<void> {
    if (window.google?.maps?.importLibrary) return Promise.resolve();
    if (bootstrapPromise) return bootstrapPromise;
    bootstrapPromise = new Promise<void>((resolve, reject) => {
        const existing = document.getElementById(BOOTSTRAP_ID);
        if (existing instanceof HTMLScriptElement) {
            existing.addEventListener('load', () => window.google?.maps?.importLibrary ? resolve() : reject(new Error('no importLibrary')), { once: true });
            existing.addEventListener('error', () => reject(new Error('script error')), { once: true });
            return;
        }
        const s = document.createElement('script');
        s.id = BOOTSTRAP_ID; s.async = true; s.defer = true;
        s.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&loading=async&libraries=places`;
        s.addEventListener('load', () => window.google?.maps?.importLibrary ? resolve() : reject(new Error('no importLibrary')), { once: true });
        s.addEventListener('error', () => reject(new Error('script error')), { once: true });
        document.head.appendChild(s);
    }).catch((e: unknown) => { bootstrapPromise = null; throw e; });
    return bootstrapPromise;
}

// ── Module-level map state ───────────────────────────────────────────────────

let mapInst: GMapsInstance | null = null;
let markerInst: GMarkerInstance | null = null;

// ── Component ────────────────────────────────────────────────────────────────

interface Step1PropertyProps { onValidChange: (valid: boolean) => void; }

export function Step1Property({ onValidChange }: Step1PropertyProps): React.JSX.Element {
    const { form, updateForm } = useClaimWizardStore();

    const [search, setSearch]           = React.useState(form.property_address ?? '');
    const [mapReady, setMapReady]       = React.useState(false);
    const [mapError, setMapError]       = React.useState<string | null>(null);
    const [acError, setAcError]         = React.useState<string | null>(null);
    const [acLoading, setAcLoading]     = React.useState(false);
    const [showDropdown, setShowDropdown] = React.useState(false);

    const mapContainerRef   = React.useRef<HTMLDivElement | null>(null);
    const acContainerRef    = React.useRef<HTMLDivElement | null>(null);
    const acElementRef      = React.useRef<GPlaceAutocompleteEl | null>(null);

    const { data: propertiesData, isPending } = useProperties({ search, per_page: 20 });
    const properties: PropertyListItem[] = propertiesData?.data ?? [];

    // ── Validity ─────────────────────────────────────────────────────────────
    React.useEffect(() => { onValidChange(form.property_id !== null); }, [form.property_id, onValidChange]);

    // ── Show/hide DB dropdown ─────────────────────────────────────────────────
    React.useEffect(() => {
        setShowDropdown(search.length >= 2 && properties.length > 0 && !isPending);
    }, [search, properties.length, isPending]);

    // ── Load map ──────────────────────────────────────────────────────────────
    const initMap = React.useCallback(async (lat: number, lng: number): Promise<void> => {
        if (!mapContainerRef.current) return;
        const apiKey = getApiKey();
        if (!apiKey) return;
        try {
            await loadBootstrap(apiKey);
            const [mapsLib, markerLib] = await Promise.all([
                window.google!.maps.importLibrary('maps') as Promise<GMapLibrary>,
                window.google!.maps.importLibrary('marker') as Promise<GMarkerLibrary>,
            ]);
            const pos = { lat, lng };
            if (!mapInst && mapContainerRef.current) {
                mapInst = new mapsLib.Map(mapContainerRef.current, {
                    center: pos, zoom: 18,
                    mapTypeId: mapsLib.MapTypeId.SATELLITE,
                    disableDefaultUI: false, tilt: 0,
                });
            } else if (mapInst) {
                mapInst.setCenter(pos);
                mapInst.setZoom(18);
                mapInst.setMapTypeId(mapsLib.MapTypeId.SATELLITE);
            }
            if (markerInst) markerInst.setMap(null);
            markerInst = new markerLib.Marker({ position: pos, map: mapInst });
            setMapReady(true);
        } catch {
            setMapError('Could not load satellite map.');
        }
    }, []);

    // ── Google Places Autocomplete ────────────────────────────────────────────
    React.useEffect(() => {
        const container = acContainerRef.current;
        if (!container) return;

        const apiKey = getApiKey();
        if (!apiKey) {
            setAcError('Google Maps API key is missing.');
            return;
        }

        let cancelled = false;
        let el: GPlaceAutocompleteEl | null = null;
        setAcLoading(true);
        setAcError(null);

        const handleSelect = (event: GmpSelectEvent): void => {
            if (cancelled) return;
            const place = event.placePrediction.toPlace();
            void place.fetchFields({ fields: ['addressComponents', 'formattedAddress', 'location'] })
                .then(({ place: p }) => {
                    if (cancelled) return;
                    const addr = p.formattedAddress ?? '';
                    setSearch(addr);
                    setShowDropdown(true);

                    const lat = p.location?.lat() ?? 25.7617;
                    const lng = p.location?.lng() ?? -80.1918;
                    updateForm({ property_address: addr, property_lat: lat, property_lng: lng });
                    void initMap(lat, lng);
                })
                .catch(() => { if (!cancelled) setAcError('Could not retrieve address details.'); });
        };

        void loadBootstrap(apiKey)
            .then(() => window.google!.maps.importLibrary('places') as Promise<GPlacesLibrary>)
            .then(({ PlaceAutocompleteElement }) => {
                if (cancelled || !acContainerRef.current) return;

                el = new PlaceAutocompleteElement({
                    includedRegionCodes: ['US'],
                    includedPrimaryTypes: ['address'],
                });

                el.setAttribute('placeholder', 'Search by address…');

                const domEl = el as unknown as HTMLElement;
                domEl.style.cssText = 'display:block;width:100%;';
                acContainerRef.current.appendChild(domEl);
                el.addEventListener('gmp-select', handleSelect);
                acElementRef.current = el;
                setAcLoading(false);
            })
            .catch(() => {
                if (!cancelled) { setAcError('Google Maps autocomplete could not be loaded.'); setAcLoading(false); }
            });

        return () => {
            cancelled = true;
            if (el) { el.removeEventListener('gmp-select', handleSelect); (el as unknown as HTMLElement).remove(); }
            acElementRef.current = null;
        };
    // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    // ── Select a DB property ──────────────────────────────────────────────────
    function selectProperty(prop: PropertyListItem): void {
        const lat = prop.property_latitude ? parseFloat(prop.property_latitude) : (form.property_lat ?? 25.7617);
        const lng = prop.property_longitude ? parseFloat(prop.property_longitude) : (form.property_lng ?? -80.1918);
        updateForm({
            property_id: prop.property_id,
            property_address: prop.property_address,
            property_lat: lat,
            property_lng: lng,
        });
        setSearch(prop.property_address);
        setShowDropdown(false);
        void initMap(lat, lng);
    }

    function clearSelection(): void {
        updateForm({ property_id: null, property_address: '', property_lat: 0, property_lng: 0 });
        setSearch('');
        setMapReady(false);
        mapInst = null;
        markerInst = null;
    }

    const isSelected = form.property_id !== null;

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 28, fontFamily: 'var(--font-sans)' }}>

            {/* ── Header ── */}
            <div style={{ display: 'flex', alignItems: 'flex-start', gap: 14 }}>
                <div style={{
                    width: 44, height: 44, borderRadius: 12, flexShrink: 0,
                    background: 'color-mix(in srgb, var(--accent-primary) 15%, var(--bg-card))',
                    border: '1px solid color-mix(in srgb, var(--accent-primary) 30%, transparent)',
                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                }}>
                    <Building2 size={20} style={{ color: 'var(--accent-primary)' }} />
                </div>
                <div>
                    <h3 style={{ fontSize: 18, fontWeight: 700, color: 'var(--text-primary)', margin: 0, lineHeight: 1.3 }}>
                        Select Property
                    </h3>
                    <p style={{ fontSize: 13, color: 'var(--text-muted)', margin: '3px 0 0' }}>
                        Search and select the property associated with this claim.
                    </p>
                </div>
            </div>

            {/* ── Search + Map stacked layout ── */}
            <div style={{ display: 'flex', flexDirection: 'column', gap: 20 }}>

                {/* ── LEFT: Search panel ── */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 16 }}>

                    {/* Search label */}
                    <label style={{
                        fontSize: 11, fontWeight: 700, color: 'var(--text-muted)',
                        textTransform: 'uppercase', letterSpacing: '0.1em',
                        display: 'flex', alignItems: 'center', gap: 6,
                    }}>
                        <Search size={11} />
                        Property Address
                        <span style={{ color: 'var(--accent-error)', marginLeft: 2 }}>*</span>
                    </label>

                    {/* Google Places autocomplete container */}
                    <div style={{ position: 'relative' }}>
                        <div
                            ref={acContainerRef}
                            style={{
                                width: '100%',
                                minHeight: 44,
                                borderRadius: 'var(--radius-md)',
                            }}
                        />
                        {acLoading && (
                            <div style={{
                                position: 'absolute', right: 12, top: '50%',
                                transform: 'translateY(-50%)',
                            }}>
                                <Loader2 size={15} style={{ color: 'var(--text-muted)', animation: 'spin 1s linear infinite' }} />
                            </div>
                        )}
                    </div>

                    {acError && (
                        <div style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--accent-warning)' }}>
                            <AlertCircle size={13} /> {acError}
                        </div>
                    )}

                    {/* ── Matching properties list ── */}
                    {(showDropdown || isPending) && (
                        <div style={{
                            background: 'var(--bg-elevated)',
                            border: '1px solid var(--border-default)',
                            borderRadius: 'var(--radius-md)',
                            overflow: 'hidden',
                            boxShadow: '0 8px 24px rgba(0,0,0,0.3)',
                        }}>
                            <div style={{
                                padding: '8px 14px 6px',
                                fontSize: 10, fontWeight: 700, letterSpacing: '0.1em',
                                color: 'var(--text-muted)', textTransform: 'uppercase',
                                borderBottom: '1px solid var(--border-subtle)',
                            }}>
                                {isPending ? 'Searching…' : `${properties.length} propert${properties.length === 1 ? 'y' : 'ies'} found`}
                            </div>

                            {isPending && (
                                <div style={{ padding: '16px 14px', display: 'flex', alignItems: 'center', gap: 8, color: 'var(--text-muted)', fontSize: 13 }}>
                                    <Loader2 size={14} className="animate-spin" /> Searching properties…
                                </div>
                            )}

                            {!isPending && properties.map((prop) => (
                                <button
                                    key={prop.uuid}
                                    type="button"
                                    onClick={() => selectProperty(prop)}
                                    style={{
                                        width: '100%', padding: '10px 14px',
                                        display: 'flex', alignItems: 'flex-start', gap: 10,
                                        background: form.property_id === prop.property_id
                                            ? 'color-mix(in srgb, var(--accent-primary) 12%, var(--bg-elevated))'
                                            : 'transparent',
                                        border: 'none',
                                        borderBottom: '1px solid var(--border-subtle)',
                                        cursor: 'pointer', textAlign: 'left',
                                        transition: 'background 0.15s ease',
                                    }}
                                >
                                    <MapPin size={14} style={{ color: 'var(--accent-primary)', flexShrink: 0, marginTop: 2 }} />
                                    <div>
                                        <p style={{ margin: 0, fontSize: 13, fontWeight: 500, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', lineHeight: 1.4 }}>
                                            {prop.property_address}
                                        </p>
                                        {(prop.property_city ?? prop.property_state) && (
                                            <p style={{ margin: '2px 0 0', fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                                {[prop.property_city, prop.property_state, prop.property_postal_code].filter(Boolean).join(', ')}
                                            </p>
                                        )}
                                    </div>
                                    {form.property_id === prop.property_id && (
                                        <CheckCircle2 size={14} style={{ color: 'var(--accent-primary)', flexShrink: 0, marginLeft: 'auto', marginTop: 2 }} />
                                    )}
                                </button>
                            ))}

                            {!isPending && properties.length === 0 && search.length >= 2 && (
                                <div style={{ padding: '14px 16px', fontSize: 13, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                    No properties found matching this address.
                                </div>
                            )}
                        </div>
                    )}

                    {/* ── Selected property badge ── */}
                    {isSelected && (
                        <div style={{
                            display: 'flex', alignItems: 'flex-start', gap: 12,
                            padding: '14px 16px',
                            background: 'color-mix(in srgb, var(--accent-success) 10%, var(--bg-card))',
                            border: '1px solid color-mix(in srgb, var(--accent-success) 30%, transparent)',
                            borderRadius: 'var(--radius-md)',
                        }}>
                            <CheckCircle2 size={18} style={{ color: 'var(--accent-success)', flexShrink: 0, marginTop: 1 }} />
                            <div style={{ flex: 1, minWidth: 0 }}>
                                <p style={{ margin: 0, fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)', wordBreak: 'break-word' }}>
                                    {form.property_address}
                                </p>
                                <p style={{ margin: '3px 0 0', fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                    Property ID #{form.property_id}
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={clearSelection}
                                style={{
                                    background: 'none', border: 'none', cursor: 'pointer',
                                    color: 'var(--text-muted)', fontSize: 11, fontFamily: 'var(--font-sans)',
                                    padding: '2px 6px', borderRadius: 4,
                                    transition: 'color 0.15s',
                                    flexShrink: 0,
                                }}
                            >
                                Change
                            </button>
                        </div>
                    )}

                    {/* ── GPS hint ── */}
                    {!isSelected && (
                        <div style={{
                            display: 'flex', alignItems: 'center', gap: 8,
                            padding: '10px 14px',
                            background: 'color-mix(in srgb, var(--accent-info) 8%, var(--bg-card))',
                            border: '1px solid color-mix(in srgb, var(--accent-info) 20%, transparent)',
                            borderRadius: 'var(--radius-md)',
                        }}>
                            <Navigation size={13} style={{ color: 'var(--accent-info)', flexShrink: 0 }} />
                            <p style={{ margin: 0, fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                                Type the address above — Google will autocomplete it and the satellite map will update automatically.
                            </p>
                        </div>
                    )}
                </div>

                {/* ── RIGHT: Map panel ── */}
                <div style={{ display: 'flex', flexDirection: 'column', gap: 10 }}>
                    <label style={{
                        fontSize: 11, fontWeight: 700, color: 'var(--text-muted)',
                        textTransform: 'uppercase', letterSpacing: '0.1em',
                        display: 'flex', alignItems: 'center', gap: 6,
                    }}>
                        <MapPin size={11} />
                        Satellite View
                    </label>

                    <div
                        ref={mapContainerRef}
                        style={{
                            width: '100%',
                            height: 420,
                            borderRadius: 'var(--radius-lg)',
                            border: '1px solid var(--border-default)',
                            overflow: 'hidden',
                            background: 'var(--bg-card)',
                            display: mapReady ? 'block' : 'flex',
                            alignItems: 'center',
                            justifyContent: 'center',
                            position: 'relative',
                            boxShadow: '0 4px 20px rgba(0,0,0,0.25)',
                        }}
                    >
                        {!mapReady && !mapError && (
                            <div style={{ textAlign: 'center', color: 'var(--text-muted)', fontFamily: 'var(--font-sans)', padding: 24 }}>
                                <div style={{
                                    width: 56, height: 56, borderRadius: '50%', margin: '0 auto 14px',
                                    background: 'color-mix(in srgb, var(--accent-primary) 10%, var(--bg-surface))',
                                    border: '1px solid color-mix(in srgb, var(--accent-primary) 20%, transparent)',
                                    display: 'flex', alignItems: 'center', justifyContent: 'center',
                                }}>
                                    <MapPin size={24} style={{ color: 'var(--accent-primary)', opacity: 0.6 }} />
                                </div>
                                <p style={{ fontSize: 13, margin: 0, fontWeight: 500, color: 'var(--text-secondary)' }}>
                                    {isSelected ? 'Loading satellite map…' : 'Select a property to view satellite map'}
                                </p>
                                {!isSelected && (
                                    <p style={{ fontSize: 11, margin: '6px 0 0', color: 'var(--text-muted)' }}>
                                        The map will show the property location
                                    </p>
                                )}
                            </div>
                        )}
                        {mapError && (
                            <div style={{
                                display: 'flex', flexDirection: 'column', alignItems: 'center', gap: 8,
                                color: 'var(--accent-warning)', fontFamily: 'var(--font-sans)', fontSize: 13, padding: 24,
                                textAlign: 'center',
                            }}>
                                <AlertCircle size={24} />
                                <span>{mapError}</span>
                            </div>
                        )}
                    </div>

                    {mapReady && (
                        <div style={{
                            display: 'flex', alignItems: 'center', gap: 6,
                            fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)',
                        }}>
                            <div style={{ width: 6, height: 6, borderRadius: '50%', background: 'var(--accent-success)', flexShrink: 0 }} />
                            Satellite view loaded · {form.property_lat?.toFixed(4)}, {form.property_lng?.toFixed(4)}
                        </div>
                    )}
                </div>
            </div>

        </div>
    );
}
