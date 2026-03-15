import { Head, router } from "@inertiajs/react";
import { Tags } from "lucide-react";
import { useCreateCategoryProduct } from "@/modules/category-products/hooks/useCategoryProductMutations";
import type { CategoryProductFormData } from "@/modules/category-products/types";
import AppLayout from "@/pages/layouts/AppLayout";
import CategoryProductForm from "./components/CategoryProductForm";

export default function CategoryProductCreatePage(): React.JSX.Element {
    const createCategoryProduct = useCreateCategoryProduct();

    async function handleSubmit(data: CategoryProductFormData): Promise<void> {
        await createCategoryProduct.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Category Product" />
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
                                Create category product
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Add a new category product to your reference catalog.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <CategoryProductForm
                            onSubmit={handleSubmit}
                            isSubmitting={createCategoryProduct.isPending}
                            onCancel={() => router.visit("/category-products")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
