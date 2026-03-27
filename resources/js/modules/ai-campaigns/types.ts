export type CampaignPlatform = 'tiktok' | 'instagram' | 'facebook';
export type CampaignStatus = 'draft' | 'generated' | 'published';
export type CampaignStatusFilter = CampaignStatus | 'deleted' | '';

export interface CampaignListItem {
  uuid: string;
  title: string;
  niche: string;
  platform: CampaignPlatform;
  caption: string | null;
  hashtags: string | null;
  image_url: string | null;
  status: CampaignStatus;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

export interface CampaignDetail extends CampaignListItem {
  call_to_action: string | null;
  image_path: string | null;
  user_id: number | null;
}

export interface CampaignFilters {
  page?: number;
  per_page?: number;
  search?: string;
  status?: CampaignStatusFilter;
  platform?: CampaignPlatform | '';
  date_from?: string;
  date_to?: string;
  sort_by?: 'title' | 'platform' | 'status' | 'created_at' | 'updated_at';
  sort_dir?: 'asc' | 'desc';
}

export interface CreateCampaignPayload {
  title: string;
  niche: string;
  platform: CampaignPlatform;
  caption?: string | null;
  hashtags?: string | null;
  call_to_action?: string | null;
  image_path?: string | null;
  image_url?: string | null;
  status?: CampaignStatus;
}

export interface UpdateCampaignPayload {
  title?: string;
  niche?: string;
  platform?: CampaignPlatform;
  caption?: string | null;
  hashtags?: string | null;
  call_to_action?: string | null;
  image_path?: string | null;
  image_url?: string | null;
  status?: CampaignStatus;
}

export interface GenerateCampaignPayload {
  title: string;
  niche: string;
  platform: CampaignPlatform;
}

export const PLATFORM_LABELS: Record<CampaignPlatform, string> = {
  tiktok: 'TikTok',
  instagram: 'Instagram',
  facebook: 'Facebook',
};

export const PLATFORM_DIMENSIONS: Record<CampaignPlatform, string> = {
  tiktok: '1080×1920 (9:16)',
  instagram: '1080×1080 (1:1)',
  facebook: '1200×630 (16:9)',
};

export const STATUS_LABELS: Record<CampaignStatus, string> = {
  draft: 'Draft',
  generated: 'Generated',
  published: 'Published',
};
