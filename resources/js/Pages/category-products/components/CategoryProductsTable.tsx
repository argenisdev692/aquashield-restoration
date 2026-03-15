import * as React from "react";
import { Link } from "@inertiajs/react";
import {
    createColumnHelper,
    type OnChangeFn,
    type RowSelectionState,
} from "@tanstack/react-table";
import { Eye, Pencil, RotateCcw, Trash2 } from "lucide-react";
import { DataTable } from "@/shadcn/data-table";
import { formatDateShort } from "@/utils/dateFormatter";
import type { CategoryProductListItem } from "@/modules/category-products/types";

interface CategoryProductsTableProps {
    data: CategoryProductListItem[];
    isPending: boolean;
    onDeleteClick: (uuid: string, categoryProductName: string) => void;
    onRestoreClick: (uuid: string, categoryProductName: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<CategoryProductListItem>();

export default function CategoryProductsTable({
    data,
    isPending,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: CategoryProductsTableProps): React.JSX.Element {
    const columns = React.useMemo(
        () => [
            columnHelper.display({
                id: "select",
                header: ({ table }) => (
                    <input
                        type="checkbox"
                        checked={table.getIsAllPageRowsSelected()}
                        onChange={table.getToggleAllPageRowsSelectedHandler()}
                        aria-label="Select all"
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: "var(--accent-primary)" }}
                    />
                ),
                cell: ({ row }) => (
                    <input
                        type="checkbox"
                        checked={row.getIsSelected()}
                        onChange={row.getToggleSelectedHandler()}
                        aria-label="Select row"
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: "var(--accent-primary)" }}
                    />
                ),
            }),
            columnHelper.display({
                id: "category_product_name",
                header: "Category Product",
                cell: ({ row }) => (
                    <span className="font-semibold" style={{ color: "var(--text-primary)" }}>
                        {row.original.category_product_name}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "created_at",
                header: "Created",
                cell: ({ row }) => (
                    <span style={{ color: "var(--text-muted)" }}>
                        {formatDateShort(row.original.created_at)}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "actions",
                header: "Actions",
                cell: (info) => {
                    const categoryProduct = info.row.original;
                    const isDeleted = Boolean(categoryProduct.deleted_at);

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/category-products/${categoryProduct.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View category product"
                                aria-label="View category product"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/category-products/${categoryProduct.uuid}/edit`}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit category product"
                                        aria-label="Edit category product"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => onDeleteClick(categoryProduct.uuid, categoryProduct.category_product_name)}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete category product"
                                        aria-label="Delete category product"
                                        style={{
                                            color: "var(--accent-error)",
                                            border: "1px solid color-mix(in srgb, var(--accent-error) 30%, var(--border-default))",
                                            background: "color-mix(in srgb, var(--accent-error) 10%, transparent)",
                                        }}
                                    >
                                        <Trash2 size={14} />
                                    </button>
                                </>
                            ) : (
                                <button
                                    type="button"
                                    onClick={() => onRestoreClick(categoryProduct.uuid, categoryProduct.category_product_name)}
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Restore category product"
                                    aria-label="Restore category product"
                                    style={{
                                        color: "var(--accent-success)",
                                        border: "1px solid color-mix(in srgb, var(--accent-success) 30%, var(--border-default))",
                                        background: "color-mix(in srgb, var(--accent-success) 10%, transparent)",
                                    }}
                                >
                                    <RotateCcw size={14} />
                                </button>
                            )}
                        </div>
                    );
                },
            }),
        ],
        [onDeleteClick, onRestoreClick],
    );

    return (
        <DataTable
            columns={columns}
            data={data}
            isLoading={isPending}
            isError={false}
            noDataMessage="No category products found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
