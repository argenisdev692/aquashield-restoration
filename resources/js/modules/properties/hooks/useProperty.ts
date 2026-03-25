import axios from 'axios';
import { useQuery } from '@tanstack/react-query';
import type { Property } from '../types';

export function useProperty(uuid: string | null) {
    return useQuery<Property | null, Error>({
        queryKey: ['properties', 'detail', uuid],
        queryFn: async () => {
            if (uuid === null) {
                return null;
            }

            const { data } = await axios.get<Property>(
                `/properties/data/admin/${uuid}`,
            );

            return data;
        },
        enabled: uuid !== null,
    });
}
