export type ZoneType =
    | 'interior'
    | 'exterior'
    | 'basement'
    | 'attic'
    | 'garage'
    | 'crawlspace';

export interface ZoneListItem {
    uuid: string;
    zone_name: string;
    zone_type: ZoneType;
    code: string | null;
    description: string | null;
    user_id: number;
    created_at: string;
    deleted_at: string | null;
}

export interface Zone {
    uuid: string;
    zoneName: string;
    zoneType: ZoneType;
    code: string | null;
    description: string | null;
    userId: number;
    createdAt: string;
    updatedAt: string;
    deletedAt: string | null;
}

export interface ZoneFilters {
    search?: string;
    zone_type?: ZoneType;
    status?: 'active' | 'deleted';
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface ZoneFormData {
    zone_name: string;
    zone_type: ZoneType;
    code: string;
    description: string;
    user_id: number;
}

export interface PaginatedZoneResponse {
    data: ZoneListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export const ZONE_TYPE_LABELS: Record<ZoneType, string> = {
    interior:   'Interior',
    exterior:   'Exterior',
    basement:   'Basement',
    attic:      'Attic',
    garage:     'Garage',
    crawlspace: 'Crawlspace',
};
