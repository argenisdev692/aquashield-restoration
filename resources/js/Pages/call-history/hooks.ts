import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { CallHistoryListItem, CallHistoryFilters, PaginatedResponse, SyncResult } from './types';

const API_BASE = '/call-history/data/admin';

// Query Keys
export const callHistoryQueryKeys = {
    all: ['call-history'] as const,
    list: (filters: CallHistoryFilters) => [...callHistoryQueryKeys.all, 'list', filters] as const,
    detail: (uuid: string) => [...callHistoryQueryKeys.all, 'detail', uuid] as const,
};

// List Call History
export const useCallHistoryList = (filters: CallHistoryFilters = {}) => {
    return useQuery({
        queryKey: callHistoryQueryKeys.list(filters),
        queryFn: async (): Promise<PaginatedResponse<CallHistoryListItem>> => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.status) params.append('status', filters.status);
            if (filters.direction) params.append('direction', filters.direction);
            if (filters.callType) params.append('call_type', filters.callType);
            if (filters.dateFrom) params.append('date_from', filters.dateFrom);
            if (filters.dateTo) params.append('date_to', filters.dateTo);
            if (filters.sortField) params.append('sort_field', filters.sortField);
            if (filters.sortDirection) params.append('sort_direction', filters.sortDirection);
            params.append('per_page', String(filters.perPage ?? 10));
            params.append('page', String(filters.page ?? 1));

            const response = await axios.get(`${API_BASE}/list?${params.toString()}`);
            return response.data;
        },
        placeholderData: (previousData) => previousData,
    });
};

// Get Single Call History
export const useCallHistoryDetail = (uuid: string | null) => {
    return useQuery({
        queryKey: callHistoryQueryKeys.detail(uuid ?? ''),
        queryFn: async (): Promise<CallHistoryListItem | null> => {
            if (!uuid) return null;
            const response = await axios.get(`${API_BASE}/${uuid}`);
            return response.data.data;
        },
        enabled: !!uuid,
    });
};

// Update Call History
export const useUpdateCallHistory = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async ({ uuid, data }: { uuid: string; data: Partial<CallHistoryListItem> }) => {
            const response = await axios.put(`${API_BASE}/${uuid}`, data);
            return response.data;
        },
        onSuccess: (_, variables) => {
            void queryClient.invalidateQueries({ queryKey: callHistoryQueryKeys.all });
            void queryClient.invalidateQueries({ queryKey: callHistoryQueryKeys.detail(variables.uuid) });
        },
    });
};

// Delete Call History
export const useDeleteCallHistory = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (uuid: string) => {
            const response = await axios.delete(`${API_BASE}/${uuid}`);
            return response.data;
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: callHistoryQueryKeys.all });
        },
    });
};

// Restore Call History
export const useRestoreCallHistory = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (uuid: string) => {
            const response = await axios.post(`${API_BASE}/${uuid}/restore`);
            return response.data;
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: callHistoryQueryKeys.all });
        },
    });
};

// Bulk Delete Call History
export const useBulkDeleteCallHistory = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (uuids: string[]) => {
            const response = await axios.post(`${API_BASE}/bulk-delete`, { uuids });
            return response.data;
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: callHistoryQueryKeys.all });
        },
    });
};

// Sync Calls from Retell AI
export const useSyncCallsFromRetell = () => {
    const queryClient = useQueryClient();

    return useMutation({
        mutationFn: async (params: { startDate?: string; endDate?: string; limit?: number }) => {
            const response = await axios.post(`${API_BASE}/sync`, params);
            return response.data.result as SyncResult;
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: callHistoryQueryKeys.all });
        },
    });
};

// Export Call History
export const useExportCallHistory = () => {
    return (format: 'excel' | 'pdf', filters: CallHistoryFilters = {}) => {
        const params = new URLSearchParams({ format });
        if (filters.search) params.append('search', filters.search);
        if (filters.status) params.append('status', filters.status);
        if (filters.direction) params.append('direction', filters.direction);
        if (filters.callType) params.append('call_type', filters.callType);
        if (filters.dateFrom) params.append('date_from', filters.dateFrom);
        if (filters.dateTo) params.append('date_to', filters.dateTo);

        window.open(`${API_BASE}/export?${params.toString()}`, '_blank');
    };
};
