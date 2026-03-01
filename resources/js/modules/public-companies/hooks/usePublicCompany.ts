import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import { PublicCompany } from '../types';

export const usePublicCompany = (uuid: string | null) => {
    return useQuery({
        queryKey: ['public-company', uuid],
        queryFn: async () => {
            if (!uuid) return null;
            const { data } = await axios.get<{ data: PublicCompany }>(`/public-companies/data/${uuid}`);
            return data.data;
        },
        enabled: !!uuid,
    });
};
