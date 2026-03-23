import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { FileEsx } from '../types';

export const useFileEsx = (uuid: string) => {
    return useQuery<{ data: FileEsx }, Error>({
        queryKey: ['files-esx', uuid],
        queryFn: async () => {
            const { data } = await axios.get<{ data: FileEsx }>(`/files-esx/data/admin/${uuid}`);

            return data;
        },
        staleTime: 1000 * 60 * 2,
        enabled: uuid !== '',
    });
};
