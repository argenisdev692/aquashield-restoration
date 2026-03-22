import { Head, Link, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { ArrowLeft, FileText, Pencil } from "lucide-react";
import type { ServiceRequest } from "@/modules/service-requests/types";
import AppLayout from "@/pages/layouts/AppLayout";

interface ServiceRequestShowPageProps extends PageProps {
    serviceRequest: ServiceRequest;
}

export default function ServiceRequestShowPage(): React.JSX.Element {
    const { serviceRequest } = usePage<ServiceRequestShowPageProps>().props;

    return (
        <>
            <Head title={serviceRequest.requested_service} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link href="/service-requests" className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold">
                            <ArrowLeft size={16} />
                            <span>Back to service requests</span>
                        </Link>

                        {!serviceRequest.deleted_at ? (
                            <Link href={`/service-requests/${serviceRequest.uuid}/edit`} className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold">
                                <Pencil size={16} />
                                <span>Edit service request</span>
                            </Link>
                        ) : null}
                    </div>

                    <div className="card overflow-hidden p-0">
                        <div className="flex items-start gap-4 border-b px-6 py-6" style={{ borderColor: "var(--border-default)" }}>
                            <div className="flex h-14 w-14 items-center justify-center rounded-2xl" style={{ background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)", color: "var(--accent-primary)" }}>
                                <FileText size={24} />
                            </div>
                            <div className="space-y-1">
                                <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                    {serviceRequest.requested_service}
                                </h1>
                                <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                    Service request details
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-6 px-6 py-6 md:grid-cols-2">
                            <Info label="Requested service" value={serviceRequest.requested_service} />
                            <Info label="Status" value={serviceRequest.deleted_at ? "Deleted" : "Active"} />
                            <Info label="Created at" value={new Date(serviceRequest.created_at).toLocaleString()} />
                            <Info label="Updated at" value={new Date(serviceRequest.updated_at).toLocaleString()} />
                        </div>
                    </div>
                </div>
            </AppLayout>
        </>
    );
}

function Info({ label, value }: { label: string; value: string }): React.JSX.Element {
    return (
        <div className="space-y-2">
            <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>{label}</p>
            <p className="text-sm font-semibold" style={{ color: "var(--text-primary)" }}>{value}</p>
        </div>
    );
}
