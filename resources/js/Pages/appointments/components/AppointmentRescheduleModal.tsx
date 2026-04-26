import * as React from "react";
import { Calendar, Clock } from "lucide-react";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@/shadcn/dialog";
import type { AppointmentCalendarEventProps } from "@/modules/appointments/types";

interface AppointmentRescheduleModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    event: AppointmentCalendarEventProps | null;
    initialDate?: string;
    initialTime?: string;
    onConfirm: (date: string, time: string) => void;
    isPending: boolean;
}

export function AppointmentRescheduleModal({
    open,
    onOpenChange,
    event,
    initialDate,
    initialTime,
    onConfirm,
    isPending,
}: AppointmentRescheduleModalProps): React.JSX.Element {
    const [date, setDate] = React.useState<string>("");
    const [time, setTime] = React.useState<string>("");

    React.useEffect(() => {
        if (open) {
            setDate(initialDate ?? event?.inspection_date ?? "");
            setTime(initialTime ?? event?.inspection_time ?? "");
        }
    }, [open, event, initialDate, initialTime]);

    const isValid = date !== "" && time !== "";

    function handleSubmit(e: React.FormEvent<HTMLFormElement>): void {
        e.preventDefault();

        if (!isValid) {
            return;
        }

        onConfirm(date, time);
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent
                className="!max-w-md"
                style={{
                    background: "var(--bg-card)",
                    border: "1px solid var(--border-default)",
                }}
            >
                <DialogHeader>
                    <DialogTitle style={{ color: "var(--text-primary)" }}>
                        Reschedule appointment
                    </DialogTitle>
                    <DialogDescription style={{ color: "var(--text-muted)" }}>
                        {event !== null
                            ? `Choose a new date and time for ${event.full_name}. The customer will be notified by email.`
                            : "Choose a new date and time. The customer will be notified by email."}
                    </DialogDescription>
                </DialogHeader>

                <form onSubmit={handleSubmit} className="flex flex-col gap-4">
                    <label className="flex flex-col gap-2 text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>
                        <span className="flex items-center gap-2 text-xs uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                            <Calendar size={12} /> Date
                        </span>
                        <input
                            type="date"
                            value={date}
                            onChange={(e) => setDate(e.target.value)}
                            required
                            className="rounded-lg px-3 py-2 text-sm font-medium"
                            style={{
                                background: "var(--input-bg)",
                                border: "1px solid var(--input-border)",
                                color: "var(--text-primary)",
                                colorScheme: "dark",
                            }}
                        />
                    </label>

                    <label className="flex flex-col gap-2 text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>
                        <span className="flex items-center gap-2 text-xs uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                            <Clock size={12} /> Time
                        </span>
                        <input
                            type="time"
                            value={time}
                            onChange={(e) => setTime(e.target.value)}
                            required
                            className="rounded-lg px-3 py-2 text-sm font-medium"
                            style={{
                                background: "var(--input-bg)",
                                border: "1px solid var(--input-border)",
                                color: "var(--text-primary)",
                                colorScheme: "dark",
                            }}
                        />
                    </label>

                    <div className="flex justify-end gap-2 pt-2">
                        <button
                            type="button"
                            onClick={() => onOpenChange(false)}
                            disabled={isPending}
                            className="btn-ghost rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={!isValid || isPending}
                            className="btn-primary rounded-lg px-4 py-2 text-sm font-semibold disabled:opacity-50"
                        >
                            {isPending ? "Saving..." : "Reschedule"}
                        </button>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default AppointmentRescheduleModal;
