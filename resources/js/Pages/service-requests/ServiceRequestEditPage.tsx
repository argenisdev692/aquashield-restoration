import { Head, router, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { FileText } from "lucide-react";
import { useUpdateServiceRequest } from "@/modules/service-requests/hooks/useServiceRequestMutations";
import type { ServiceRequest, ServiceRequestFormData } from "@/modules/service-requests/types";
import AppLayout from "@/pages/layouts/AppLayout";
import ServiceRequestForm from "./components/ServiceRequestForm";

interface ServiceRequestEditPageProps extends PageProps {
    serviceRequest: ServiceRequest;
}

export default function ServiceRequestEditPage(): React.JSX.Element {
    const { serviceRequest } = usePage<ServiceRequestEditPageProps>().props;
    const updateServiceRequest = useUpdateServiceRequest();

    async function handleSubmit(data: ServiceRequestFormData): Promise<void> {
        await updateServiceRequest.mutateAsync({
            uuid: serviceRequest.uuid,
            data,
        });
    }

    return (
        <>
            <Head title={`Edit ${serviceRequest.requested_service}`} />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex items-start gap-4">
                        <div className="flex h-14 w-14 items-center justify-center rounded-2xl" style={{ background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)", color: "var(--accent-primary)" }}>
                            <FileText size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                Edit service request
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Update the selected service request.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <ServiceRequestForm
                            initialData={{
                                requested_service: serviceRequest.requested_service,
                            }}
                            onSubmit={handleSubmit}
                            isSubmitting={updateServiceRequest.isPending}
                            onCancel={() => router.visit("/service-requests")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
