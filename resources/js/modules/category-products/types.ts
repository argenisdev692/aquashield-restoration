export interface CategoryProduct {
    uuid: string;
    category_product_name: string;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface CategoryProductListItem {
    uuid: string;
    category_product_name: string;
    created_at: string;
    deleted_at: string | null;
}

export interface CategoryProductFilters {
    search?: string;
    status?: "active" | "deleted";
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface CategoryProductFormData {
    category_product_name: string;
}

export interface PaginatedCategoryProductResponse {
    data: CategoryProductListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
