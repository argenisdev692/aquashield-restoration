import * as React from "react";
import { Link, Head, useRemember, router } from "@inertiajs/react";
import AppLayout from "@/pages/layouts/AppLayout";
import { useProducts } from "@/modules/products/hooks/useProducts";
import { useDeleteProduct, useRestoreProduct } from "@/modules/products/hooks/useProductMutations";
import ProductsTable from "./components/ProductsTable";
import { DeleteConfirmModal } from "@/shadcn/DeleteConfirmModal";
import { RestoreConfirmModal } from "@/shadcn/RestoreConfirmModal";
import { DataTableBulkActions } from "@/shadcn/DataTableBulkActions";
import { ExportButton } from "@/common/export/ExportButton";
import { CrudFilterBar } from "@/common/filters/CrudFilterBar";
import type { ProductFilters } from "@/modules/products/types";
import type { RowSelectionState } from "@tanstack/react-table";
import { ChevronLeft, ChevronRight, Plus } from "lucide-react";

export default function ProductsIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<ProductFilters>(
        { page: 1, perPage: 15 },
        "products-filters"
    );
    const [search, setSearch] = React.useState<string>(filters.search || "");
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{
        uuid: string;
        name: string;
    } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{
        uuid: string;
        name: string;
    } | null>(null);

    const [, startSearchTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending } = useProducts(filters);
    const products = data?.data ?? [];
    const meta = data?.meta ?? {
        currentPage: 1,
        lastPage: 1,
        perPage: 15,
        total: 0,
    };

    const deleteProduct = useDeleteProduct();
    const restoreProduct = useRestoreProduct();

    const handleSearchChange = (value: string): void => {
        setSearch(value);
        startSearchTransition(() => {
            setFilters((prev) => ({
                ...prev,
                search: value || undefined,
                page: 1,
            }));
        });
    };

    const handleDeleteClick = (uuid: string, name: string) => {
        setPendingDelete({ uuid, name });
    };

    const handleRestoreClick = (uuid: string, name: string) => {
        setPendingRestore({ uuid, name });
    };

    const handleConfirmDelete = async () => {
        if (!pendingDelete) return;
        await deleteProduct.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    };

    const handleConfirmRestore = async () => {
        if (!pendingRestore) return;
        await restoreProduct.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    };

    const handleBulkDelete = async () => {
        const selectedUuids = Object.keys(rowSelection)
            .filter((key) => rowSelection[key])
            .map((index) => products[parseInt(index)]?.uuid)
            .filter(Boolean);

        if (selectedUuids.length === 0) return;

        router.post(
            "/products/data/admin/bulk-delete",
            { uuids: selectedUuids },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setRowSelection({});
                },
                onError: (errors) => {
                    console.error("Bulk delete failed:", errors);
                },
            }
        );
    };

    const handleExport = (format: "excel" | "pdf") => {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append("search", filters.search);
            if (filters.categoryId) params.append("categoryId", filters.categoryId);
            if (filters.status) params.append("status", filters.status);
            if (filters.dateFrom) params.append("dateFrom", filters.dateFrom);
            if (filters.dateTo) params.append("dateTo", filters.dateTo);
            params.append("format", format);
            window.open(`/products/data/admin/export?${params.toString()}`, "_blank");
        });
    };

    const goToPage = (page: number) => {
        setFilters((prev) => ({ ...prev, page }));
    };

    return (
        <>
            <Head title="Products" />
            <AppLayout>
                <div className="flex flex-col gap-6 animate-in fade-in duration-500">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 className="text-3xl font-extrabold tracking-tight text-(--text-primary)">
                                Products
                            </h1>
                            <p className="text-sm mt-1 text-(--text-muted) font-medium">
                                Manage your product catalog — <span className="text-(--accent-primary)">{meta.total} products</span> found
                            </p>
                        </div>
                        <Link
                            href="/products/create"
                            className="flex items-center gap-2 rounded-xl px-6 py-2.5 font-bold shadow-lg transition-all hover:scale-[1.03] active:scale-[0.97]"
                            style={{
                                background: "var(--accent-primary)",
                                color: "var(--color-white)",
                                boxShadow: "0 10px 24px color-mix(in srgb, var(--accent-primary) 24%, transparent)",
                            }}
                        >
                            <Plus size={18} />
                            <span>New Product</span>
                        </Link>
                    </div>

                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search products..."
                        searchAriaLabel="Search products"
                        statusValue={filters.status ?? ""}
                        onStatusChange={(value) => {
                            startSearchTransition(() => {
                                setFilters((p) => ({
                                    ...p,
                                    status: value === "" ? undefined : value,
                                    page: 1,
                                }));
                            });
                        }}
                        dateFrom={filters.dateFrom}
                        dateTo={filters.dateTo}
                        onDateRangeChange={(range) => {
                            startSearchTransition(() => {
                                setFilters((p) => ({
                                    ...p,
                                    dateFrom: range.dateFrom,
                                    dateTo: range.dateTo,
                                    page: 1,
                                }));
                            });
                        }}
                        actions={<ExportButton onExport={handleExport} isExporting={isPendingExport} />}
                    />

                    <DataTableBulkActions
                        count={Object.keys(rowSelection).length}
                        onDelete={handleBulkDelete}
                        isDeleting={deleteProduct.isPending}
                    />

                    <div className="overflow-hidden rounded-2xl border border-(--border-default) shadow-2xl bg-(--bg-card)">
                        <ProductsTable
                            data={products}
                            isPending={isPending}
                            onDeleteClick={handleDeleteClick}
                            onRestoreClick={handleRestoreClick}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                        />

                        {meta.lastPage > 1 && (
                            <div className="flex items-center justify-between px-6 py-4 border-t border-(--border-subtle) bg-white/5">
                                <span className="text-xs font-semibold text-(--text-disabled) uppercase tracking-wider">
                                    Page {meta.currentPage} / {meta.lastPage} • {meta.total} Total
                                </span>
                                <div className="flex items-center gap-2">
                                    <button
                                        onClick={() =>
                                            goToPage(meta.currentPage - 1)
                                        }
                                        disabled={meta.currentPage === 1}
                                        className="h-9 w-9 flex items-center justify-center rounded-xl bg-(--bg-app) border border-(--border-default) hover:bg-(--bg-hover) disabled:opacity-30 transition-all font-bold"
                                    >
                                        <ChevronLeft size={18} />
                                    </button>
                                    <button
                                        onClick={() =>
                                            goToPage(meta.currentPage + 1)
                                        }
                                        disabled={
                                            meta.currentPage === meta.lastPage
                                        }
                                        className="h-9 w-9 flex items-center justify-center rounded-xl bg-(--bg-app) border border-(--border-default) hover:bg-(--bg-hover) disabled:opacity-30 transition-all font-bold"
                                    >
                                        <ChevronRight size={18} />
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <DeleteConfirmModal
                    open={!!pendingDelete}
                    entityLabel={pendingDelete?.name ?? ""}
                    onConfirm={handleConfirmDelete}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteProduct.isPending}
                />

                <RestoreConfirmModal
                    isOpen={!!pendingRestore}
                    entityLabel="product"
                    entityName={pendingRestore?.name}
                    onConfirm={handleConfirmRestore}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreProduct.isPending}
                />
            </AppLayout>
        </>
    );
}
