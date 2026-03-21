export interface ProjectType {
    uuid: string;
    title: string;
    description: string | null;
    status: 'active' | 'inactive';
    service_category_uuid: string;
    service_category_name: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface ProjectTypeListItem {
    uuid: string;
    title: string;
    description: string | null;
    status: 'active' | 'inactive';
    service_category_uuid: string;
    service_category_name: string | null;
    created_at: string;
    deleted_at: string | null;
}

export interface ProjectTypeFilters {
    search?: string;
    status?: 'active' | 'deleted';
    service_category_uuid?: string;
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface ProjectTypeFormData {
    title: string;
    description: string;
    status: 'active' | 'inactive';
    service_category_uuid: string;
}

export interface ServiceCategoryOption {
    uuid: string;
    category: string;
    type: string | null;
}

export interface PaginatedProjectTypeResponse {
    data: ProjectTypeListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
