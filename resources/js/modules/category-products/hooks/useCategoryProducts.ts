import { keepPreviousData, useQuery } from "@tanstack/react-query";
import type {
    CategoryProductFilters,
    PaginatedCategoryProductResponse,
} from "../types";

async function fetchCategoryProducts(
    filters: CategoryProductFilters,
): Promise<PaginatedCategoryProductResponse> {
    const params = new URLSearchParams();

    if (filters.search) params.append("search", filters.search);
    if (filters.status) params.append("status", filters.status);
    if (filters.date_from) params.append("date_from", filters.date_from);
    if (filters.date_to) params.append("date_to", filters.date_to);
    if (filters.page) params.append("page", filters.page.toString());
    if (filters.per_page) params.append("per_page", filters.per_page.toString());

    const response = await fetch(`/category-products/data/admin?${params.toString()}`);

    if (!response.ok) {
        throw new Error("Failed to fetch category products.");
    }

    return response.json() as Promise<PaginatedCategoryProductResponse>;
}

export function useCategoryProducts(filters: CategoryProductFilters) {
    return useQuery<PaginatedCategoryProductResponse, Error>({
        queryKey: ["category-products", "list", filters],
        queryFn: () => fetchCategoryProducts(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
