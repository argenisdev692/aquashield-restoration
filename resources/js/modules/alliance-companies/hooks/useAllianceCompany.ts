import axios from 'axios';
import { useQuery } from '@tanstack/react-query';
import type { AllianceCompany } from '../types';

export function useAllianceCompany(uuid: string | null) {
    return useQuery<AllianceCompany | null, Error>({
        queryKey: ['alliance-companies', 'detail', uuid],
        queryFn: async () => {
            if (uuid === null) {
                return null;
            }

            const { data } = await axios.get<AllianceCompany>(
                `/alliance-companies/data/admin/${uuid}`,
            );

            return data;
        },
        enabled: uuid !== null,
    });
}
