import * as React from 'react';
import { Search, MapPin, Loader2, AlertCircle } from 'lucide-react';
import { useClaimWizardStore } from '@/modules/claims/stores/claimWizardStore';
import { useProperties } from '@/modules/properties/hooks/useProperties';
import type { PropertyListItem } from '@/modules/properties/types';

interface GoogleMapsWindow {
    Map: new (el: HTMLElement, opts: object) => GoogleMapInstance;
    Marker: new (opts: object) => MarkerInstance;
    MapTypeId: { SATELLITE: string };
    importLibrary: (lib: string) => Promise<unknown>;
}

interface GoogleMapInstance {
    setCenter: (pos: { lat: number; lng: number }) => void;
    setZoom: (zoom: number) => void;
    setMapTypeId: (type: string) => void;
}

interface MarkerInstance {
    setMap: (m: null) => void;
}

let mapInstance: GoogleMapInstance | null = null;
let markerInstance: MarkerInstance | null = null;

async function loadGoogleMaps(apiKey: string): Promise<GoogleMapsWindow> {
    const existing = (window as unknown as { google?: { maps?: GoogleMapsWindow } }).google?.maps;
    if (existing?.Map) return existing as GoogleMapsWindow;

    await new Promise<void>((resolve, reject) => {
        const id = 'google-maps-full-script';
        if (document.getElementById(id)) {
            const s = document.getElementById(id) as HTMLScriptElement;
            s.addEventListener('load', () => resolve(), { once: true });
            return;
        }
        const script = document.createElement('script');
        script.id = id;
        script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(apiKey)}&libraries=places`;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => reject(new Error('Google Maps failed to load'));
        document.head.appendChild(script);
    });

    return (window as unknown as { google: { maps: GoogleMapsWindow } }).google.maps;
}

function getApiKey(): string {
    return (
        (import.meta.env.VITE_GOOGLE_MAPS_API_KEY as string | undefined) ??
        (import.meta.env.PUBLIC_GOOGLE_MAPS_API_KEY as string | undefined) ??
        ''
    );
}

interface Step1PropertyProps {
    onValidChange: (valid: boolean) => void;
}

export function Step1Property({ onValidChange }: Step1PropertyProps): React.JSX.Element {
    const { form, updateForm } = useClaimWizardStore();
    const [search, setSearch] = React.useState(form.property_address ?? '');
    const [mapReady, setMapReady] = React.useState(false);
    const [mapError, setMapError] = React.useState<string | null>(null);
    const [showDropdown, setShowDropdown] = React.useState(false);
    const mapContainerRef = React.useRef<HTMLDivElement | null>(null);

    const { data: propertiesData, isPending } = useProperties({ search, per_page: 20 });
    const properties: PropertyListItem[] = propertiesData?.data ?? [];

    React.useEffect(() => {
        onValidChange(form.property_id !== null);
    }, [form.property_id, onValidChange]);

    React.useEffect(() => {
        setShowDropdown(search.length >= 2 && properties.length > 0);
    }, [search, properties.length]);

    const initMap = React.useCallback(async (lat: number, lng: number) => {
        const apiKey = getApiKey();
        if (!apiKey || !mapContainerRef.current) return;

        try {
            const maps = await loadGoogleMaps(apiKey);

            if (!mapInstance && mapContainerRef.current) {
                mapInstance = new maps.Map(mapContainerRef.current, {
                    center: { lat, lng },
                    zoom: 18,
                    mapTypeId: maps.MapTypeId.SATELLITE,
                    disableDefaultUI: false,
                    tilt: 0,
                });
            } else if (mapInstance) {
                mapInstance.setCenter({ lat, lng });
                mapInstance.setZoom(18);
                mapInstance.setMapTypeId(maps.MapTypeId.SATELLITE);
            }

            if (markerInstance) markerInstance.setMap(null);
            markerInstance = new maps.Marker({ position: { lat, lng }, map: mapInstance });
            setMapReady(true);
        } catch {
            setMapError('Could not load satellite map.');
        }
    }, []);

    function selectProperty(prop: PropertyListItem): void {
        const lat = 25.7617;
        const lng = -80.1918;

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

    return (
        <div style={{ display: 'flex', flexDirection: 'column', gap: 24 }}>
            <div>
                <h3
                    style={{
                        fontSize: 18,
                        fontWeight: 700,
                        color: 'var(--text-primary)',
                        fontFamily: 'var(--font-sans)',
                        margin: 0,
                    }}
                >
                    Select Property
                </h3>
                <p style={{ fontSize: 13, color: 'var(--text-muted)', margin: '4px 0 0', fontFamily: 'var(--font-sans)' }}>
                    Search and select the property associated with this claim.
                </p>
            </div>

            <div style={{ position: 'relative' }}>
                <label
                    htmlFor="property-search"
                    style={{
                        display: 'block',
                        fontSize: 12,
                        fontWeight: 600,
                        color: 'var(--text-secondary)',
                        fontFamily: 'var(--font-sans)',
                        marginBottom: 6,
                        textTransform: 'uppercase',
                        letterSpacing: '0.08em',
                    }}
                >
                    Property Address *
                </label>
                <div style={{ position: 'relative' }}>
                    <span
                        style={{
                            position: 'absolute',
                            left: 12,
                            top: '50%',
                            transform: 'translateY(-50%)',
                            color: 'var(--text-muted)',
                            pointerEvents: 'none',
                        }}
                    >
                        {isPending ? <Loader2 size={16} className="animate-spin" /> : <Search size={16} />}
                    </span>
                    <input
                        id="property-search"
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        onFocus={() => { if (search.length >= 2 && properties.length > 0) setShowDropdown(true); }}
                        placeholder="Search by address..."
                        autoComplete="off"
                        style={{
                            width: '100%',
                            height: 44,
                            paddingLeft: 40,
                            paddingRight: 16,
                            background: 'var(--input-bg)',
                            border: `1px solid ${form.property_id ? 'var(--accent-success)' : 'var(--input-border)'}`,
                            borderRadius: 'var(--input-radius)',
                            color: 'var(--text-primary)',
                            fontSize: 14,
                            fontFamily: 'var(--font-sans)',
                            outline: 'none',
                            transition: 'border-color 0.2s ease',
                            boxSizing: 'border-box',
                        }}
                    />
                </div>

                {showDropdown && (
                    <div
                        style={{
                            position: 'absolute',
                            top: 'calc(100% + 4px)',
                            left: 0,
                            right: 0,
                            background: 'var(--bg-elevated)',
                            border: '1px solid var(--border-default)',
                            borderRadius: 'var(--radius-md)',
                            zIndex: 50,
                            maxHeight: 220,
                            overflowY: 'auto',
                            boxShadow: '0 8px 24px rgba(0,0,0,0.3)',
                        }}
                    >
                        {properties.map((prop) => (
                            <button
                                key={prop.uuid}
                                type="button"
                                onClick={() => selectProperty(prop)}
                                style={{
                                    width: '100%',
                                    padding: '10px 14px',
                                    display: 'flex',
                                    alignItems: 'center',
                                    gap: 10,
                                    background: 'transparent',
                                    border: 'none',
                                    borderBottom: '1px solid var(--border-subtle)',
                                    cursor: 'pointer',
                                    textAlign: 'left',
                                    color: 'var(--text-primary)',
                                    fontFamily: 'var(--font-sans)',
                                    fontSize: 13,
                                }}
                            >
                                <MapPin size={14} style={{ color: 'var(--accent-primary)', flexShrink: 0 }} />
                                <span>{prop.property_address}</span>
                            </button>
                        ))}
                    </div>
                )}

                {search.length >= 2 && !isPending && properties.length === 0 && (
                    <div
                        style={{
                            position: 'absolute',
                            top: 'calc(100% + 4px)',
                            left: 0,
                            right: 0,
                            background: 'var(--bg-elevated)',
                            border: '1px solid var(--border-default)',
                            borderRadius: 'var(--radius-md)',
                            padding: '14px 16px',
                            zIndex: 50,
                            fontSize: 13,
                            color: 'var(--text-muted)',
                            fontFamily: 'var(--font-sans)',
                        }}
                    >
                        No properties found.
                    </div>
                )}
            </div>

            {form.property_id !== null && (
                <div
                    style={{
                        display: 'flex',
                        alignItems: 'center',
                        gap: 10,
                        padding: '10px 14px',
                        background: 'color-mix(in srgb, var(--accent-success) 10%, var(--bg-card))',
                        border: '1px solid color-mix(in srgb, var(--accent-success) 30%, transparent)',
                        borderRadius: 'var(--radius-md)',
                    }}
                >
                    <MapPin size={16} style={{ color: 'var(--accent-success)', flexShrink: 0 }} />
                    <div>
                        <p style={{ margin: 0, fontSize: 13, fontWeight: 600, color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}>
                            {form.property_address}
                        </p>
                        <p style={{ margin: 0, fontSize: 11, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                            Property ID: {form.property_id}
                        </p>
                    </div>
                </div>
            )}

            <div>
                <p
                    style={{
                        fontSize: 12,
                        fontWeight: 600,
                        color: 'var(--text-secondary)',
                        fontFamily: 'var(--font-sans)',
                        textTransform: 'uppercase',
                        letterSpacing: '0.08em',
                        margin: '0 0 8px',
                    }}
                >
                    Satellite View
                </p>
                <div
                    ref={mapContainerRef}
                    style={{
                        width: '100%',
                        height: 'var(--map-container-height)',
                        borderRadius: 'var(--map-border-radius)',
                        border: '1px solid var(--map-border)',
                        overflow: 'hidden',
                        background: 'var(--bg-card)',
                        display: mapReady ? 'block' : 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                    }}
                >
                    {!mapReady && !mapError && (
                        <div style={{ textAlign: 'center', color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>
                            <MapPin size={32} style={{ opacity: 0.3, margin: '0 auto 8px', display: 'block' }} />
                            <p style={{ fontSize: 13, margin: 0 }}>
                                {form.property_id ? 'Loading satellite map...' : 'Select a property to view satellite map'}
                            </p>
                        </div>
                    )}
                    {mapError && (
                        <div
                            style={{
                                display: 'flex',
                                alignItems: 'center',
                                gap: 8,
                                color: 'var(--accent-warning)',
                                fontFamily: 'var(--font-sans)',
                                fontSize: 13,
                            }}
                        >
                            <AlertCircle size={16} />
                            <span>{mapError}</span>
                        </div>
                    )}
                </div>
            </div>
        </div>
    );
}
