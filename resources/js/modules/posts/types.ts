export type PostStatus = 'draft' | 'published' | 'scheduled' | 'archived';
export type PostStatusFilter = PostStatus | 'deleted' | '';

export interface PostListItem {
  uuid: string;
  post_title: string;
  post_title_slug: string;
  post_excerpt: string | null;
  post_cover_image: string | null;
  category_name: string | null;
  post_status: PostStatus;
  published_at: string | null;
  scheduled_at: string | null;
  created_at: string;
  updated_at: string;
  deleted_at: string | null;
}

export interface PostDetail extends PostListItem {
  post_content: string;
  meta_title: string | null;
  meta_description: string | null;
  meta_keywords: string | null;
  category_uuid: string | null;
  user_id: number | null;
}

export interface PostFilters {
  page?: number;
  per_page?: number;
  search?: string;
  status?: PostStatusFilter;
  date_from?: string;
  date_to?: string;
  sort_by?: 'post_title' | 'post_status' | 'published_at' | 'scheduled_at' | 'created_at' | 'updated_at';
  sort_dir?: 'asc' | 'desc';
}

export interface CreatePostPayload {
  post_title: string;
  post_title_slug?: string | null;
  post_content: string;
  post_excerpt?: string | null;
  post_cover_image?: string | null;
  meta_title?: string | null;
  meta_description?: string | null;
  meta_keywords?: string | null;
  category_uuid?: string | null;
  post_status: PostStatus;
  published_at?: string | null;
  scheduled_at?: string | null;
}

export interface UpdatePostPayload {
  post_title?: string;
  post_title_slug?: string | null;
  post_content?: string;
  post_excerpt?: string | null;
  post_cover_image?: string | null;
  meta_title?: string | null;
  meta_description?: string | null;
  meta_keywords?: string | null;
  category_uuid?: string | null;
  post_status?: PostStatus;
  published_at?: string | null;
  scheduled_at?: string | null;
}
