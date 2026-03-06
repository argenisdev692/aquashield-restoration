import { Head, Link, usePage } from "@inertiajs/react";
import type { PageProps } from '@inertiajs/core';
import AppLayout from "@/pages/layouts/AppLayout";
import { Package, ArrowLeft, Pencil, Tag, DollarSign, Box, Hash } from "lucide-react";
import type { Product } from "@/modules/products/types";

interface ProductShowPageProps extends PageProps {
    product: Product;
}

export default function ProductShowPage(): React.JSX.Element {
    const { product } = usePage<ProductShowPageProps>().props;

    return (
        <>
            <Head title={product.name} />
            <AppLayout>
                <div className="max-w-4xl mx-auto space-y-8 animate-in fade-in duration-500">
                    <div className="flex items-center justify-between">
                        <Link
                            href="/products"
                            className="flex items-center gap-2 px-4 py-2 rounded-xl font-medium transition-all hover:scale-[1.02]"
                            style={{
                                background: "var(--bg-subtle)",
                                color: "var(--text-primary)",
                                border: "1px solid var(--border-default)",
                            }}
                        >
                            <ArrowLeft size={16} />
                            <span>Back to Products</span>
                        </Link>

                        <Link
                            href={`/products/${product.uuid}/edit`}
                            className="flex items-center gap-2 px-4 py-2 rounded-xl font-bold text-white transition-all hover:scale-[1.02]"
                            style={{ background: "var(--accent-primary)" }}
                        >
                            <Pencil size={16} />
                            <span>Edit Product</span>
                        </Link>
                    </div>

                    <div
                        className="rounded-3xl shadow-2xl overflow-hidden"
                        style={{
                            border: "1px solid var(--border-default)",
                            background: "var(--bg-card)",
                        }}
                    >
                        <div
                            className="px-8 py-6 border-b"
                            style={{ borderColor: "var(--border-default)" }}
                        >
                            <div className="flex items-center gap-4">
                                <div
                                    className="p-4 rounded-2xl"
                                    style={{
                                        background: "color-mix(in srgb, var(--accent-primary) 10%, transparent)",
                                        color: "var(--accent-primary)",
                                    }}
                                >
                                    <Package size={32} />
                                </div>
                                <div>
                                    <h1
                                        className="text-3xl font-extrabold tracking-tight"
                                        style={{ color: "var(--text-primary)" }}
                                    >
                                        {product.name}
                                    </h1>
                                    <p
                                        className="text-sm mt-1"
                                        style={{ color: "var(--text-muted)" }}
                                    >
                                        Product Details
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="p-8 space-y-6">
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-2">
                                    <div className="flex items-center gap-2">
                                        <Tag size={16} style={{ color: "var(--text-muted)" }} />
                                        <label
                                            className="text-sm font-semibold"
                                            style={{ color: "var(--text-muted)" }}
                                        >
                                            Category
                                        </label>
                                    </div>
                                    <p
                                        className="text-base font-medium"
                                        style={{ color: "var(--text-primary)" }}
                                    >
                                        {product.categoryName}
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <div className="flex items-center gap-2">
                                        <DollarSign size={16} style={{ color: "var(--text-muted)" }} />
                                        <label
                                            className="text-sm font-semibold"
                                            style={{ color: "var(--text-muted)" }}
                                        >
                                            Price
                                        </label>
                                    </div>
                                    <p
                                        className="text-base font-medium"
                                        style={{ color: "var(--text-primary)" }}
                                    >
                                        ${product.price.toFixed(2)}
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <div className="flex items-center gap-2">
                                        <Box size={16} style={{ color: "var(--text-muted)" }} />
                                        <label
                                            className="text-sm font-semibold"
                                            style={{ color: "var(--text-muted)" }}
                                        >
                                            Unit
                                        </label>
                                    </div>
                                    <p
                                        className="text-base font-medium"
                                        style={{ color: "var(--text-primary)" }}
                                    >
                                        {product.unit}
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <div className="flex items-center gap-2">
                                        <Hash size={16} style={{ color: "var(--text-muted)" }} />
                                        <label
                                            className="text-sm font-semibold"
                                            style={{ color: "var(--text-muted)" }}
                                        >
                                            Order Position
                                        </label>
                                    </div>
                                    <p
                                        className="text-base font-medium"
                                        style={{ color: "var(--text-primary)" }}
                                    >
                                        {product.orderPosition}
                                    </p>
                                </div>
                            </div>

                            <div className="space-y-2">
                                <label
                                    className="text-sm font-semibold"
                                    style={{ color: "var(--text-muted)" }}
                                >
                                    Description
                                </label>
                                <p
                                    className="text-base leading-relaxed"
                                    style={{ color: "var(--text-primary)" }}
                                >
                                    {product.description}
                                </p>
                            </div>

                            <div
                                className="pt-6 border-t grid grid-cols-1 md:grid-cols-2 gap-4"
                                style={{ borderColor: "var(--border-default)" }}
                            >
                                <div className="space-y-1">
                                    <p
                                        className="text-xs font-semibold"
                                        style={{ color: "var(--text-muted)" }}
                                    >
                                        Created At
                                    </p>
                                    <p
                                        className="text-sm"
                                        style={{ color: "var(--text-secondary)" }}
                                    >
                                        {new Date(product.createdAt).toLocaleString()}
                                    </p>
                                </div>
                                <div className="space-y-1">
                                    <p
                                        className="text-xs font-semibold"
                                        style={{ color: "var(--text-muted)" }}
                                    >
                                        Last Updated
                                    </p>
                                    <p
                                        className="text-sm"
                                        style={{ color: "var(--text-secondary)" }}
                                    >
                                        {new Date(product.updatedAt).toLocaleString()}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
