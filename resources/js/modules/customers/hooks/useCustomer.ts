import axios from 'axios';
import { useQuery } from '@tanstack/react-query';
import type { Customer } from '../types';

export function useCustomer(uuid: string | null) {
    return useQuery<Customer | null, Error>({
        queryKey: ['customers', 'detail', uuid],
        queryFn: async () => {
            if (uuid === null) {
                return null;
            }

            const { data } = await axios.get<Customer>(
                `/customers/data/admin/${uuid}`,
            );

            return data;
        },
        enabled: uuid !== null,
    });
}
