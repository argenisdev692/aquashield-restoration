export interface PortfolioImage {
    uuid: string;
    path: string;
    order: number | null;
}

export interface Portfolio {
    uuid: string;
    project_type_uuid: string | null;
    project_type_title: string | null;
    service_category_name: string | null;
    images: PortfolioImage[];
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface PortfolioListItem {
    uuid: string;
    project_type_uuid: string | null;
    project_type_title: string | null;
    service_category_name: string | null;
    image_count: number;
    first_image_path: string | null;
    created_at: string;
    deleted_at: string | null;
}

export interface PortfolioFilters {
    search?: string;
    status?: 'active' | 'deleted';
    project_type_uuid?: string;
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface PortfolioFormData {
    project_type_uuid: string | null;
}

export interface ProjectTypeOption {
    uuid: string;
    title: string;
    service_category_name: string | null;
}

export interface PaginatedPortfolioResponse {
    data: PortfolioListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
