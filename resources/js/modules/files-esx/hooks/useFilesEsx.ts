import { keepPreviousData, useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { PaginatedResponse } from '@/types/api';
import type { FileEsx, FileEsxFilters } from '../types';

export const useFilesEsx = (filters: FileEsxFilters = {}) => {
    return useQuery<PaginatedResponse<FileEsx>, Error>({
        queryKey: ['files-esx', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedResponse<FileEsx>>('/files-esx/data/admin', {
                params: filters,
            });

            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
};
