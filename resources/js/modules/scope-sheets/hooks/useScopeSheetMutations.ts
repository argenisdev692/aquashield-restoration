import axios, { isAxiosError } from 'axios';
import { router } from '@inertiajs/react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { sileo } from 'sileo';
import type { ScopeSheetFormData, ScopeSheetPresentation, ScopeSheetZone, PhotoUploadResponse } from '../types';

interface MutationResponse {
    message: string;
    uuid?: string;
}

function getErrorMessage(error: unknown, fallback: string): string {
    if (isAxiosError<{ message?: string }>(error)) {
        return error.response?.data?.message ?? error.message ?? fallback;
    }
    if (error instanceof Error) return error.message;
    return fallback;
}

// ── Photo Upload ──────────────────────────────────────────────────────────────

export async function uploadScopeSheetPhoto(file: File): Promise<PhotoUploadResponse> {
    const form = new FormData();
    form.append('photo', file);
    const { data } = await axios.post<PhotoUploadResponse>(
        '/scope-sheets/data/admin/upload-photo',
        form,
        { headers: { 'Content-Type': 'multipart/form-data' } },
    );
    return data;
}

// ── Helpers ───────────────────────────────────────────────────────────────────

async function resolvePresentation(p: ScopeSheetPresentation): Promise<{ photo_type: string; photo_path: string; photo_order: number }> {
    if (p._file) {
        const uploaded = await uploadScopeSheetPhoto(p._file);
        return { photo_type: p.photo_type, photo_path: uploaded.path, photo_order: p.photo_order };
    }
    return { photo_type: p.photo_type, photo_path: p.photo_path, photo_order: p.photo_order };
}

async function resolveZone(z: ScopeSheetZone): Promise<{ zone_id: number; zone_order: number; zone_notes: string; photos: { photo_path: string; photo_order: number }[] }> {
    const photos = await Promise.all(
        z.photos.map(async (ph, idx) => {
            if (ph._file) {
                const uploaded = await uploadScopeSheetPhoto(ph._file);
                return { photo_path: uploaded.path, photo_order: idx };
            }
            return { photo_path: ph.photo_path, photo_order: ph.photo_order };
        }),
    );
    return {
        zone_id: z.zone_id,
        zone_order: z.zone_order,
        zone_notes: z.zone_notes,
        photos,
    };
}

async function buildPayload(form: ScopeSheetFormData) {
    const [presentations, zones] = await Promise.all([
        Promise.all(form.presentations.map(resolvePresentation)),
        Promise.all(form.zones.map(resolveZone)),
    ]);
    return {
        claim_id: form.claim_id,
        generated_by: form.generated_by,
        scope_sheet_description: form.scope_sheet_description || null,
        presentations,
        zones,
    };
}

// ── Mutations ─────────────────────────────────────────────────────────────────

export function useCreateScopeSheet() {
    const queryClient = useQueryClient();
    return useMutation<MutationResponse, Error, ScopeSheetFormData>({
        mutationFn: async (form) => {
            const payload = await buildPayload(form);
            const { data } = await axios.post<MutationResponse>('/scope-sheets/data/admin', payload);
            return data;
        },
        onSuccess: async (res) => {
            await queryClient.invalidateQueries({ queryKey: ['scope-sheets'] });
            sileo.success({ title: 'Scope sheet created successfully.' });
            if (res.uuid) {
                router.visit(`/scope-sheets/${res.uuid}`);
            } else {
                router.visit('/scope-sheets');
            }
        },
        onError: (err) => {
            sileo.error({ title: getErrorMessage(err, 'Failed to create scope sheet.') });
        },
    });
}

export function useUpdateScopeSheet() {
    const queryClient = useQueryClient();
    return useMutation<MutationResponse, Error, { uuid: string; form: ScopeSheetFormData }>({
        mutationFn: async ({ uuid, form }) => {
            const payload = await buildPayload(form);
            const { data } = await axios.put<MutationResponse>(`/scope-sheets/data/admin/${uuid}`, payload);
            return data;
        },
        onSuccess: async (_res, { uuid }) => {
            await queryClient.invalidateQueries({ queryKey: ['scope-sheets'] });
            await queryClient.invalidateQueries({ queryKey: ['scope-sheets', 'detail', uuid] });
            sileo.success({ title: 'Scope sheet updated successfully.' });
            router.visit(`/scope-sheets/${uuid}`);
        },
        onError: (err) => {
            sileo.error({ title: getErrorMessage(err, 'Failed to update scope sheet.') });
        },
    });
}

export function useDeleteScopeSheet() {
    const queryClient = useQueryClient();
    return useMutation<MutationResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.delete<MutationResponse>(`/scope-sheets/data/admin/${uuid}`);
            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['scope-sheets'] });
            sileo.success({ title: 'Scope sheet deleted successfully.' });
        },
        onError: (err) => {
            sileo.error({ title: getErrorMessage(err, 'Failed to delete scope sheet.') });
        },
    });
}

export function useRestoreScopeSheet() {
    const queryClient = useQueryClient();
    return useMutation<MutationResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.patch<MutationResponse>(`/scope-sheets/data/admin/${uuid}/restore`);
            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['scope-sheets'] });
            sileo.success({ title: 'Scope sheet restored successfully.' });
        },
        onError: (err) => {
            sileo.error({ title: getErrorMessage(err, 'Failed to restore scope sheet.') });
        },
    });
}

export function useBulkDeleteScopeSheets() {
    const queryClient = useQueryClient();
    return useMutation<MutationResponse, Error, string[]>({
        mutationFn: async (uuids) => {
            const { data } = await axios.post<MutationResponse>('/scope-sheets/data/admin/bulk-delete', { uuids });
            return data;
        },
        onSuccess: async (res) => {
            await queryClient.invalidateQueries({ queryKey: ['scope-sheets'] });
            sileo.success({ title: res.message });
        },
        onError: (err) => {
            sileo.error({ title: getErrorMessage(err, 'Failed to bulk delete scope sheets.') });
        },
    });
}
