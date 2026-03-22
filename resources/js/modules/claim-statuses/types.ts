export interface ClaimStatus {
    uuid: string;
    claim_status_name: string;
    background_color: string | null;
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface ClaimStatusListItem {
    uuid: string;
    claim_status_name: string;
    background_color: string | null;
    created_at: string;
    deleted_at: string | null;
}

export interface ClaimStatusFilters {
    search?: string;
    status?: "active" | "deleted";
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface ClaimStatusFormData {
    claim_status_name: string;
    background_color: string;
}

export interface PaginatedClaimStatusResponse {
    data: ClaimStatusListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
