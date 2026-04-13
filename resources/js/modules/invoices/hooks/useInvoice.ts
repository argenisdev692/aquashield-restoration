import axios from 'axios';
import { useQuery } from '@tanstack/react-query';
import type { Invoice } from '../types';

interface InvoiceResponse {
    data: Invoice;
}

export function useInvoice(uuid: string) {
    return useQuery<Invoice, Error>({
        queryKey: ['invoices', 'detail', uuid],
        queryFn: async () => {
            const { data } = await axios.get<InvoiceResponse>(
                `/invoices/data/admin/${uuid}`,
            );
            return data.data;
        },
        enabled: !!uuid,
        staleTime: 1000 * 60 * 2,
    });
}
