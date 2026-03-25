import axios from 'axios';
import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type { PaginatedPropertyResponse, PropertyFilters } from '../types';

export function useProperties(filters: PropertyFilters) {
    return useQuery<PaginatedPropertyResponse, Error>({
        queryKey: ['properties', 'list', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedPropertyResponse>(
                '/properties/data/admin',
                { params: filters },
            );

            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
