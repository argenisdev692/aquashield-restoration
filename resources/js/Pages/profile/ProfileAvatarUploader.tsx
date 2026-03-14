import * as React from 'react';
import Cropper from 'react-easy-crop';
import { useDropzone, type FileRejection } from 'react-dropzone';
import { Camera, LoaderCircle, Trash2, UploadCloud } from 'lucide-react';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/shadcn/dialog';
import { createCroppedImage } from './createCroppedImage';

type PixelCrop = {
  x: number;
  y: number;
  width: number;
  height: number;
};

type ProfileAvatarUploaderProps = {
  name: string;
  lastName?: string | null;
  photoUrl?: string | null;
  isUploading: boolean;
  uploadProgress: number | null;
  error?: string;
  onUpload: (file: File) => Promise<void> | void;
  onRemove: () => Promise<void> | void;
};

function buildRejectionMessage(fileRejections: FileRejection[]): string {
  const firstRejection = fileRejections[0];

  if (!firstRejection) {
    return 'The selected image could not be processed.';
  }

  const firstError = firstRejection.errors[0];

  if (!firstError) {
    return 'The selected image could not be processed.';
  }

  if (firstError.code === 'file-too-large') {
    return 'Avatar must be smaller than 2 MB.';
  }

  if (firstError.code === 'file-invalid-type') {
    return 'Only JPG, PNG, and WEBP images are allowed.';
  }

  if (firstError.code === 'too-many-files') {
    return 'Please select only one image.';
  }

  return firstError.message;
}

export default function ProfileAvatarUploader({
  name,
  lastName,
  photoUrl,
  isUploading,
  uploadProgress,
  error,
  onUpload,
  onRemove,
}: ProfileAvatarUploaderProps): React.JSX.Element {
  const [isDialogOpen, setIsDialogOpen] = React.useState<boolean>(false);
  const [sourceImageUrl, setSourceImageUrl] = React.useState<string | null>(null);
  const [crop, setCrop] = React.useState<{ x: number; y: number }>({ x: 0, y: 0 });
  const [zoom, setZoom] = React.useState<number>(1);
  const [croppedAreaPixels, setCroppedAreaPixels] = React.useState<PixelCrop | null>(null);
  const [localError, setLocalError] = React.useState<string>('');
  const [isPreparingImage, setIsPreparingImage] = React.useState<boolean>(false);

  const initials = `${name[0] ?? ''}${lastName?.[0] ?? ''}`.toUpperCase() || 'U';
  const hasPhoto = typeof photoUrl === 'string' && photoUrl.length > 0;
  const effectiveError = error && error.length > 0 ? error : localError;

  const onCropComplete = React.useCallback((_: unknown, areaPixels: PixelCrop) => {
    setCroppedAreaPixels(areaPixels);
  }, []);

  const resetCropState = React.useCallback((): void => {
    if (sourceImageUrl !== null) {
      URL.revokeObjectURL(sourceImageUrl);
    }

    setSourceImageUrl(null);
    setCrop({ x: 0, y: 0 });
    setZoom(1);
    setCroppedAreaPixels(null);
    setIsPreparingImage(false);
  }, [sourceImageUrl]);

  const handleDrop = React.useCallback((acceptedFiles: File[]) => {
    const [file] = acceptedFiles;

    if (!file) {
      return;
    }

    setLocalError('');
    const objectUrl = URL.createObjectURL(file);
    setSourceImageUrl(objectUrl);
    setIsDialogOpen(true);
  }, []);

  const handleDropRejected = React.useCallback((fileRejections: FileRejection[]): void => {
    setLocalError(buildRejectionMessage(fileRejections));
  }, []);

  const { getRootProps, getInputProps, isDragActive, open } = useDropzone({
    accept: {
      'image/jpeg': ['.jpg', '.jpeg'],
      'image/png': ['.png'],
      'image/webp': ['.webp'],
    },
    maxFiles: 1,
    maxSize: 2 * 1024 * 1024,
    multiple: false,
    noKeyboard: true,
    noClick: true,
    disabled: isUploading,
    onDropAccepted: handleDrop,
    onDropRejected: handleDropRejected,
  });

  async function handleConfirmCrop(): Promise<void> {
    if (sourceImageUrl === null || croppedAreaPixels === null) {
      setLocalError('Please select a valid crop area.');
      return;
    }

    try {
      setIsPreparingImage(true);
      setLocalError('');
      const croppedFile = await createCroppedImage(sourceImageUrl, croppedAreaPixels);
      await onUpload(croppedFile);
      setIsDialogOpen(false);
      resetCropState();
    } catch (cropError) {
      setLocalError(cropError instanceof Error ? cropError.message : 'Failed to prepare the avatar image.');
    } finally {
      setIsPreparingImage(false);
    }
  }

  function handleDialogOpenChange(nextOpen: boolean): void {
    setIsDialogOpen(nextOpen);

    if (!nextOpen) {
      resetCropState();
    }
  }

  return (
    <>
      <section className="card space-y-5 p-5">
        <div className="flex items-center justify-between gap-3">
          <div>
            <h2 className="text-sm font-bold uppercase tracking-[1.6px]" style={{ color: 'var(--text-secondary)' }}>
              Profile avatar
            </h2>
            <p className="mt-2 text-sm leading-6" style={{ color: 'var(--text-muted)' }}>
              Upload a square avatar. The image is cropped before being stored in R2.
            </p>
          </div>
          <div
            className="flex h-9 w-9 items-center justify-center rounded-xl"
            style={{
              background: 'color-mix(in srgb, var(--accent-primary) 14%, transparent)',
              color: 'var(--accent-primary)',
            }}
          >
            <Camera size={18} />
          </div>
        </div>

        <div className="flex flex-col gap-4 sm:flex-row sm:items-center">
          {hasPhoto ? (
            <img
              src={photoUrl ?? undefined}
              alt={`${name} ${lastName ?? ''}`.trim()}
              className="h-24 w-24 rounded-2xl object-cover"
              style={{ border: '2px solid var(--accent-primary)' }}
            />
          ) : (
            <div
              className="flex h-24 w-24 items-center justify-center rounded-2xl"
              style={{
                background: 'var(--grad-primary)',
                color: 'var(--color-white)',
              }}
            >
              <span className="text-3xl font-black">{initials}</span>
            </div>
          )}

          <div className="min-w-0 flex-1 space-y-3">
            <div
              {...getRootProps()}
              className="rounded-2xl border border-dashed p-4 transition-all duration-200"
              style={{
                background: isDragActive ? 'color-mix(in srgb, var(--accent-primary) 8%, var(--bg-card))' : 'var(--bg-surface)',
                borderColor: isDragActive ? 'var(--accent-primary)' : 'var(--border-default)',
              }}
            >
              <input {...getInputProps()} aria-label="Upload profile avatar" />
              <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                  <p className="text-sm font-semibold" style={{ color: 'var(--text-primary)' }}>
                    Drag and drop a JPG, PNG, or WEBP image
                  </p>
                  <p className="mt-1 text-xs" style={{ color: 'var(--text-secondary)' }}>
                    Max size: 2 MB. The final image is cropped to a square avatar.
                  </p>
                </div>
                <button
                  type="button"
                  className="btn-primary px-4 py-2 text-sm font-semibold"
                  onClick={open}
                  disabled={isUploading}
                >
                  <span className="inline-flex items-center gap-2">
                    <UploadCloud size={16} />
                    Choose image
                  </span>
                </button>
              </div>
            </div>

            <div className="flex flex-wrap gap-3">
              {hasPhoto ? (
                <button
                  type="button"
                  className="btn-ghost px-4 py-2 text-sm font-semibold"
                  onClick={() => {
                    void Promise.resolve(onRemove()).catch(() => undefined);
                  }}
                  disabled={isUploading}
                >
                  <span className="inline-flex items-center gap-2">
                    <Trash2 size={16} />
                    Remove avatar
                  </span>
                </button>
              ) : null}
            </div>

            {typeof uploadProgress === 'number' ? (
              <div className="space-y-2">
                <div className="h-2 w-full overflow-hidden rounded-full" style={{ background: 'var(--bg-surface)' }}>
                  <div
                    className="h-full rounded-full transition-all duration-200"
                    style={{
                      width: `${uploadProgress}%`,
                      background: 'var(--accent-primary)',
                    }}
                  />
                </div>
                <p className="text-xs font-medium" style={{ color: 'var(--text-secondary)' }}>
                  Uploading avatar: {uploadProgress}%
                </p>
              </div>
            ) : null}

            {effectiveError ? (
              <p className="text-sm font-medium" style={{ color: 'var(--accent-error)' }}>
                {effectiveError}
              </p>
            ) : null}
          </div>
        </div>
      </section>

      <Dialog open={isDialogOpen} onOpenChange={handleDialogOpenChange}>
        <DialogContent
          className="max-w-3xl p-0"
          showCloseButton={!isPreparingImage && !isUploading}
          style={{
            background: 'var(--bg-card)',
            borderColor: 'var(--border-default)',
          }}
        >
          <DialogHeader className="px-6 pt-6">
            <DialogTitle style={{ color: 'var(--text-primary)' }}>Crop avatar</DialogTitle>
            <DialogDescription style={{ color: 'var(--text-secondary)' }}>
              Adjust the image and confirm the square crop before uploading it.
            </DialogDescription>
          </DialogHeader>

          <div className="px-6">
            <div
              className="relative overflow-hidden rounded-2xl"
              style={{
                height: '22rem',
                background: 'var(--bg-surface)',
                border: '1px solid var(--border-default)',
              }}
            >
              {sourceImageUrl ? (
                <Cropper
                  image={sourceImageUrl}
                  crop={crop}
                  zoom={zoom}
                  aspect={1}
                  onCropChange={setCrop}
                  onCropComplete={onCropComplete}
                  onZoomChange={setZoom}
                  restrictPosition
                />
              ) : null}
            </div>

            <div className="mt-4 space-y-3">
              <label className="flex flex-col gap-2">
                <span className="text-xs font-semibold uppercase tracking-[1.6px]" style={{ color: 'var(--text-secondary)' }}>
                  Zoom
                </span>
                <input
                  type="range"
                  min={1}
                  max={3}
                  step={0.1}
                  value={zoom}
                  onChange={(event) => setZoom(Number(event.target.value))}
                />
              </label>
            </div>
          </div>

          <DialogFooter className="px-6 pb-6">
            <button
              type="button"
              className="btn-ghost px-4 py-2 text-sm font-semibold"
              onClick={() => handleDialogOpenChange(false)}
              disabled={isPreparingImage || isUploading}
            >
              Cancel
            </button>
            <button
              type="button"
              className="btn-primary px-4 py-2 text-sm font-semibold"
              onClick={() => {
                void handleConfirmCrop();
              }}
              disabled={isPreparingImage || isUploading}
              autoFocus
            >
              <span className="inline-flex items-center gap-2">
                {isPreparingImage || isUploading ? <LoaderCircle size={16} className="animate-spin" /> : <UploadCloud size={16} />}
                {isPreparingImage || isUploading ? 'Uploading...' : 'Apply crop'}
              </span>
            </button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </>
  );
}
