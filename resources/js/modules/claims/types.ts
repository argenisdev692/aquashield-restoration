export type ClaimStatus = 'Active' | 'Suspended';

export interface ClaimCustomer {
    id: number;
    uuid: string;
    full_name: string;
    email: string | null;
    cell_phone: string | null;
    home_phone: string | null;
}

export interface ClaimCompanyAssignment {
    id: number;
    company_id: number;
    company_name: string;
    assignment_date: string | null;
}

export interface ClaimAdjusterAssignment {
    id: number;
    adjuster_id: number;
    adjuster_name: string;
    assignment_date: string | null;
}

export interface ClaimAlliance {
    id: number;
    alliance_company_id: number;
    alliance_company_name: string;
    assignment_date: string | null;
}

export interface Claim {
    id: number;
    uuid: string;
    claim_number: string | null;
    claim_internal_id: string;
    policy_number: string;
    date_of_loss: string | null;
    description_of_loss: string | null;
    number_of_floors: number | null;
    claim_date: string | null;
    work_date: string | null;
    damage_description: string | null;
    scope_of_work: string | null;
    customer_reviewed: boolean | null;
    property_id: number;
    property_address: string | null;
    customers: ClaimCustomer[];
    type_damage_id: number;
    type_damage_name: string | null;
    claim_status_id: number;
    claim_status_name: string | null;
    claim_status_color: string | null;
    user_id_ref_by: number;
    referred_by_name: string | null;
    causes_of_loss: { id: number; name: string }[];
    service_requests: { id: number; name: string }[];
    insurance_company_assignment: ClaimCompanyAssignment | null;
    public_company_assignment: ClaimCompanyAssignment | null;
    insurance_adjuster_assignment: ClaimAdjusterAssignment | null;
    public_adjuster_assignment: ClaimAdjusterAssignment | null;
    claim_alliance: ClaimAlliance | null;
    status: ClaimStatus;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface ClaimListItem {
    uuid: string;
    claim_number: string | null;
    claim_internal_id: string;
    policy_number: string;
    date_of_loss: string | null;
    property_id: number;
    property_address: string | null;
    customers: ClaimCustomer[];
    type_damage_id: number;
    type_damage_name: string | null;
    claim_status_id: number;
    claim_status_name: string | null;
    claim_status_color: string | null;
    user_id_ref_by: number;
    referred_by_name: string | null;
    status: ClaimStatus;
    created_at: string;
    deleted_at: string | null;
}

export interface ClaimFilters {
    search?: string;
    status?: 'active' | 'deleted';
    date_from?: string;
    date_to?: string;
    claim_status_id?: number;
    type_damage_id?: number;
    page?: number;
    per_page?: number;
}

export type CustomerSlotRole = 'owner' | 'co_owner' | 'extra';

export interface CustomerSlot {
    role: CustomerSlotRole;
    customer_uuid: string | null;
    customer_id: number | null;
    customer_label: string;
}

export interface ClaimWizardFormData {
    property_id: number | null;
    property_address: string;
    property_lat: number | null;
    property_lng: number | null;
    customer_slots: CustomerSlot[];
    policy_number: string;
    claim_number: string;
    date_of_loss: string;
    description_of_loss: string;
    number_of_floors: string;
    claim_date: string;
    work_date: string;
    type_damage_id: number | null;
    claim_status: number | null;
    insurance_company_id: number | null;
    insurance_company_name: string;
    public_company_id: number | null;
    public_company_name: string;
    alliance_company_id: number | null;
    alliance_company_name: string;
    mortgage_company_id: number | null;
    mortgage_company_name: string;
    damage_description: string;
    scope_of_work: string;
    customer_reviewed: boolean;
    cause_of_loss_ids: number[];
    service_request_ids: number[];
    user_id_ref_by: number | null;
    signature_path_id: number;
}

export interface ClaimStorePayload {
    property_id: number;
    signature_path_id: number;
    type_damage_id: number;
    user_id_ref_by: number;
    claim_status: number;
    policy_number: string;
    claim_number?: string | null;
    date_of_loss?: string | null;
    description_of_loss?: string | null;
    number_of_floors?: number | null;
    claim_date?: string | null;
    work_date?: string | null;
    damage_description?: string | null;
    scope_of_work?: string | null;
    customer_reviewed?: boolean | null;
    cause_of_loss_ids?: number[] | null;
    service_request_ids?: number[] | null;
}

export interface PaginatedClaimResponse {
    data: ClaimListItem[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
}

export const EMPTY_CUSTOMER_SLOTS: CustomerSlot[] = [
    { role: 'owner',    customer_uuid: null, customer_id: null, customer_label: 'Owner' },
    { role: 'co_owner', customer_uuid: null, customer_id: null, customer_label: 'Co-Owner' },
    { role: 'extra',    customer_uuid: null, customer_id: null, customer_label: 'Extra Contact' },
];

export const DEFAULT_WIZARD_FORM: ClaimWizardFormData = {
    property_id: null,
    property_address: '',
    property_lat: null,
    property_lng: null,
    customer_slots: [...EMPTY_CUSTOMER_SLOTS],
    policy_number: '',
    claim_number: '',
    date_of_loss: '',
    description_of_loss: '',
    number_of_floors: '',
    claim_date: '',
    work_date: '',
    type_damage_id: null,
    claim_status: null,
    insurance_company_id: null,
    insurance_company_name: '',
    public_company_id: null,
    public_company_name: '',
    alliance_company_id: null,
    alliance_company_name: '',
    mortgage_company_id: null,
    mortgage_company_name: '',
    damage_description: '',
    scope_of_work: '',
    customer_reviewed: false,
    cause_of_loss_ids: [],
    service_request_ids: [],
    user_id_ref_by: null,
    signature_path_id: 1,
};
