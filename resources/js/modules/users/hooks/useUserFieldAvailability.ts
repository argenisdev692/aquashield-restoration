import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import { useDebounce } from '@/common/hooks/useDebounce';
import { hasCompleteUsPhone } from '@/common/helpers/phone';

export type UserAvailabilityField = 'email' | 'username' | 'phone';

type UserAvailabilityScope = 'admin' | 'profile';

interface UserFieldAvailabilityResponse {
  field: UserAvailabilityField;
  available: boolean;
}

interface UseUserFieldAvailabilityOptions {
  field: UserAvailabilityField;
  value: string;
  scope: UserAvailabilityScope;
  ignoreUuid?: string;
}

function isValidEmailCandidate(value: string): boolean {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
}

export function shouldCheckUserFieldAvailability(field: UserAvailabilityField, value: string): boolean {
  const trimmedValue = value.trim();

  if (trimmedValue.length === 0) {
    return false;
  }

  if (field === 'email') {
    return isValidEmailCandidate(trimmedValue);
  }

  if (field === 'username') {
    return trimmedValue.length >= 3;
  }

  return hasCompleteUsPhone(trimmedValue);
}

export function getUserAvailabilityErrorMessage(field: UserAvailabilityField): string {
  if (field === 'email') {
    return 'This email is already registered.';
  }

  if (field === 'username') {
    return 'This username is already in use.';
  }

  return 'This phone number is already in use.';
}

export function useUserFieldAvailability({ field, value, scope, ignoreUuid }: UseUserFieldAvailabilityOptions) {
  const normalizedValue = value.trim();
  const debouncedValue = useDebounce(normalizedValue, 450);
  const enabled = shouldCheckUserFieldAvailability(field, debouncedValue);

  return useQuery<UserFieldAvailabilityResponse, Error>({
    queryKey: ['users', 'availability', scope, field, debouncedValue, ignoreUuid ?? null],
    queryFn: async () => {
      const { data } = await axios.get<{ data: UserFieldAvailabilityResponse }>(`/users/data/${scope}/availability`, {
        params: {
          field,
          value: debouncedValue,
          ignore_uuid: ignoreUuid,
        },
      });

      return data.data;
    },
    enabled,
    staleTime: 30000,
  });
}
