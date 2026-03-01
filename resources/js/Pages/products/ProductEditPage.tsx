import { Head, router, usePage } from "@inertiajs/react";
import AppLayout from "@/pages/layouts/AppLayout";
import { useUpdateProduct } from "@/modules/products/hooks/useProductMutations";
import ProductForm from "./components/ProductForm";
import { PackageOpen } from "lucide-react";
import type { Product, Category } from "@/modules/products/types";

interface ProductEditPageProps {
    product: Product;
    categories: Category[];
}

export default function ProductEditPage(): React.JSX.Element {
    const { product, categories } = usePage<ProductEditPageProps>().props;
    const updateProduct = useUpdateProduct();

    const handleSubmit = async (data: any) => {
        await updateProduct.mutateAsync({ uuid: product.uuid, data });
    };

    return (
        <>
            <Head title={`Edit ${product.name}`} />
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
                                <PackageOpen size={24} />
                            </div>
                            <h1
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: "var(--text-primary)" }}
                            >
                                Edit Product
                            </h1>
                        </div>
                        <p
                            className="text-sm font-medium ml-14"
                            style={{ color: "var(--text-muted)" }}
                        >
                            Update product information and pricing details.
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
                            initialData={{
                                categoryId: product.categoryId,
                                name: product.name,
                                description: product.description,
                                price: product.price,
                                unit: product.unit,
                                orderPosition: product.orderPosition,
                            }}
                            categories={categories}
                            onSubmit={handleSubmit}
                            isSubmitting={updateProduct.isPending}
                            onCancel={() => router.visit("/products")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
