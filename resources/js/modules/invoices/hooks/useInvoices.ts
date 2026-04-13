import axios from 'axios';
import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type { InvoiceFilters, PaginatedInvoiceResponse } from '../types';

export function useInvoices(filters: InvoiceFilters) {
    return useQuery<PaginatedInvoiceResponse, Error>({
        queryKey: ['invoices', 'list', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedInvoiceResponse>(
                '/invoices/data/admin',
                { params: filters },
            );
            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
