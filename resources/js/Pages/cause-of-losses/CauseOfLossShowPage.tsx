import { Head, Link, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { AlertTriangle, ArrowLeft, Pencil } from "lucide-react";
import type { CauseOfLoss } from "@/modules/cause-of-losses/types";
import AppLayout from "@/pages/layouts/AppLayout";

interface CauseOfLossShowPageProps extends PageProps {
    causeOfLoss: CauseOfLoss;
}

export default function CauseOfLossShowPage(): React.JSX.Element {
    const { causeOfLoss } = usePage<CauseOfLossShowPageProps>().props;

    return (
        <>
            <Head title={causeOfLoss.cause_loss_name} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href="/cause-of-losses"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                        >
                            <ArrowLeft size={16} />
                            <span>Back to cause of losses</span>
                        </Link>

                        {!causeOfLoss.deleted_at ? (
                            <Link
                                href={`/cause-of-losses/${causeOfLoss.uuid}/edit`}
                                className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                            >
                                <Pencil size={16} />
                                <span>Edit cause of loss</span>
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
                                    background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)",
                                    color: "var(--accent-primary)",
                                }}
                            >
                                <AlertTriangle size={24} />
                            </div>
                            <div className="space-y-1">
                                <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                    {causeOfLoss.cause_loss_name}
                                </h1>
                                <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                    Cause of loss details
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-6 px-6 py-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Severity
                                </p>
                                <p className="text-base font-semibold" style={{ color: "var(--text-primary)" }}>
                                    {causeOfLoss.severity}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Status
                                </p>
                                <p className="text-base font-semibold" style={{ color: causeOfLoss.deleted_at ? "var(--accent-error)" : "var(--accent-success)" }}>
                                    {causeOfLoss.deleted_at ? "Deleted" : "Active"}
                                </p>
                            </div>

                            <div className="space-y-2 md:col-span-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Description
                                </p>
                                <p className="text-sm leading-7" style={{ color: "var(--text-secondary)" }}>
                                    {causeOfLoss.description ?? "—"}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Created at
                                </p>
                                <p className="text-sm" style={{ color: "var(--text-secondary)" }}>
                                    {new Date(causeOfLoss.created_at).toLocaleString()}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Updated at
                                </p>
                                <p className="text-sm" style={{ color: "var(--text-secondary)" }}>
                                    {new Date(causeOfLoss.updated_at).toLocaleString()}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
