import { Head, router } from "@inertiajs/react";
import { FileText } from "lucide-react";
import { useCreateServiceRequest } from "@/modules/service-requests/hooks/useServiceRequestMutations";
import type { ServiceRequestFormData } from "@/modules/service-requests/types";
import AppLayout from "@/pages/layouts/AppLayout";
import ServiceRequestForm from "./components/ServiceRequestForm";

export default function ServiceRequestCreatePage(): React.JSX.Element {
    const createServiceRequest = useCreateServiceRequest();

    async function handleSubmit(data: ServiceRequestFormData): Promise<void> {
        await createServiceRequest.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Service Request" />
            <AppLayout>
                <div className="mx-auto flex max-w-4xl flex-col gap-6">
                    <div className="flex items-start gap-4">
                        <div className="flex h-14 w-14 items-center justify-center rounded-2xl" style={{ background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)", color: "var(--accent-primary)" }}>
                            <FileText size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                Create service request
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Register a new service request in the intake pipeline.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <ServiceRequestForm
                            onSubmit={handleSubmit}
                            isSubmitting={createServiceRequest.isPending}
                            onCancel={() => router.visit("/service-requests")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
