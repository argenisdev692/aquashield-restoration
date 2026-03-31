// ─── Scope Sheet Domain Types ─────────────────────────────────────────────────

export interface ScopeSheetPresentation {
    uuid?: string;
    photo_type: string;
    photo_path: string;
    photo_order: number;
    /** Local preview URL (object URL) — not sent to backend */
    _preview?: string;
    /** Pending File to upload — not sent to backend */
    _file?: File;
}

export interface ScopeSheetZonePhoto {
    uuid?: string;
    photo_path: string;
    photo_order: number;
    /** Local preview URL (object URL) — not sent to backend */
    _preview?: string;
    /** Pending File to upload — not sent to backend */
    _file?: File;
}

export interface ScopeSheetZone {
    uuid?: string;
    zone_id: number;
    zone_name?: string;
    zone_order: number;
    zone_notes: string;
    photos: ScopeSheetZonePhoto[];
}

export interface ScopeSheet {
    uuid: string;
    claim_id: number;
    claim_number: string | null;
    claim_internal_id: string | null;
    property_address: string | null;
    generated_by: number;
    generated_by_name: string | null;
    scope_sheet_description: string | null;
    presentations: ScopeSheetPresentation[];
    zones: ScopeSheetZone[];
    export_record: ScopeSheetExportRecord | null;
    status: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface ScopeSheetExportRecord {
    uuid: string;
    format: string;
    file_path: string | null;
    file_url: string | null;
    created_at: string;
}

export interface ScopeSheetListItem {
    uuid: string;
    claim_id: number;
    claim_number: string | null;
    claim_internal_id: string | null;
    generated_by: number;
    generated_by_name: string | null;
    scope_sheet_description: string | null;
    presentations_count: number;
    zones_count: number;
    status: string;
    created_at: string;
    deleted_at: string | null;
}

export interface ScopeSheetFilters {
    search?: string;
    status?: 'active' | 'deleted';
    date_from?: string;
    date_to?: string;
    claim_id?: number;
    page?: number;
    per_page?: number;
}

export interface PaginatedScopeSheetResponse {
    data: ScopeSheetListItem[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
}

// ─── Form types ────────────────────────────────────────────────────────────────

export const PRESENTATION_PHOTO_TYPES = [
    'front_house',
    'back_house',
    'left_house',
    'right_house',
    'roof',
    'driveway',
    'entrance',
    'other',
] as const;

export type PresentationPhotoType = typeof PRESENTATION_PHOTO_TYPES[number];

export const PRESENTATION_PHOTO_TYPE_LABELS: Record<string, string> = {
    front_house: 'Front House',
    back_house:  'Back House',
    left_house:  'Left Side',
    right_house: 'Right Side',
    roof:        'Roof',
    driveway:    'Driveway',
    entrance:    'Entrance',
    other:       'Other',
};

export interface ScopeSheetFormData {
    claim_id: number | null;
    generated_by: number | null;
    scope_sheet_description: string;
    presentations: ScopeSheetPresentation[];
    zones: ScopeSheetZone[];
}

export const DEFAULT_SCOPE_SHEET_FORM: ScopeSheetFormData = {
    claim_id: null,
    generated_by: null,
    scope_sheet_description: '',
    presentations: [],
    zones: [],
};

// ─── Photo upload response ─────────────────────────────────────────────────────

export interface PhotoUploadResponse {
    path: string;
    url: string;
}
