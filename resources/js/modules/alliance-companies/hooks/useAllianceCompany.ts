import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import { AllianceCompany } from '../types';

export const useAllianceCompany = (uuid: string | null) => {
    return useQuery({
        queryKey: ['alliance-company', uuid],
        queryFn: async () => {
            if (!uuid) return null;
            const { data } = await axios.get<{ data: AllianceCompany }>(`/alliance-companies/data/${uuid}`);
            return data.data;
        },
        enabled: !!uuid,
    });
};
