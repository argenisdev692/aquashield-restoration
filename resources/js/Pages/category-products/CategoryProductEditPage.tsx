import { Head, router, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { Tags } from "lucide-react";
import { useUpdateCategoryProduct } from "@/modules/category-products/hooks/useCategoryProductMutations";
import type { CategoryProduct, CategoryProductFormData } from "@/modules/category-products/types";
import AppLayout from "@/pages/layouts/AppLayout";
import CategoryProductForm from "./components/CategoryProductForm";

interface CategoryProductEditPageProps extends PageProps {
    categoryProduct: CategoryProduct;
}

export default function CategoryProductEditPage(): React.JSX.Element {
    const { categoryProduct } = usePage<CategoryProductEditPageProps>().props;
    const updateCategoryProduct = useUpdateCategoryProduct();

    async function handleSubmit(data: CategoryProductFormData): Promise<void> {
        await updateCategoryProduct.mutateAsync({
            uuid: categoryProduct.uuid,
            data,
        });
    }

    return (
        <>
            <Head title={`Edit ${categoryProduct.category_product_name}`} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex items-start gap-4">
                        <div
                            className="flex h-14 w-14 items-center justify-center rounded-2xl"
                            style={{
                                background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)",
                                color: "var(--accent-primary)",
                            }}
                        >
                            <Tags size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                Edit category product
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Update the current category product information.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <CategoryProductForm
                            initialData={{
                                category_product_name: categoryProduct.category_product_name,
                            }}
                            onSubmit={handleSubmit}
                            isSubmitting={updateCategoryProduct.isPending}
                            onCancel={() => router.visit("/category-products")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
