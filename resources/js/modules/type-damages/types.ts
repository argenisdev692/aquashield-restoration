export interface TypeDamage {
    uuid: string;
    type_damage_name: string;
    description: string | null;
    severity: "low" | "medium" | "high";
    created_at: string;
    updated_at: string;
    deleted_at: string | null;
}

export interface TypeDamageListItem {
    uuid: string;
    type_damage_name: string;
    description: string | null;
    severity: "low" | "medium" | "high";
    created_at: string;
    deleted_at: string | null;
}

export interface TypeDamageFilters {
    search?: string;
    severity?: "low" | "medium" | "high";
    status?: "active" | "deleted";
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface TypeDamageFormData {
    type_damage_name: string;
    description: string;
    severity: "low" | "medium" | "high";
}

export interface PaginatedTypeDamageResponse {
    data: TypeDamageListItem[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}
