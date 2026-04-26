export interface Appointment {
    uuid: string;
    full_name: string;
    first_name: string;
    last_name: string;
    phone: string | null;
    email: string | null;
    address: string | null;
    address_2: string | null;
    city: string | null;
    state: string | null;
    zipcode: string | null;
    country: string | null;
    insurance_property: boolean;
    message: string | null;
    sms_consent: boolean;
    registration_date: string | null;
    inspection_date: string | null;
    inspection_time: string | null;
    notes: string | null;
    owner: string | null;
    damage_detail: string | null;
    intent_to_claim: boolean;
    lead_source: string | null;
    follow_up_date: string | null;
    additional_note: string | null;
    inspection_status: string;
    status_lead: string;
    latitude: number | null;
    longitude: number | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface AppointmentListItem {
    uuid: string;
    full_name: string;
    phone: string | null;
    email: string | null;
    inspection_status: string;
    status_lead: string;
    inspection_date: string | null;
    created_at: string;
    deleted_at: string | null;
}

export interface AppointmentFilters {
    search?: string;
    status?: "active" | "deleted";
    inspection_status?: string;
    status_lead?: string;
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface AppointmentFormData {
    first_name: string;
    last_name: string;
    phone: string;
    email: string;
    address: string;
    address_2: string;
    city: string;
    state: string;
    zipcode: string;
    country: string;
    insurance_property: boolean;
    message: string;
    sms_consent: boolean;
    registration_date: string;
    inspection_date: string;
    inspection_time: string;
    notes: string;
    owner: string;
    damage_detail: string;
    intent_to_claim: boolean;
    lead_source: string;
    follow_up_date: string;
    additional_note: string;
    inspection_status: string;
    status_lead: string;
    latitude: string;
    longitude: string;
}

export interface PaginatedAppointmentResponse {
    data: AppointmentListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export interface AppointmentCalendarEventProps {
    uuid: string;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string | null;
    phone: string | null;
    address: string;
    inspection_date: string | null;
    inspection_time: string | null;
    inspection_status: string;
    status_lead: string;
    notes: string | null;
    damage_detail: string | null;
    message: string | null;
    insurance_property: boolean;
    latitude: number | null;
    longitude: number | null;
}

export interface AppointmentCalendarEvent {
    id: string;
    title: string;
    start: string;
    end: string;
    allDay: boolean;
    backgroundColor: string;
    borderColor: string;
    extendedProps: AppointmentCalendarEventProps;
}

export type AppointmentInspectionStatus =
    | "Pending"
    | "Confirmed"
    | "Declined"
    | "Completed";
