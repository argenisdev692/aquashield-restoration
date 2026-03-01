import * as React from "react";
import { createColumnHelper, type ColumnDef, type RowSelectionState, type OnChangeFn } from "@tanstack/react-table";
import { Link } from "@inertiajs/react";
import { DataTable } from "@/shadcn/data-table";
import type { ProductListItem } from "@/modules/products/types";
import { formatDateShort } from "@/utils/dateFormatter";
import { Eye, Pencil, Trash2, RotateCcw } from "lucide-react";

interface ProductsTableProps {
    data: ProductListItem[];
    isPending: boolean;
    onDeleteClick: (uuid: string, name: string) => void;
    onRestoreClick: (uuid: string, name: string) => void;
    rowSelection?: RowSelectionState;
    onRowSelectionChange?: OnChangeFn<RowSelectionState>;
}

export default function ProductsTable({
    data,
    isPending,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: ProductsTableProps): React.JSX.Element {
    const columnHelper = createColumnHelper<ProductListItem>();

    const columns = React.useMemo<ColumnDef<ProductListItem, any>[]>(
        () => [
            columnHelper.display({
                id: "select",
                header: ({ table }) => (
                    <input
                        type="checkbox"
                        checked={table.getIsAllPageRowsSelected()}
                        onChange={table.getToggleAllPageRowsSelectedHandler()}
                        aria-label="Select all"
                        className="h-4 w-4 rounded border-gray-300 accent-(--accent-primary) cursor-pointer"
                    />
                ),
                cell: ({ row }) => (
                    <input
                        type="checkbox"
                        checked={row.getIsSelected()}
                        onChange={row.getToggleSelectedHandler()}
                        aria-label="Select row"
                        className="h-4 w-4 rounded border-gray-300 accent-(--accent-primary) cursor-pointer"
                    />
                ),
            }),
            columnHelper.accessor("name", {
                header: "Product Name",
                cell: (info) => (
                    <span
                        className="font-semibold"
                        style={{ color: "var(--text-primary)" }}
                    >
                        {info.getValue()}
                    </span>
                ),
            }),
            columnHelper.accessor("categoryName", {
                header: "Category",
                cell: (info) => info.getValue() || "—",
            }),
            columnHelper.accessor("price", {
                header: "Price",
                cell: (info) => `$${info.getValue().toFixed(2)}`,
            }),
            columnHelper.accessor("unit", {
                header: "Unit",
                cell: (info) => info.getValue(),
            }),
            columnHelper.accessor("orderPosition", {
                header: "Order",
                cell: (info) => info.getValue(),
            }),
            columnHelper.accessor("createdAt", {
                header: "Created",
                cell: (info) => (
                    <span className="text-sm" style={{ color: "var(--text-muted)" }}>
                        {formatDateShort(info.getValue())}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "actions",
                header: "Actions",
                cell: (info) => {
                    const product = info.row.original;
                    const isDeleted = !!product.deletedAt;

                    return (
                        <div className="flex items-center justify-center gap-1.5">
                            <Link
                                href={`/products/${product.uuid}`}
                                className="btn-action btn-action-view"
                                title="View Product"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/products/${product.uuid}/edit`}
                                        className="btn-action btn-action-edit"
                                        title="Edit Product"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        onClick={() =>
                                            onDeleteClick(
                                                product.uuid,
                                                product.name
                                            )
                                        }
                                        className="btn-action btn-action-delete"
                                        title="Delete Product"
                                    >
                                        <Trash2 size={14} />
                                    </button>
                                </>
                            ) : (
                                <button
                                    onClick={() =>
                                        onRestoreClick(
                                            product.uuid,
                                            product.name
                                        )
                                    }
                                    className="btn-action btn-action-restore"
                                    title="Restore Product"
                                >
                                    <RotateCcw size={14} />
                                </button>
                            )}
                        </div>
                    );
                },
            }),
        ],
        [columnHelper, onDeleteClick, onRestoreClick]
    );

    return (
        <DataTable
            columns={columns}
            data={data}
            isLoading={isPending}
            isError={false}
            noDataMessage="No products found"
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
