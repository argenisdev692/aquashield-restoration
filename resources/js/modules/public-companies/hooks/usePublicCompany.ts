import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { PublicCompany } from '../types';

export const usePublicCompany = (uuid: string | null) => {
    return useQuery<PublicCompany | null, Error>({
        queryKey: ['public-company', uuid],
        queryFn: async () => {
            if (!uuid) {
                return null;
            }

            const { data } = await axios.get<{ data: PublicCompany }>(`/public-companies/data/admin/${uuid}`);

            return data.data;
        },
        enabled: uuid !== null,
    });
};
