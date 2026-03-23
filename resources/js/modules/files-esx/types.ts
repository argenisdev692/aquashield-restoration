export interface FileEsxUploader {
    id: number;
    name: string;
    email: string;
}

export interface FileEsxAdjuster {
    id: number;
    name: string;
    email: string;
}

export interface FileEsx {
    id: number;
    uuid: string;
    file_name: string | null;
    file_path: string;
    file_url: string;
    uploaded_by: number;
    uploader: FileEsxUploader | null;
    assigned_adjusters: FileEsxAdjuster[];
    created_at: string;
    updated_at: string;
}

export interface FileEsxFilters {
    search?: string;
    date_from?: string;
    date_to?: string;
    page?: number;
    per_page?: number;
}

export interface FileEsxFormData {
    file_name: string;
    file_path: string;
}

export interface AssignFileEsxPayload {
    public_adjuster_id: number;
}

export type CreateFileEsxPayload = FileEsxFormData;
export type UpdateFileEsxPayload = Pick<FileEsxFormData, 'file_name'>;

export type FileEsxFormErrors = Partial<Record<keyof FileEsxFormData, string>>;
