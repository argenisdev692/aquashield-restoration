export interface Product {
    uuid: string;
    categoryId: string;
    categoryName: string;
    name: string;
    description: string;
    price: number;
    unit: string;
    orderPosition: number;
    createdAt: string;
    updatedAt: string;
    deletedAt: string | null;
}

export interface ProductListItem {
    uuid: string;
    categoryName: string;
    name: string;
    price: number;
    unit: string;
    orderPosition: number;
    createdAt: string;
    deletedAt: string | null;
}

export interface ProductFilters {
    search?: string;
    categoryId?: string;
    status?: string;
    dateFrom?: string;
    dateTo?: string;
    page?: number;
    perPage?: number;
}

export interface ProductFormData {
    categoryId: string;
    name: string;
    description: string;
    price: number;
    unit: string;
    orderPosition: number;
}

export interface Category {
    uuid: string;
    category_product_name: string;
}
