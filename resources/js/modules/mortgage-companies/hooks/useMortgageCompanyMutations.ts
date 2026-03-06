import { useMutation, useQueryClient } from '@tanstack/react-query';

interface CreateMortgageCompanyData {
  mortgageCompanyName: string;
  address?: string;
  phone?: string;
  email?: string;
  website?: string;
}

interface UpdateMortgageCompanyData {
  mortgageCompanyName: string;
  address?: string;
  phone?: string;
  email?: string;
  website?: string;
}

export function useMortgageCompanyMutations() {
  const queryClient = useQueryClient();

  const createMortgageCompany = useMutation<{ uuid: string }, Error, CreateMortgageCompanyData>({
    mutationFn: async (data) => {
      const response = await fetch('/mortgage-companies/data/admin', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });
      if (!response.ok) throw new Error('Failed to create mortgage company');
      return response.json();
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['mortgage-companies'] });
    },
  });

  const updateMortgageCompany = useMutation<void, Error, { uuid: string; data: UpdateMortgageCompanyData }>({
    mutationFn: async ({ uuid, data }) => {
      const response = await fetch(`/mortgage-companies/data/admin/${uuid}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });
      if (!response.ok) throw new Error('Failed to update mortgage company');
    },
    onSuccess: (_, { uuid }) => {
      queryClient.invalidateQueries({ queryKey: ['mortgage-companies'] });
      queryClient.invalidateQueries({ queryKey: ['mortgage-companies', uuid] });
    },
  });

  const deleteMortgageCompany = useMutation<void, Error, string>({
    mutationFn: async (uuid) => {
      const response = await fetch(`/mortgage-companies/data/admin/${uuid}`, {
        method: 'DELETE',
      });
      if (!response.ok) throw new Error('Failed to delete mortgage company');
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['mortgage-companies'] });
    },
  });

  const restoreMortgageCompany = useMutation<void, Error, string>({
    mutationFn: async (uuid) => {
      const response = await fetch(`/mortgage-companies/data/admin/${uuid}/restore`, {
        method: 'PATCH',
      });
      if (!response.ok) throw new Error('Failed to restore mortgage company');
    },
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['mortgage-companies'] });
    },
  });

  return {
    createMortgageCompany,
    updateMortgageCompany,
    deleteMortgageCompany,
    restoreMortgageCompany,
  };
}
