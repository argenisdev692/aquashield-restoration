import * as React from "react";
import { Head, Link, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { ArrowLeft, CircleDot, Pencil } from "lucide-react";
import type { ClaimStatus } from "@/modules/claim-statuses/types";
import AppLayout from "@/pages/layouts/AppLayout";

interface ClaimStatusShowPageProps extends PageProps {
    claimStatus: ClaimStatus;
}

export default function ClaimStatusShowPage(): React.JSX.Element {
    const { claimStatus } = usePage<ClaimStatusShowPageProps>().props;

    return (
        <>
            <Head title={claimStatus.claim_status_name} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href="/claim-statuses"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                        >
                            <ArrowLeft size={16} />
                            <span>Back to claim statuses</span>
                        </Link>

                        {!claimStatus.deleted_at ? (
                            <Link
                                href={`/claim-statuses/${claimStatus.uuid}/edit`}
                                className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                            >
                                <Pencil size={16} />
                                <span>Edit claim status</span>
                            </Link>
                        ) : null}
                    </div>

                    <div className="card overflow-hidden p-0">
                        <div
                            className="flex items-start gap-4 border-b px-6 py-6"
                            style={{ borderColor: "var(--border-default)" }}
                        >
                            <div
                                className="flex h-14 w-14 items-center justify-center rounded-2xl"
                                style={{
                                    background: claimStatus.background_color
                                        ? `color-mix(in srgb, ${claimStatus.background_color} 20%, transparent)`
                                        : "color-mix(in srgb, var(--accent-primary) 12%, transparent)",
                                    color: claimStatus.background_color ?? "var(--accent-primary)",
                                }}
                            >
                                <CircleDot size={24} />
                            </div>
                            <div className="space-y-1">
                                <h1
                                    className="text-3xl font-extrabold"
                                    style={{ color: "var(--text-primary)" }}
                                >
                                    {claimStatus.claim_status_name}
                                </h1>
                                <p
                                    className="text-sm"
                                    style={{ color: "var(--text-muted)" }}
                                >
                                    Claim status details
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-6 px-6 py-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: "var(--text-disabled)" }}
                                >
                                    Background Color
                                </p>
                                {claimStatus.background_color ? (
                                    <div className="flex items-center gap-2">
                                        <span
                                            className="inline-block h-6 w-6 rounded-lg border"
                                            style={{
                                                backgroundColor: claimStatus.background_color,
                                                borderColor: "var(--border-default)",
                                            }}
                                        />
                                        <span
                                            className="font-mono text-sm font-semibold"
                                            style={{ color: "var(--text-primary)" }}
                                        >
                                            {claimStatus.background_color}
                                        </span>
                                    </div>
                                ) : (
                                    <p
                                        className="text-base font-semibold"
                                        style={{ color: "var(--text-disabled)" }}
                                    >
                                        —
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: "var(--text-disabled)" }}
                                >
                                    Status
                                </p>
                                <p
                                    className="text-base font-semibold"
                                    style={{
                                        color: claimStatus.deleted_at
                                            ? "var(--accent-error)"
                                            : "var(--accent-success)",
                                    }}
                                >
                                    {claimStatus.deleted_at ? "Deleted" : "Active"}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: "var(--text-disabled)" }}
                                >
                                    Preview
                                </p>
                                <div
                                    className="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold"
                                    style={{
                                        background: claimStatus.background_color ?? "var(--bg-surface)",
                                        color: "#ffffff",
                                        border: "1px solid var(--border-default)",
                                    }}
                                >
                                    {claimStatus.claim_status_name}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: "var(--text-disabled)" }}
                                >
                                    Created at
                                </p>
                                <p
                                    className="text-sm"
                                    style={{ color: "var(--text-secondary)" }}
                                >
                                    {new Date(claimStatus.created_at).toLocaleString()}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: "var(--text-disabled)" }}
                                >
                                    Updated at
                                </p>
                                <p
                                    className="text-sm"
                                    style={{ color: "var(--text-secondary)" }}
                                >
                                    {new Date(claimStatus.updated_at).toLocaleString()}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
