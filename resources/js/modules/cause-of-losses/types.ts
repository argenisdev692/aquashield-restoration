export interface CauseOfLoss {
    uuid: string;
    cause_loss_name: string;
    description: string | null;
    severity: "low" | "medium" | "high";
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface CauseOfLossListItem {
    uuid: string;
    cause_loss_name: string;
    description: string | null;
    severity: "low" | "medium" | "high";
    created_at: string;
    deleted_at: string | null;
}

export interface CauseOfLossFilters {
    search?: string;
    severity?: "low" | "medium" | "high";
    status?: "active" | "deleted";
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface CauseOfLossFormData {
    cause_loss_name: string;
    description: string;
    severity: "low" | "medium" | "high";
}

export interface PaginatedCauseOfLossResponse {
    data: CauseOfLossListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
