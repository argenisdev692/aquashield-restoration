import axios from 'axios';
import { useQuery } from '@tanstack/react-query';
import type { MortgageCompany } from '../types';

export function useMortgageCompany(uuid: string) {
    return useQuery<MortgageCompany, Error>({
        queryKey: ['mortgage-companies', 'detail', uuid],
        queryFn: async () => {
            const { data } = await axios.get<{ data: MortgageCompany }>(
                `/mortgage-companies/data/admin/${uuid}`,
            );

            return data.data;
        },
        staleTime: 1000 * 60 * 5,
    });
}
