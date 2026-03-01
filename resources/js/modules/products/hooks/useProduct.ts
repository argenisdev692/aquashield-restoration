import { useQuery } from "@tanstack/react-query";
import type { Product } from "../types";

async function fetchProduct(uuid: string): Promise<Product> {
    const response = await fetch(`/products/data/admin/${uuid}`);
    if (!response.ok) throw new Error("Failed to fetch product");
    return response.json();
}

export function useProduct(uuid: string) {
    return useQuery<Product, Error>({
        queryKey: ["products", uuid],
        queryFn: () => fetchProduct(uuid),
        staleTime: 1000 * 60 * 5,
    });
}
