import * as React from "react";
import { Head, Link, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { ArrowLeft, Mail, Pencil } from "lucide-react";
import { PermissionGuard } from "@/modules/auth/components/PermissionGuard";
import type { EmailData } from "@/modules/email-data/types";
import AppLayout from "@/pages/layouts/AppLayout";

interface EmailDataShowPageProps extends PageProps {
    emailData: EmailData;
}

export default function EmailDataShowPage(): React.JSX.Element {
    const { emailData } = usePage<EmailDataShowPageProps>().props;

    return (
        <>
            <Head title={emailData.email} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link
                            href="/email-data"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                        >
                            <ArrowLeft size={16} />
                            <span>Back to email data</span>
                        </Link>

                        {!emailData.deleted_at ? (
                            <PermissionGuard permissions={["UPDATE_EMAIL_DATA"]}>
                                <Link
                                    href={`/email-data/${emailData.uuid}/edit`}
                                    className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                                >
                                    <Pencil size={16} />
                                    <span>Edit email data</span>
                                </Link>
                            </PermissionGuard>
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
                                <Mail size={24} />
                            </div>
                            <div className="space-y-1">
                                <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                    {emailData.email}
                                </h1>
                                <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                    Email data details
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-6 px-6 py-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Type
                                </p>
                                <p className="text-base font-semibold" style={{ color: "var(--text-primary)" }}>
                                    {emailData.type ?? "—"}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Phone
                                </p>
                                <p className="text-base font-semibold" style={{ color: "var(--text-primary)" }}>
                                    {emailData.phone ?? "—"}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Owner user id
                                </p>
                                <p className="text-base font-semibold" style={{ color: "var(--text-primary)" }}>
                                    {emailData.user_id}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Status
                                </p>
                                <p className="text-base font-semibold" style={{ color: emailData.deleted_at ? "var(--accent-error)" : "var(--accent-success)" }}>
                                    {emailData.deleted_at ? "Deleted" : "Active"}
                                </p>
                            </div>

                            <div className="space-y-2 md:col-span-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Description
                                </p>
                                <p className="text-sm leading-7" style={{ color: "var(--text-secondary)" }}>
                                    {emailData.description ?? "—"}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Created at
                                </p>
                                <p className="text-sm" style={{ color: "var(--text-secondary)" }}>
                                    {new Date(emailData.created_at).toLocaleString()}
                                </p>
                            </div>

                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Updated at
                                </p>
                                <p className="text-sm" style={{ color: "var(--text-secondary)" }}>
                                    {new Date(emailData.updated_at).toLocaleString()}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
