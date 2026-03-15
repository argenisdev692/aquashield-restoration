import * as React from "react";
import type { AppointmentFormData } from "@/modules/appointments/types";

interface AppointmentFormProps {
    initialData?: Partial<AppointmentFormData>;
    onSubmit: (data: AppointmentFormData) => Promise<void>;
    isSubmitting: boolean;
    onCancel: () => void;
}

const INSPECTION_STATUS_OPTIONS = ["Pending", "Scheduled", "Completed", "Canceled"];
const STATUS_LEAD_OPTIONS = ["New", "Contacted", "Qualified", "Closed", "Lost"];

export default function AppointmentForm({
    initialData,
    onSubmit,
    isSubmitting,
    onCancel,
}: AppointmentFormProps): React.JSX.Element {
    const [formData, setFormData] = React.useState<AppointmentFormData>({
        first_name: initialData?.first_name ?? "",
        last_name: initialData?.last_name ?? "",
        phone: initialData?.phone ?? "",
        email: initialData?.email ?? "",
        address: initialData?.address ?? "",
        address_2: initialData?.address_2 ?? "",
        city: initialData?.city ?? "",
        state: initialData?.state ?? "",
        zipcode: initialData?.zipcode ?? "",
        country: initialData?.country ?? "",
        insurance_property: initialData?.insurance_property ?? false,
        message: initialData?.message ?? "",
        sms_consent: initialData?.sms_consent ?? false,
        registration_date: initialData?.registration_date ?? "",
        inspection_date: initialData?.inspection_date ?? "",
        inspection_time: initialData?.inspection_time ?? "",
        notes: initialData?.notes ?? "",
        owner: initialData?.owner ?? "",
        damage_detail: initialData?.damage_detail ?? "",
        intent_to_claim: initialData?.intent_to_claim ?? false,
        lead_source: initialData?.lead_source ?? "",
        follow_up_date: initialData?.follow_up_date ?? "",
        additional_note: initialData?.additional_note ?? "",
        inspection_status: initialData?.inspection_status ?? "Pending",
        status_lead: initialData?.status_lead ?? "New",
        latitude: initialData?.latitude ?? "",
        longitude: initialData?.longitude ?? "",
    });
    const [errors, setErrors] = React.useState<Partial<Record<keyof AppointmentFormData, string>>>({});

    function handleTextChange(event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>): void {
        const { name, value } = event.target;
        const field = name as keyof AppointmentFormData;

        setFormData((current) => ({
            ...current,
            [field]: value,
        }));

        if (errors[field]) {
            setErrors((current) => ({
                ...current,
                [field]: undefined,
            }));
        }
    }

    function handleCheckboxChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const { name, checked } = event.target;
        const field = name as keyof AppointmentFormData;

        setFormData((current) => ({
            ...current,
            [field]: checked,
        }));
    }

    function validate(): boolean {
        const nextErrors: Partial<Record<keyof AppointmentFormData, string>> = {};

        if (formData.first_name.trim() === "") {
            nextErrors.first_name = "First name is required.";
        }

        if (formData.last_name.trim() === "") {
            nextErrors.last_name = "Last name is required.";
        }

        setErrors(nextErrors);
        return Object.keys(nextErrors).length === 0;
    }

    async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
        event.preventDefault();

        if (!validate()) {
            return;
        }

        await onSubmit({
            ...formData,
            first_name: formData.first_name.trim(),
            last_name: formData.last_name.trim(),
            phone: formData.phone.trim(),
            email: formData.email.trim(),
            address: formData.address.trim(),
            address_2: formData.address_2.trim(),
            city: formData.city.trim(),
            state: formData.state.trim(),
            zipcode: formData.zipcode.trim(),
            country: formData.country.trim(),
            message: formData.message.trim(),
            notes: formData.notes.trim(),
            owner: formData.owner.trim(),
            damage_detail: formData.damage_detail.trim(),
            lead_source: formData.lead_source.trim(),
            additional_note: formData.additional_note.trim(),
            latitude: formData.latitude.trim(),
            longitude: formData.longitude.trim(),
        });
    }

    function inputStyle(hasError: boolean): React.CSSProperties {
        return {
            background: "var(--input-bg)",
            color: "var(--input-text)",
            border: `1px solid ${hasError ? "var(--accent-error)" : "var(--input-border)"}`,
            fontFamily: "var(--font-sans)",
        };
    }

    function renderField(label: string, name: keyof AppointmentFormData, type: string = "text"): React.JSX.Element {
        return (
            <div className="space-y-2">
                <label htmlFor={name} className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>
                    {label}
                </label>
                <input id={name} name={name} type={type} value={String(formData[name])} onChange={handleTextChange} className="w-full rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(Boolean(errors[name]))} />
                {errors[name] ? <p className="text-xs" style={{ color: "var(--accent-error)" }}>{errors[name]}</p> : null}
            </div>
        );
    }

    return (
        <form onSubmit={handleSubmit} className="space-y-6 p-6 md:p-8">
            <div className="grid gap-6 md:grid-cols-2">
                {renderField("First name", "first_name")}
                {renderField("Last name", "last_name")}
                {renderField("Phone", "phone")}
                {renderField("Email", "email", "email")}
                {renderField("Address", "address")}
                {renderField("Address 2", "address_2")}
                {renderField("City", "city")}
                {renderField("State", "state")}
                {renderField("Zipcode", "zipcode")}
                {renderField("Country", "country")}
                {renderField("Registration date", "registration_date", "date")}
                {renderField("Inspection date", "inspection_date", "date")}
                {renderField("Inspection time", "inspection_time", "time")}
                {renderField("Owner", "owner")}
                {renderField("Lead source", "lead_source")}
                {renderField("Follow up date", "follow_up_date", "date")}
                {renderField("Latitude", "latitude", "number")}
                {renderField("Longitude", "longitude", "number")}

                <div className="space-y-2">
                    <label htmlFor="inspection_status" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>
                        Inspection status
                    </label>
                    <select id="inspection_status" name="inspection_status" value={formData.inspection_status} onChange={handleTextChange} className="w-full rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(false)}>
                        {INSPECTION_STATUS_OPTIONS.map((option) => (
                            <option key={option} value={option}>{option}</option>
                        ))}
                    </select>
                </div>

                <div className="space-y-2">
                    <label htmlFor="status_lead" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>
                        Lead status
                    </label>
                    <select id="status_lead" name="status_lead" value={formData.status_lead} onChange={handleTextChange} className="w-full rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(false)}>
                        {STATUS_LEAD_OPTIONS.map((option) => (
                            <option key={option} value={option}>{option}</option>
                        ))}
                    </select>
                </div>
            </div>

            <div className="space-y-2">
                <label htmlFor="message" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>Message</label>
                <textarea id="message" name="message" value={formData.message} onChange={handleTextChange} rows={4} className="w-full resize-none rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(false)} />
            </div>

            <div className="space-y-2">
                <label htmlFor="damage_detail" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>Damage detail</label>
                <textarea id="damage_detail" name="damage_detail" value={formData.damage_detail} onChange={handleTextChange} rows={4} className="w-full resize-none rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(false)} />
            </div>

            <div className="space-y-2">
                <label htmlFor="notes" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>Notes</label>
                <textarea id="notes" name="notes" value={formData.notes} onChange={handleTextChange} rows={4} className="w-full resize-none rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(false)} />
            </div>

            <div className="space-y-2">
                <label htmlFor="additional_note" className="block text-sm font-semibold" style={{ color: "var(--text-secondary)" }}>Additional note</label>
                <textarea id="additional_note" name="additional_note" value={formData.additional_note} onChange={handleTextChange} rows={4} className="w-full resize-none rounded-xl px-4 py-3 text-sm outline-none" style={inputStyle(false)} />
            </div>

            <div className="grid gap-4 md:grid-cols-3">
                <label className="flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold" style={{ borderColor: "var(--border-default)", color: "var(--text-secondary)" }}>
                    <input type="checkbox" name="insurance_property" checked={formData.insurance_property} onChange={handleCheckboxChange} className="h-4 w-4" style={{ accentColor: "var(--accent-primary)" }} />
                    Insurance property
                </label>
                <label className="flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold" style={{ borderColor: "var(--border-default)", color: "var(--text-secondary)" }}>
                    <input type="checkbox" name="sms_consent" checked={formData.sms_consent} onChange={handleCheckboxChange} className="h-4 w-4" style={{ accentColor: "var(--accent-primary)" }} />
                    SMS consent
                </label>
                <label className="flex items-center gap-3 rounded-xl border px-4 py-3 text-sm font-semibold" style={{ borderColor: "var(--border-default)", color: "var(--text-secondary)" }}>
                    <input type="checkbox" name="intent_to_claim" checked={formData.intent_to_claim} onChange={handleCheckboxChange} className="h-4 w-4" style={{ accentColor: "var(--accent-primary)" }} />
                    Intent to claim
                </label>
            </div>

            <div className="flex flex-col gap-3 pt-2 sm:flex-row sm:justify-end">
                <button type="button" onClick={onCancel} disabled={isSubmitting} className="btn-ghost px-5 py-3 text-sm font-semibold">
                    Cancel
                </button>
                <button type="submit" disabled={isSubmitting} className="btn-primary px-5 py-3 text-sm font-semibold">
                    {isSubmitting ? "Saving..." : "Save appointment"}
                </button>
            </div>
        </form>
    );
}
