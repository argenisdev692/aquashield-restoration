export interface CallHistoryListItem {
    uuid: string;
    callId: string;
    agentId: string | null;
    agentName: string | null;
    fromNumber: string | null;
    toNumber: string | null;
    direction: 'inbound' | 'outbound';
    callStatus: 'registered' | 'ongoing' | 'ended' | 'error';
    startTimestamp: string | null;
    endTimestamp: string | null;
    durationMs: number | null;
    transcript: string | null;
    recordingUrl: string | null;
    callAnalysis: {
        call_summary?: string;
        user_sentiment?: string;
        custom_analysis_data?: Record<string, unknown>;
    } | null;
    disconnectionReason: string | null;
    metadata: Record<string, unknown> | null;
    callType: 'lead' | 'appointment' | 'support' | 'other';
    createdAt: string;
    updatedAt: string;
    deletedAt: string | null;
}

export interface CallHistoryFilters {
    search?: string;
    status?: string;
    direction?: string;
    callType?: string;
    dateFrom?: string;
    dateTo?: string;
    sortField?: string;
    sortDirection?: 'asc' | 'desc';
    page?: number;
    perPage?: number;
}

export interface PaginatedResponse<T> {
    data: T[];
    meta: {
        currentPage: number;
        lastPage: number;
        perPage: number;
        total: number;
    };
}

export interface SyncResult {
    created: number;
    updated: number;
    total: number;
    errors: Array<{ call_id: string; error: string }>;
}
