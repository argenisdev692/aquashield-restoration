import { useMutation, useQueryClient } from "@tanstack/react-query";
import { router } from "@inertiajs/react";
import type { ProductFormData } from "../types";

export function useCreateProduct() {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string }, Error, ProductFormData>({
        mutationFn: async (data: ProductFormData) => {
            const response = await fetch("/products/data/admin", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            });
            if (!response.ok) throw new Error("Failed to create product");
            return response.json();
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["products"] });
            router.visit("/products");
        },
    });
}

export function useUpdateProduct() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: ProductFormData }>({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(`/products/data/admin/${uuid}`, {
                method: "PUT",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(data),
            });
            if (!response.ok) throw new Error("Failed to update product");
        },
        onSuccess: (_, variables) => {
            queryClient.invalidateQueries({ queryKey: ["products"] });
            router.visit("/products");
        },
    });
}

export function useDeleteProduct() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid: string) => {
            const response = await fetch(`/products/data/admin/${uuid}`, {
                method: "DELETE",
            });
            if (!response.ok) throw new Error("Failed to delete product");
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["products"] });
        },
    });
}

export function useRestoreProduct() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid: string) => {
            const response = await fetch(`/products/data/admin/${uuid}/restore`, {
                method: "PATCH",
            });
            if (!response.ok) throw new Error("Failed to restore product");
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ["products"] });
        },
    });
}
