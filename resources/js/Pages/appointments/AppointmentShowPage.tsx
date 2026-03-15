import { Head, Link, usePage } from "@inertiajs/react";
import type { PageProps } from "@inertiajs/core";
import { ArrowLeft, CalendarRange, Pencil } from "lucide-react";
import type { Appointment } from "@/modules/appointments/types";
import AppLayout from "@/pages/layouts/AppLayout";

interface AppointmentShowPageProps extends PageProps {
    appointment: Appointment;
}

export default function AppointmentShowPage(): React.JSX.Element {
    const { appointment } = usePage<AppointmentShowPageProps>().props;

    return (
        <>
            <Head title={appointment.full_name} />
            <AppLayout>
                <div className="mx-auto flex max-w-6xl flex-col gap-6">
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <Link href="/appointments" className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold">
                            <ArrowLeft size={16} />
                            <span>Back to appointments</span>
                        </Link>

                        {!appointment.deleted_at ? (
                            <Link href={`/appointments/${appointment.uuid}/edit`} className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold">
                                <Pencil size={16} />
                                <span>Edit appointment</span>
                            </Link>
                        ) : null}
                    </div>

                    <div className="card overflow-hidden p-0">
                        <div className="flex items-start gap-4 border-b px-6 py-6" style={{ borderColor: "var(--border-default)" }}>
                            <div className="flex h-14 w-14 items-center justify-center rounded-2xl" style={{ background: "color-mix(in srgb, var(--accent-primary) 12%, transparent)", color: "var(--accent-primary)" }}>
                                <CalendarRange size={24} />
                            </div>
                            <div className="space-y-1">
                                <h1 className="text-3xl font-extrabold" style={{ color: "var(--text-primary)" }}>
                                    {appointment.full_name}
                                </h1>
                                <p className="text-sm" style={{ color: "var(--text-muted)" }}>
                                    Appointment details
                                </p>
                            </div>
                        </div>

                        <div className="grid gap-6 px-6 py-6 md:grid-cols-2 xl:grid-cols-3">
                            <Info label="Phone" value={appointment.phone ?? "—"} />
                            <Info label="Email" value={appointment.email ?? "—"} />
                            <Info label="Owner" value={appointment.owner ?? "—"} />
                            <Info label="Inspection status" value={appointment.inspection_status} />
                            <Info label="Lead status" value={appointment.status_lead} />
                            <Info label="Inspection date" value={appointment.inspection_date ?? "—"} />
                            <Info label="Inspection time" value={appointment.inspection_time ?? "—"} />
                            <Info label="Registration date" value={appointment.registration_date ?? "—"} />
                            <Info label="Follow up date" value={appointment.follow_up_date ?? "—"} />
                            <Info label="Address" value={appointment.address ?? "—"} />
                            <Info label="Address 2" value={appointment.address_2 ?? "—"} />
                            <Info label="City" value={appointment.city ?? "—"} />
                            <Info label="State" value={appointment.state ?? "—"} />
                            <Info label="Zipcode" value={appointment.zipcode ?? "—"} />
                            <Info label="Country" value={appointment.country ?? "—"} />
                            <Info label="Lead source" value={appointment.lead_source ?? "—"} />
                            <Info label="Latitude" value={appointment.latitude === null ? "—" : String(appointment.latitude)} />
                            <Info label="Longitude" value={appointment.longitude === null ? "—" : String(appointment.longitude)} />
                            <Info label="Insurance property" value={appointment.insurance_property ? "Yes" : "No"} />
                            <Info label="SMS consent" value={appointment.sms_consent ? "Yes" : "No"} />
                            <Info label="Intent to claim" value={appointment.intent_to_claim ? "Yes" : "No"} />
                            <Info label="Created at" value={new Date(appointment.created_at).toLocaleString()} />
                            <Info label="Updated at" value={new Date(appointment.updated_at).toLocaleString()} />
                            <Info label="Status" value={appointment.deleted_at ? "Deleted" : "Active"} />
                            <TextInfo label="Message" value={appointment.message ?? "—"} />
                            <TextInfo label="Damage detail" value={appointment.damage_detail ?? "—"} />
                            <TextInfo label="Notes" value={appointment.notes ?? "—"} />
                            <TextInfo label="Additional note" value={appointment.additional_note ?? "—"} />
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

function TextInfo({ label, value }: { label: string; value: string }): React.JSX.Element {
    return (
        <div className="space-y-2 md:col-span-2 xl:col-span-3">
            <p className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>{label}</p>
            <p className="text-sm leading-7" style={{ color: "var(--text-secondary)" }}>{value}</p>
        </div>
    );
}
