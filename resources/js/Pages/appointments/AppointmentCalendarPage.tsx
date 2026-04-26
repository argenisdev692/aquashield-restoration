import * as React from "react";
import { Head, Link, router } from "@inertiajs/react";
import FullCalendar from "@fullcalendar/react";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import type {
    DateSelectArg,
    DatesSetArg,
    EventClickArg,
    EventDropArg,
} from "@fullcalendar/core";
import type { EventResizeDoneArg } from "@fullcalendar/interaction";
import { sileo } from "sileo";
import { CalendarDays, List, Plus } from "lucide-react";
import AppLayout from "@/pages/layouts/AppLayout";
import { DeleteConfirmModal } from "@/shadcn/DeleteConfirmModal";
import { useCalendarEvents } from "@/modules/appointments/hooks/useCalendarEvents";
import {
    useRescheduleAppointment,
    useUpdateAppointmentStatus,
} from "@/modules/appointments/hooks/useCalendarMutations";
import { useDeleteAppointment } from "@/modules/appointments/hooks/useAppointmentMutations";
import type {
    AppointmentCalendarEvent,
    AppointmentCalendarEventProps,
} from "@/modules/appointments/types";
import AppointmentDetailModal from "./components/AppointmentDetailModal";
import AppointmentRescheduleModal from "./components/AppointmentRescheduleModal";

const STATUS_LEGEND: ReadonlyArray<{ status: string; color: string; label: string }> = [
    { status: "Pending", color: "#f59e0b", label: "Pending" },
    { status: "Confirmed", color: "#10b981", label: "Confirmed" },
    { status: "Completed", color: "#059669", label: "Completed" },
    { status: "Declined", color: "#ef4444", label: "Declined" },
];

interface RescheduleState {
    event: AppointmentCalendarEventProps | null;
    initialDate?: string;
    initialTime?: string;
}

export default function AppointmentCalendarPage(): React.JSX.Element {
    const [range, setRange] = React.useState<{ start?: string; end?: string }>({});
    const [selectedEvent, setSelectedEvent] = React.useState<AppointmentCalendarEventProps | null>(null);
    const [rescheduleState, setRescheduleState] = React.useState<RescheduleState>({ event: null });
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);

    const { data: events, isPending, isFetching, refetch } = useCalendarEvents(range);
    const reschedule = useRescheduleAppointment();
    const updateStatus = useUpdateAppointmentStatus();
    const deleteAppointment = useDeleteAppointment();

    const calendarRef = React.useRef<FullCalendar | null>(null);

    const fcEvents = React.useMemo<AppointmentCalendarEvent[]>(() => events ?? [], [events]);

    function handleDatesSet(arg: DatesSetArg): void {
        setRange({
            start: arg.startStr,
            end: arg.endStr,
        });
    }

    function handleEventClick(arg: EventClickArg): void {
        const props = arg.event.extendedProps as AppointmentCalendarEventProps;
        setSelectedEvent(props);
    }

    function handleEventDrop(arg: EventDropArg): void {
        const props = arg.event.extendedProps as AppointmentCalendarEventProps;
        const start = arg.event.start;

        if (start === null) {
            arg.revert();
            return;
        }

        const date = formatDate(start);
        const time = formatTime(start);

        reschedule.mutate(
            { uuid: props.uuid, inspection_date: date, inspection_time: time },
            {
                onSuccess: () => {
                    sileo.success({ title: "Appointment rescheduled.", description: `Moved to ${date} at ${time}.` });
                    void refetch();
                },
                onError: (error) => {
                    sileo.error({ title: "Reschedule failed.", description: error.message });
                    arg.revert();
                },
            },
        );
    }

    function handleEventResize(arg: EventResizeDoneArg): void {
        // Resizing currently not supported by domain — revert and surface message.
        arg.revert();
        sileo.info({ title: "Duration changes are not supported. Drag to a new time instead." });
    }

    function handleDateSelect(arg: DateSelectArg): void {
        const date = formatDate(arg.start);
        const time = arg.allDay ? "09:00" : formatTime(arg.start);

        setRescheduleState({ event: null, initialDate: date, initialTime: time });
    }

    function handleStatusChange(uuid: string, status: "Confirmed" | "Declined" | "Completed"): void {
        updateStatus.mutate(
            { uuid, inspection_status: status },
            {
                onSuccess: () => {
                    sileo.success({ title: `Appointment marked as ${status}.` });
                    setSelectedEvent(null);
                    void refetch();
                },
                onError: (error) => {
                    sileo.error({ title: "Status update failed.", description: error.message });
                },
            },
        );
    }

    function handleRescheduleConfirm(date: string, time: string): void {
        if (rescheduleState.event === null) {
            // Date click without an existing event — redirect to create form with prefilled date.
            const params = new URLSearchParams({ inspection_date: date, inspection_time: time });
            router.visit(`/appointments/create?${params.toString()}`);
            setRescheduleState({ event: null });
            return;
        }

        const uuid = rescheduleState.event.uuid;

        reschedule.mutate(
            { uuid, inspection_date: date, inspection_time: time },
            {
                onSuccess: () => {
                    sileo.success({ title: "Appointment rescheduled." });
                    setRescheduleState({ event: null });
                    setSelectedEvent(null);
                    void refetch();
                },
                onError: (error) => {
                    sileo.error({ title: "Reschedule failed.", description: error.message });
                },
            },
        );
    }

    async function handleConfirmDelete(): Promise<void> {
        if (pendingDelete === null) return;

        try {
            await deleteAppointment.mutateAsync(pendingDelete.uuid);
            sileo.success({ title: "Appointment deleted." });
            setSelectedEvent(null);
            void refetch();
        } catch (error) {
            const message = error instanceof Error ? error.message : "Failed to delete appointment.";
            sileo.error({ title: message });
        } finally {
            setPendingDelete(null);
        }
    }

    return (
        <>
            <Head title="Appointment Calendar" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 className="text-3xl font-extrabold tracking-tight" style={{ color: "var(--text-primary)" }}>
                                Appointment Calendar
                            </h1>
                            <p className="text-sm font-medium" style={{ color: "var(--text-muted)" }}>
                                Drag to reschedule, click events for details, click empty slots to create.
                            </p>
                        </div>
                        <div className="flex flex-wrap items-center gap-2">
                            <Link
                                href="/appointments"
                                className="btn-ghost inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold"
                            >
                                <List size={14} />
                                List view
                            </Link>
                            <Link
                                href="/appointments/create"
                                className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                <Plus size={16} />
                                <span>New appointment</span>
                            </Link>
                        </div>
                    </div>

                    <div className="card p-4 sm:p-6">
                        <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                            <div className="flex flex-wrap items-center gap-3">
                                <CalendarDays size={16} style={{ color: "var(--accent-primary)" }} />
                                <span className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Status legend
                                </span>
                                <div className="flex flex-wrap items-center gap-3">
                                    {STATUS_LEGEND.map((legend) => (
                                        <div key={legend.status} className="flex items-center gap-2">
                                            <span
                                                className="inline-block h-3 w-3 rounded-full"
                                                style={{ background: legend.color }}
                                                aria-hidden
                                            />
                                            <span className="text-xs font-semibold" style={{ color: "var(--text-secondary)" }}>
                                                {legend.label}
                                            </span>
                                        </div>
                                    ))}
                                </div>
                            </div>
                            {isFetching ? (
                                <span className="text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                    Refreshing…
                                </span>
                            ) : null}
                        </div>

                        <div className="aq-calendar">
                            <FullCalendar
                                ref={calendarRef}
                                plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
                                initialView="dayGridMonth"
                                headerToolbar={{
                                    left: "prev,next today",
                                    center: "title",
                                    right: "dayGridMonth,timeGridWeek,timeGridDay",
                                }}
                                buttonText={{
                                    today: "Today",
                                    month: "Month",
                                    week: "Week",
                                    day: "Day",
                                }}
                                events={fcEvents}
                                editable
                                selectable
                                selectMirror
                                dayMaxEvents
                                weekends
                                height="auto"
                                nowIndicator
                                slotMinTime="07:00:00"
                                slotMaxTime="20:00:00"
                                eventTimeFormat={{
                                    hour: "2-digit",
                                    minute: "2-digit",
                                    meridiem: "short",
                                }}
                                datesSet={handleDatesSet}
                                eventClick={handleEventClick}
                                eventDrop={handleEventDrop}
                                eventResize={handleEventResize}
                                select={handleDateSelect}
                                loading={() => undefined}
                            />
                        </div>

                        {isPending ? (
                            <p className="mt-4 text-center text-xs font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                                Loading appointments…
                            </p>
                        ) : null}
                    </div>
                </div>

                <AppointmentDetailModal
                    open={selectedEvent !== null}
                    onOpenChange={(open) => {
                        if (!open) {
                            setSelectedEvent(null);
                        }
                    }}
                    event={selectedEvent}
                    onConfirm={(uuid) => handleStatusChange(uuid, "Confirmed")}
                    onDecline={(uuid) => handleStatusChange(uuid, "Declined")}
                    onComplete={(uuid) => handleStatusChange(uuid, "Completed")}
                    onReschedule={(event) => setRescheduleState({ event })}
                    onDelete={(uuid, name) => setPendingDelete({ uuid, name })}
                    isUpdatingStatus={updateStatus.isPending}
                />

                <AppointmentRescheduleModal
                    open={rescheduleState.event !== null || rescheduleState.initialDate !== undefined}
                    onOpenChange={(open) => {
                        if (!open) {
                            setRescheduleState({ event: null });
                        }
                    }}
                    event={rescheduleState.event}
                    initialDate={rescheduleState.initialDate}
                    initialTime={rescheduleState.initialTime}
                    onConfirm={handleRescheduleConfirm}
                    isPending={reschedule.isPending}
                />

                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.name ?? ""}
                    onConfirm={() => {
                        void handleConfirmDelete();
                    }}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteAppointment.isPending}
                />
            </AppLayout>
        </>
    );
}

function formatDate(value: Date): string {
    const year = value.getFullYear();
    const month = String(value.getMonth() + 1).padStart(2, "0");
    const day = String(value.getDate()).padStart(2, "0");
    return `${year}-${month}-${day}`;
}

function formatTime(value: Date): string {
    const hours = String(value.getHours()).padStart(2, "0");
    const minutes = String(value.getMinutes()).padStart(2, "0");
    return `${hours}:${minutes}`;
}
