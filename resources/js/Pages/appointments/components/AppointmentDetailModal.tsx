import * as React from "react";
import { Link } from "@inertiajs/react";
import { sileo } from "sileo";
import {
    Calendar,
    Check,
    Clock,
    Copy,
    Mail,
    MapPin,
    Phone,
    Pencil,
    ShieldCheck,
    Trash2,
    User,
    XCircle,
} from "lucide-react";
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from "@/shadcn/dialog";
import type { AppointmentCalendarEventProps } from "@/modules/appointments/types";

interface AppointmentDetailModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    event: AppointmentCalendarEventProps | null;
    onConfirm: (uuid: string) => void;
    onDecline: (uuid: string) => void;
    onComplete: (uuid: string) => void;
    onReschedule: (event: AppointmentCalendarEventProps) => void;
    onDelete: (uuid: string, name: string) => void;
    isUpdatingStatus: boolean;
}

const STATUS_VARIANT: Record<string, { bg: string; color: string; border: string }> = {
    Confirmed: { bg: "color-mix(in srgb, var(--accent-success) 18%, transparent)", color: "var(--accent-success)", border: "color-mix(in srgb, var(--accent-success) 40%, transparent)" },
    Pending: { bg: "color-mix(in srgb, var(--accent-warning) 18%, transparent)", color: "var(--accent-warning)", border: "color-mix(in srgb, var(--accent-warning) 40%, transparent)" },
    Completed: { bg: "color-mix(in srgb, var(--accent-success) 12%, transparent)", color: "var(--accent-success)", border: "color-mix(in srgb, var(--accent-success) 30%, transparent)" },
    Declined: { bg: "color-mix(in srgb, var(--accent-error) 18%, transparent)", color: "var(--accent-error)", border: "color-mix(in srgb, var(--accent-error) 40%, transparent)" },
};

function StatusBadge({ status }: { status: string }): React.JSX.Element {
    const variant = STATUS_VARIANT[status] ?? STATUS_VARIANT.Pending;

    return (
        <span
            className="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-[1px]"
            style={{
                backgroundColor: variant.bg,
                color: variant.color,
                border: `1px solid ${variant.border}`,
            }}
        >
            {status}
        </span>
    );
}

function InfoRow({
    icon,
    label,
    children,
}: {
    icon: React.ReactNode;
    label: string;
    children: React.ReactNode;
}): React.JSX.Element {
    return (
        <div className="flex items-start gap-3 py-2">
            <div className="flex h-9 w-9 flex-none items-center justify-center rounded-lg" style={{ background: "var(--bg-overlay)", color: "var(--accent-primary)" }}>
                {icon}
            </div>
            <div className="flex-1 min-w-0">
                <p className="text-[11px] font-semibold uppercase tracking-[1.5px]" style={{ color: "var(--text-disabled)" }}>
                    {label}
                </p>
                <div className="text-sm font-medium" style={{ color: "var(--text-primary)" }}>
                    {children}
                </div>
            </div>
        </div>
    );
}

function formatDate(value: string | null): string {
    if (!value) return "—";

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return value;

    return date.toLocaleDateString(undefined, {
        weekday: "short",
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}

function formatTime(value: string | null): string {
    if (!value) return "—";

    const [hours, minutes] = value.split(":").map((part) => Number.parseInt(part, 10));

    if (Number.isNaN(hours) || Number.isNaN(minutes)) return value;

    const date = new Date();
    date.setHours(hours, minutes, 0, 0);

    return date.toLocaleTimeString(undefined, { hour: "2-digit", minute: "2-digit" });
}

export function AppointmentDetailModal({
    open,
    onOpenChange,
    event,
    onConfirm,
    onDecline,
    onComplete,
    onReschedule,
    onDelete,
    isUpdatingStatus,
}: AppointmentDetailModalProps): React.JSX.Element | null {
    if (event === null) {
        return null;
    }

    const mapsUrl = event.latitude !== null && event.longitude !== null
        ? `https://www.google.com/maps/search/?api=1&query=${event.latitude},${event.longitude}`
        : event.address !== ""
            ? `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(event.address)}`
            : null;

    function handleCopyAddress(): void {
        if (event === null || event.address === "") {
            return;
        }
        void navigator.clipboard.writeText(event.address)
            .then(() => sileo.success({ title: "Address copied to clipboard." }))
            .catch(() => sileo.error({ title: "Failed to copy address." }));
    }

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent
                className="!max-w-2xl overflow-hidden p-0"
                style={{
                    background: "var(--bg-card)",
                    border: "1px solid var(--border-default)",
                }}
            >
                <DialogHeader className="px-6 pt-6 pb-4" style={{ borderBottom: "1px solid var(--border-subtle)" }}>
                    <div className="flex items-start justify-between gap-4">
                        <div className="min-w-0">
                            <DialogTitle className="text-xl font-bold" style={{ color: "var(--text-primary)" }}>
                                {event.full_name}
                            </DialogTitle>
                            <div className="mt-2 flex flex-wrap items-center gap-2">
                                <StatusBadge status={event.inspection_status} />
                                <span className="text-xs font-semibold" style={{ color: "var(--text-muted)" }}>
                                    Lead: {event.status_lead}
                                </span>
                            </div>
                        </div>
                    </div>
                </DialogHeader>

                <div className="grid gap-2 px-6 py-4 sm:grid-cols-2">
                    <InfoRow icon={<Calendar size={16} />} label="Date">
                        {formatDate(event.inspection_date)}
                    </InfoRow>
                    <InfoRow icon={<Clock size={16} />} label="Time">
                        {formatTime(event.inspection_time)}
                    </InfoRow>
                    <InfoRow icon={<Mail size={16} />} label="Email">
                        {event.email ?? "—"}
                    </InfoRow>
                    <InfoRow icon={<Phone size={16} />} label="Phone">
                        {event.phone ?? "—"}
                    </InfoRow>
                    <div className="sm:col-span-2">
                        <InfoRow icon={<MapPin size={16} />} label="Address">
                            {event.address !== "" ? event.address : "—"}
                            {event.address !== "" ? (
                                <div className="mt-2 flex flex-wrap gap-2">
                                    {mapsUrl !== null ? (
                                        <a
                                            href={mapsUrl}
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            className="btn-ghost inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold"
                                        >
                                            <MapPin size={12} />
                                            Open in Maps
                                        </a>
                                    ) : null}
                                    <button
                                        type="button"
                                        onClick={handleCopyAddress}
                                        className="btn-ghost inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold"
                                    >
                                        <Copy size={12} />
                                        Copy
                                    </button>
                                </div>
                            ) : null}
                        </InfoRow>
                    </div>
                    <InfoRow icon={<ShieldCheck size={16} />} label="Insurance">
                        {event.insurance_property ? "Yes" : "No"}
                    </InfoRow>
                    <InfoRow icon={<User size={16} />} label="Damage">
                        {event.damage_detail ?? "—"}
                    </InfoRow>
                    {event.notes !== null && event.notes !== "" ? (
                        <div className="sm:col-span-2">
                            <InfoRow icon={<User size={16} />} label="Notes">
                                <p className="whitespace-pre-line">{event.notes}</p>
                            </InfoRow>
                        </div>
                    ) : null}
                </div>

                <div
                    className="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                    style={{ borderTop: "1px solid var(--border-subtle)", background: "var(--bg-subtle)" }}
                >
                    <div className="flex flex-wrap gap-2">
                        {event.inspection_status !== "Confirmed" && event.inspection_status !== "Completed" ? (
                            <button
                                type="button"
                                onClick={() => onConfirm(event.uuid)}
                                disabled={isUpdatingStatus}
                                className="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold disabled:opacity-50"
                                style={{ background: "var(--accent-success)", color: "#fff" }}
                            >
                                <Check size={14} />
                                Confirm
                            </button>
                        ) : null}
                        {event.inspection_status === "Confirmed" ? (
                            <button
                                type="button"
                                onClick={() => onComplete(event.uuid)}
                                disabled={isUpdatingStatus}
                                className="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold disabled:opacity-50"
                                style={{ background: "var(--accent-info)", color: "#fff" }}
                            >
                                <Check size={14} />
                                Mark Completed
                            </button>
                        ) : null}
                        {event.inspection_status !== "Declined" ? (
                            <button
                                type="button"
                                onClick={() => onDecline(event.uuid)}
                                disabled={isUpdatingStatus}
                                className="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold disabled:opacity-50"
                                style={{ background: "var(--accent-error)", color: "#fff" }}
                            >
                                <XCircle size={14} />
                                Decline
                            </button>
                        ) : null}
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <button
                            type="button"
                            onClick={() => onReschedule(event)}
                            className="btn-ghost inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold"
                        >
                            <Clock size={14} />
                            Reschedule
                        </button>
                        <Link
                            href={`/appointments/${event.uuid}/edit`}
                            className="btn-ghost inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold"
                        >
                            <Pencil size={14} />
                            Edit
                        </Link>
                        <button
                            type="button"
                            onClick={() => onDelete(event.uuid, event.full_name)}
                            className="inline-flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold"
                            style={{
                                background: "color-mix(in srgb, var(--accent-error) 14%, transparent)",
                                color: "var(--accent-error)",
                                border: "1px solid color-mix(in srgb, var(--accent-error) 32%, transparent)",
                            }}
                        >
                            <Trash2 size={14} />
                            Delete
                        </button>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    );
}

export default AppointmentDetailModal;
