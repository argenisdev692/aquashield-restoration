import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import { InsuranceCompany } from '../types';

export const useInsuranceCompany = (uuid: string | null) => {
    return useQuery({
        queryKey: ['insurance-company', uuid],
        queryFn: async () => {
            if (!uuid) return null;
            const { data } = await axios.get<{ data: InsuranceCompany }>(`/insurance-companies/data/${uuid}`);
            return data.data;
        },
        enabled: !!uuid,
    });
};
