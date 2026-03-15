import { Head, router } from "@inertiajs/react";
import { CalendarRange } from "lucide-react";
import { useCreateAppointment } from "@/modules/appointments/hooks/useAppointmentMutations";
import type { AppointmentFormData } from "@/modules/appointments/types";
import AppLayout from "@/pages/layouts/AppLayout";
import AppointmentForm from "./components/AppointmentForm";

export default function AppointmentCreatePage(): React.JSX.Element {
    const createAppointment = useCreateAppointment();

    async function handleSubmit(data: AppointmentFormData): Promise<void> {
        await createAppointment.mutateAsync(data);
    }

    return (
        <>
            <Head title="Create Appointment" />
            <AppLayout>
                <div className="mx-auto flex max-w-6xl flex-col gap-6">
                    <div className="flex items-start gap-4">
                        <div className="flex h-14 w-14 items-center justify-center rounded-2xl" style={{ background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)", color: "var(--accent-primary)" }}>
                            <CalendarRange size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                Create appointment
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Register a new appointment lead in the CRM pipeline.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <AppointmentForm
                            onSubmit={handleSubmit}
                            isSubmitting={createAppointment.isPending}
                            onCancel={() => router.visit("/appointments")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
