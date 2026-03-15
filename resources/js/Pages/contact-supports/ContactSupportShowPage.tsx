import { Head, Link, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { ArrowLeft, MessageSquareText, Pencil } from "lucide-react";
import type { ContactSupport } from "@/modules/contact-supports/types";
import AppLayout from "@/pages/layouts/AppLayout";

interface ContactSupportShowPageProps extends PageProps {
    contactSupport: ContactSupport;
}

export default function ContactSupportShowPage(): React.JSX.Element {
    const { contactSupport } = usePage<ContactSupportShowPageProps>().props;

    return (
        <>
            <Head title={contactSupport.full_name} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link href="/contact-supports" className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold">
                            <ArrowLeft size={16} />
                            <span>Back to contact supports</span>
                        </Link>

                        {!contactSupport.deleted_at ? (
                            <Link href={`/contact-supports/${contactSupport.uuid}/edit`} className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold">
                                <Pencil size={16} />
                                <span>Edit contact support</span>
                            </Link>
                        ) : null}
                    </div>

                    <div className="card overflow-hidden p-0">
                        <div className="flex items-start gap-4 border-b px-6 py-6" style={{ borderColor: "var(--border-default)" }}>
                            <div className="flex h-14 w-14 items-center justify-center rounded-2xl" style={{ background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)", color: "var(--accent-primary)" }}>
                                <MessageSquareText size={24} />
                            </div>
                            <div className="space-y-1">
                                <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                    {contactSupport.full_name}
                                </h1>
                                <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                    Contact support details
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-6 px-6 py-6 md:grid-cols-2">
                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>Email</p>
                                <p className="text-base font-semibold" style={{ color: "var(--text-primary)" }}>{contactSupport.email}</p>
                            </div>
                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>Phone</p>
                                <p className="text-base font-semibold" style={{ color: "var(--text-primary)" }}>{contactSupport.phone ?? "—"}</p>
                            </div>
                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>Read status</p>
                                <p className="text-base font-semibold" style={{ color: contactSupport.readed ? "var(--accent-success)" : "var(--accent-warning)" }}>
                                    {contactSupport.readed ? "Read" : "Unread"}
                                </p>
                            </div>
                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>SMS consent</p>
                                <p className="text-base font-semibold" style={{ color: contactSupport.sms_consent ? "var(--accent-success)" : "var(--text-secondary)" }}>
                                    {contactSupport.sms_consent ? "Granted" : "Not granted"}
                                </p>
                            </div>
                            <div className="space-y-2 md:col-span-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>Message</p>
                                <p className="text-sm leading-7" style={{ color: "var(--text-secondary)" }}>{contactSupport.message}</p>
                            </div>
                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>Created at</p>
                                <p className="text-sm" style={{ color: "var(--text-secondary)" }}>{new Date(contactSupport.created_at).toLocaleString()}</p>
                            </div>
                            <div className="space-y-2">
                                <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>Updated at</p>
                                <p className="text-sm" style={{ color: "var(--text-secondary)" }}>{new Date(contactSupport.updated_at).toLocaleString()}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
