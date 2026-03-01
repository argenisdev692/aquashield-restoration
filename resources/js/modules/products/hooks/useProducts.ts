import { useQuery, keepPreviousData } from "@tanstack/react-query";
import type { ProductListItem, ProductFilters } from "../types";

interface PaginatedResponse {
    data: ProductListItem[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
}

async function fetchProducts(filters: ProductFilters): Promise<PaginatedResponse> {
    const params = new URLSearchParams();
    
    if (filters.search) params.append("search", filters.search);
    if (filters.categoryId) params.append("categoryId", filters.categoryId);
    if (filters.status) params.append("status", filters.status);
    if (filters.dateFrom) params.append("dateFrom", filters.dateFrom);
    if (filters.dateTo) params.append("dateTo", filters.dateTo);
    if (filters.page) params.append("page", filters.page.toString());
    if (filters.perPage) params.append("perPage", filters.perPage.toString());

    const response = await fetch(`/products/data/admin?${params}`);
    if (!response.ok) throw new Error("Failed to fetch products");
    return response.json();
}

export function useProducts(filters: ProductFilters) {
    return useQuery<PaginatedResponse, Error>({
        queryKey: ["products", "list", filters],
        queryFn: () => fetchProducts(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
