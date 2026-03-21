export interface ServiceCategory {
    uuid: string;
    category: string;
    type: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface ServiceCategoryListItem {
    uuid: string;
    category: string;
    type: string | null;
    created_at: string;
    deleted_at: string | null;
}

export interface ServiceCategoryFilters {
    search?: string;
    status?: 'active' | 'deleted';
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface ServiceCategoryFormData {
    category: string;
    type: string;
}

export interface PaginatedServiceCategoryResponse {
    data: ServiceCategoryListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
