import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { AxiosError } from 'axios';
import { sileo } from 'sileo';
import type {
  CampaignDetail,
  CreateCampaignPayload,
  GenerateCampaignPayload,
  UpdateCampaignPayload,
} from '@/modules/ai-campaigns/types';

function getErrorMessage(error: AxiosError | Error | unknown, fallback: string): string {
  if (axios.isAxiosError(error)) {
    const message = error.response?.data as { message?: string } | undefined;
    if (typeof message?.message === 'string' && message.message.length > 0) {
      return message.message;
    }
  }
  if (error instanceof Error && error.message.length > 0) {
    return error.message;
  }
  return fallback;
}

export function useCampaignMutations() {
  const queryClient = useQueryClient();

  const createCampaign = useMutation({
    mutationFn: async (payload: CreateCampaignPayload) => {
      const { data } = await axios.post<{ data: CampaignDetail }>('/ai-campaigns/data/admin', payload);
      return data.data;
    },
    onSuccess: () => {
      sileo.success({ title: 'Campaign created successfully' });
      queryClient.invalidateQueries({ queryKey: ['ai-campaigns'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to create campaign') });
    },
  });

  const updateCampaign = useMutation({
    mutationFn: async ({ uuid, payload }: { uuid: string; payload: UpdateCampaignPayload }) => {
      const { data } = await axios.put<{ data: CampaignDetail }>(`/ai-campaigns/data/admin/${uuid}`, payload);
      return data.data;
    },
    onSuccess: (_, variables) => {
      sileo.success({ title: 'Campaign updated successfully' });
      queryClient.invalidateQueries({ queryKey: ['ai-campaigns'] });
      queryClient.invalidateQueries({ queryKey: ['ai-campaigns', variables.uuid] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to update campaign') });
    },
  });

  const deleteCampaign = useMutation({
    mutationFn: (uuid: string) => axios.delete(`/ai-campaigns/data/admin/${uuid}`),
    onSuccess: () => {
      sileo.success({ title: 'Campaign deleted successfully' });
      queryClient.invalidateQueries({ queryKey: ['ai-campaigns'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to delete campaign') });
    },
  });

  const restoreCampaign = useMutation({
    mutationFn: (uuid: string) => axios.patch(`/ai-campaigns/data/admin/${uuid}/restore`),
    onSuccess: (_, uuid) => {
      sileo.success({ title: 'Campaign restored successfully' });
      queryClient.invalidateQueries({ queryKey: ['ai-campaigns'] });
      queryClient.invalidateQueries({ queryKey: ['ai-campaigns', uuid] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to restore campaign') });
    },
  });

  const bulkDeleteCampaigns = useMutation({
    mutationFn: (uuids: string[]) => axios.post('/ai-campaigns/data/admin/bulk-delete', { uuids }),
    onSuccess: () => {
      sileo.success({ title: 'Selected campaigns deleted successfully' });
      queryClient.invalidateQueries({ queryKey: ['ai-campaigns'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to delete selected campaigns') });
    },
  });

  const generateCampaign = useMutation({
    mutationFn: async (payload: GenerateCampaignPayload) => {
      const { data } = await axios.post<{ data: CampaignDetail }>('/ai-campaigns/data/admin/generate', payload);
      return data.data;
    },
    onSuccess: () => {
      sileo.success({ title: 'Campaign generated successfully!' });
      queryClient.invalidateQueries({ queryKey: ['ai-campaigns'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'AI generation failed. Check your API keys.') });
    },
  });

  return {
    createCampaign,
    updateCampaign,
    deleteCampaign,
    restoreCampaign,
    bulkDeleteCampaigns,
    generateCampaign,
  };
}
