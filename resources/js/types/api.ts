/* ══════════════════════════════════════════════════════════════════
   API Response Contracts — mirrors backend DTOs exactly
   Per ARQUITECTURE-REACT-INERTIA.md — types/api.ts
   ══════════════════════════════════════════════════════════════════ */

// ── Shared Pagination ────────────────────────────────────────────
export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
  };
}

// ── Export Types ─────────────────────────────────────────────────
export type ExportFormat = 'excel' | 'pdf';

export interface ExportParams {
  format: ExportFormat;
  dateFrom?: string;
  dateTo?: string;
  [key: string]: string | number | boolean | undefined;
}

// ── User Status (mirrors backend enum) ──────────────────────────
export type UserStatus = 'active' | 'suspended' | 'banned' | 'pending_setup';

// ── User List Item (for tables) ─────────────────────────────────
export interface UserListItem {
  id: number;
  uuid: string;
  name: string;
  last_name: string | null;
  full_name: string;
  email: string;
  status: UserStatus;
  roles: string[];
  profile_photo_path: string | null;
  created_at: string; // ISO 8601
}

// ── User Detail ─────────────────────────────────────────────────
export interface UserDetail extends UserListItem {
  username: string | null;
  phone: string | null;
  date_of_birth: string | null;
  address: string | null;
  zip_code: string | null;
  city: string | null;
  state: string | null;
  country: string | null;
  gender: string | null;
  updated_at: string;
}

// ── User Filters ────────────────────────────────────────────────
export interface UserFilters {
  page?: number;
  perPage?: number;
  search?: string;
  status?: UserStatus | '';
  dateFrom?: string;   // ISO 8601 'YYYY-MM-DD'
  dateTo?: string;     // ISO 8601 'YYYY-MM-DD'
  sortBy?: string;
  sortDir?: 'asc' | 'desc';
}

// ── Page Props ──────────────────────────────────────────────────
export interface UsersIndexPageProps {
  filters: UserFilters;
}

// ── Company Data ────────────────────────────────────────────────

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
  status: string;
  signature_url: string | null;
  created_at: string; // ISO 8601
  updated_at: string | null;
  deleted_at?: string | null;
}

export interface CompanyDataDetail extends CompanyDataListItem {
  facebook_link: string | null;
  instagram_link: string | null;
  linkedin_link: string | null;
  twitter_link: string | null;
  latitude: number | null;
  longitude: number | null;
  signature_url: string | null;
  updated_at: string | null;
  deleted_at: string | null;
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
  date_from?: string;   // ISO 8601 'YYYY-MM-DD'
  date_to?: string;     // ISO 8601 'YYYY-MM-DD'
  sort_by?: string;
  sort_dir?: 'asc' | 'desc';
}

export interface CompanyDataIndexPageProps {
  filters: CompanyDataFilters;
}

// ── Authentication ──────────────────────────────────────────────

export interface AuthUser {
  id: number;
  uuid: string;
  name: string;
  last_name: string | null;
  username: string | null;
  email: string;
  email_verified_at: string | null;
  phone: string | null;
  date_of_birth: string | null;
  address: string | null;
  address_2: string | null;
  zip_code: string | null;
  city: string | null;
  state: string | null;
  country: string | null;
  gender: string | null;
  profile_photo_path: string | null;
  latitude: number | null;
  longitude: number | null;
  terms_and_conditions: boolean;
  roles: string[];
  permissions: string[];
  created_at: string;
  updated_at: string;
}

export interface LoginPasswordDTO {
  email: string;
  password: string;
}

export interface LoginOtpRequestDTO {
  identifier: string; // email or phone
}

export interface LoginOtpVerifyDTO {
  identifier: string;
  otp: string;
}

export interface ForgotPasswordEmailDTO {
  email: string;
}

export interface ForgotPasswordOtpDTO {
  email: string;
  otp: string;
}

export interface ForgotPasswordResetDTO {
  email: string;
  token: string;
  password: string;
  password_confirmation: string;
}

export interface UpdateProfileDTO {
  name: string;
  last_name: string | null;
  username: string | null;
  email: string;
  phone: string | null;
  date_of_birth: string | null;
  address: string | null;
  address_2: string | null;
  zip_code: string | null;
  city: string | null;
  state: string | null;
  country: string | null;
  gender: string | null;
}

export interface UpdatePasswordDTO {
  current_password: string;
  password: string;
  password_confirmation: string;
}


// ── Mortgage Company ────────────────────────────────────────────

export interface MortgageCompanyListItem {
  uuid: string;
  mortgageCompanyName: string;
  address: string | null;
  phone: string | null;
  email: string | null;
  website: string | null;
  createdAt: string; // ISO 8601
  deletedAt: string | null;
}

export interface MortgageCompanyDetail {
  uuid: string;
  mortgageCompanyName: string;
  address: string | null;
  phone: string | null;
  email: string | null;
  website: string | null;
  userId: number;
  createdAt: string;
  updatedAt: string;
  deletedAt: string | null;
}

export interface MortgageCompanyFilters {
  page?: number;
  perPage?: number;
  search?: string;
  status?: 'active' | 'deleted' | '';
  dateFrom?: string;
  dateTo?: string;
}
