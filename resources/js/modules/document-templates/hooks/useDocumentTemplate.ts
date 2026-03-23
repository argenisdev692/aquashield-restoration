import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { DocumentTemplate } from '../types';
import { documentTemplateKeys } from './useDocumentTemplates';

export function useDocumentTemplate(
    uuid: string,
): ReturnType<typeof useQuery<DocumentTemplate>> {
    return useQuery<DocumentTemplate>({
        queryKey: documentTemplateKeys.detail(uuid),
        queryFn: async () => {
            const { data } = await axios.get<DocumentTemplate>(
                `/document-templates/data/admin/${uuid}`,
            );
            return data;
        },
        enabled: uuid.length > 0,
    });
}
