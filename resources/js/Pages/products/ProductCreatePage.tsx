import { Head, router, usePage } from "@inertiajs/react";
import type { PageProps } from '@inertiajs/core';
import AppLayout from "@/pages/layouts/AppLayout";
import { useCreateProduct } from "@/modules/products/hooks/useProductMutations";
import ProductForm from "./components/ProductForm";
import { PackagePlus } from "lucide-react";
import type { Category, ProductFormData } from "@/modules/products/types";

interface ProductCreatePageProps extends PageProps {
    categories: Category[];
}

export default function ProductCreatePage(): React.JSX.Element {
    const { categories } = usePage<ProductCreatePageProps>().props;
    const createProduct = useCreateProduct();

    const handleSubmit = async (data: ProductFormData): Promise<void> => {
        await createProduct.mutateAsync(data);
    };

    return (
        <>
            <Head title="Create Product" />
            <AppLayout>
                <div className="max-w-4xl mx-auto space-y-8 animate-in fade-in duration-500">
                    <div className="flex flex-col gap-2">
                        <div className="flex items-center gap-3">
                            <div
                                className="p-3 rounded-2xl shadow-sm"
                                style={{
                                    background: "color-mix(in srgb, var(--accent-primary) 10%, transparent)",
                                    color: "var(--accent-primary)",
                                }}
                            >
                                <PackagePlus size={24} />
                            </div>
                            <h1
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: "var(--text-primary)" }}
                            >
                                Create New Product
                            </h1>
                        </div>
                        <p
                            className="text-sm font-medium ml-14"
                            style={{ color: "var(--text-muted)" }}
                        >
                            Add a new product to your catalog with pricing and details.
                        </p>
                    </div>

                    <div
                        className="rounded-3xl shadow-2xl overflow-hidden transition-all"
                        style={{
                            border: "1px solid var(--border-default)",
                            background: "var(--bg-card)",
                        }}
                    >
                        <ProductForm
                            categories={categories}
                            onSubmit={handleSubmit}
                            isSubmitting={createProduct.isPending}
                            onCancel={() => router.visit("/products")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
