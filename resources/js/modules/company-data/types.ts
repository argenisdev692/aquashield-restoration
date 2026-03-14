import type { PaginatedResponse } from '@/types/api';

export interface CompanyDataListItem {
  uuid: string;
  user_uuid: string;
  name: string | null;
  company_name: string;
  email: string | null;
  phone: string | null;
  address: string | null;
  address_2: string | null;
  website: string | null;
  signature_url: string | null;
  created_at: string;
  updated_at: string | null;
  deleted_at: string | null;
}

export interface CompanyDataDetail extends CompanyDataListItem {
  facebook_link: string | null;
  instagram_link: string | null;
  linkedin_link: string | null;
  twitter_link: string | null;
  latitude: number | null;
  longitude: number | null;
}

export interface CreateCompanyDataDTO {
  user_uuid: string;
  company_name: string;
  name?: string | null;
  email?: string | null;
  phone?: string | null;
  address?: string | null;
  address_2?: string | null;
  website?: string | null;
  facebook_link?: string | null;
  instagram_link?: string | null;
  linkedin_link?: string | null;
  twitter_link?: string | null;
  latitude?: number | null;
  longitude?: number | null;
  signature_data_url?: string | null;
  remove_signature?: boolean;
}

export interface UpdateCompanyDataDTO {
  company_name: string;
  name?: string | null;
  email?: string | null;
  phone?: string | null;
  address?: string | null;
  address_2?: string | null;
  website?: string | null;
  facebook_link?: string | null;
  instagram_link?: string | null;
  linkedin_link?: string | null;
  twitter_link?: string | null;
  latitude?: number | null;
  longitude?: number | null;
  signature_data_url?: string | null;
  remove_signature?: boolean;
}

export interface CompanyDataFilters {
  page?: number;
  per_page?: number;
  search?: string;
  user_uuid?: string;
  date_from?: string;
  date_to?: string;
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
}

export interface CompanyDataIndexPageProps {
  filters: CompanyDataFilters;
}

export type CompanyDataPaginatedResponse = PaginatedResponse<CompanyDataListItem>;
