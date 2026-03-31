export type CustomerPropertyRole = 'owner' | 'co-owner' | 'additional-signer';

export interface PropertyCustomer {
    uuid: string;
    name: string;
    last_name: string | null;
    email: string;
    role: CustomerPropertyRole;
}

export interface PropertyListItem {
    property_id: number;
    uuid: string;
    property_address: string;
    property_address_2: string | null;
    property_state: string | null;
    property_city: string | null;
    property_postal_code: string | null;
    property_country: string | null;
    property_latitude: string | null;
    property_longitude: string | null;
    created_at: string;
    deleted_at: string | null;
}

export interface Property {
    uuid: string;
    property_address: string;
    property_address_2: string | null;
    property_state: string | null;
    property_city: string | null;
    property_postal_code: string | null;
    property_country: string | null;
    property_latitude: string | null;
    property_longitude: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
    customers?: PropertyCustomer[];
}

export interface PropertyFilters {
    search?: string;
    status?: 'active' | 'deleted';
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface PropertyFormData {
    property_address: string;
    property_address_2: string | null;
    property_state: string | null;
    property_city: string | null;
    property_postal_code: string | null;
    property_country: string | null;
    property_latitude: string | null;
    property_longitude: string | null;
}

export interface PaginatedPropertyResponse {
    data: PropertyListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
