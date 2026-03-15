import { Head, router, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { CalendarRange } from "lucide-react";
import { useUpdateAppointment } from "@/modules/appointments/hooks/useAppointmentMutations";
import type { Appointment, AppointmentFormData } from "@/modules/appointments/types";
import AppLayout from "@/pages/layouts/AppLayout";
import AppointmentForm from "./components/AppointmentForm";

interface AppointmentEditPageProps extends PageProps {
    appointment: Appointment;
}

export default function AppointmentEditPage(): React.JSX.Element {
    const { appointment } = usePage<AppointmentEditPageProps>().props;
    const updateAppointment = useUpdateAppointment();

    async function handleSubmit(data: AppointmentFormData): Promise<void> {
        await updateAppointment.mutateAsync({
            uuid: appointment.uuid,
            data,
        });
    }

    return (
        <>
            <Head title={`Edit ${appointment.full_name}`} />
            <AppLayout>
                <div className="mx-auto flex max-w-6xl flex-col gap-6">
                    <div className="flex items-start gap-4">
                        <div className="flex h-14 w-14 items-center justify-center rounded-2xl" style={{ background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)", color: "var(--accent-primary)" }}>
                            <CalendarRange size={24} />
                        </div>
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                Edit appointment
                            </h1>
                            <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                Update the selected appointment lead information.
                            </p>
                        </div>
                    </div>

                    <div className="card overflow-hidden p-0">
                        <AppointmentForm
                            initialData={{
                                first_name: appointment.first_name,
                                last_name: appointment.last_name,
                                phone: appointment.phone ?? "",
                                email: appointment.email ?? "",
                                address: appointment.address ?? "",
                                address_2: appointment.address_2 ?? "",
                                city: appointment.city ?? "",
                                state: appointment.state ?? "",
                                zipcode: appointment.zipcode ?? "",
                                country: appointment.country ?? "",
                                insurance_property: appointment.insurance_property,
                                message: appointment.message ?? "",
                                sms_consent: appointment.sms_consent,
                                registration_date: appointment.registration_date ?? "",
                                inspection_date: appointment.inspection_date ?? "",
                                inspection_time: appointment.inspection_time ?? "",
                                notes: appointment.notes ?? "",
                                owner: appointment.owner ?? "",
                                damage_detail: appointment.damage_detail ?? "",
                                intent_to_claim: appointment.intent_to_claim,
                                lead_source: appointment.lead_source ?? "",
                                follow_up_date: appointment.follow_up_date ?? "",
                                additional_note: appointment.additional_note ?? "",
                                inspection_status: appointment.inspection_status,
                                status_lead: appointment.status_lead,
                                latitude: appointment.latitude === null ? "" : String(appointment.latitude),
                                longitude: appointment.longitude === null ? "" : String(appointment.longitude),
                            }}
                            onSubmit={handleSubmit}
                            isSubmitting={updateAppointment.isPending}
                            onCancel={() => router.visit("/appointments")}
                        />
                    </div>
                </div>
            </AppLayout>
        </>
    );
}
