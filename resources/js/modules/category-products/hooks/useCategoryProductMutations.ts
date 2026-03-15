import { router } from "@inertiajs/react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import type { CategoryProductFormData } from "../types";

export function useCreateCategoryProduct() {
    const queryClient = useQueryClient();

    return useMutation<
        { uuid: string; message: string },
        Error,
        CategoryProductFormData
    >({
        mutationFn: async (data) => {
            const response = await fetch("/category-products/data/admin", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error("Failed to create category product.");
            }

            return response.json() as Promise<{ uuid: string; message: string }>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["category-products"] });
            router.visit("/category-products");
        },
    });
}

export function useUpdateCategoryProduct() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: CategoryProductFormData }>({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(`/category-products/data/admin/${uuid}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error("Failed to update category product.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["category-products"] });
            router.visit("/category-products");
        },
    });
}

export function useDeleteCategoryProduct() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/category-products/data/admin/${uuid}`, {
                method: "DELETE",
            });

            if (!response.ok) {
                throw new Error("Failed to delete category product.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["category-products"] });
        },
    });
}

export function useRestoreCategoryProduct() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(
                `/category-products/data/admin/${uuid}/restore`,
                {
                    method: "PATCH",
                },
            );

            if (!response.ok) {
                throw new Error("Failed to restore category product.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["category-products"] });
        },
    });
}

export function useBulkDeleteCategoryProducts() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids) => {
            const response = await fetch("/category-products/data/admin/bulk-delete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ uuids }),
            });

            if (!response.ok) {
                throw new Error("Failed to bulk delete category products.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["category-products"] });
        },
    });
}
