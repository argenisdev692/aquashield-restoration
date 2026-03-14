type PixelCrop = {
  x: number;
  y: number;
  width: number;
  height: number;
};

function loadImage(source: string): Promise<HTMLImageElement> {
  return new Promise((resolve, reject) => {
    const image = new Image();
    image.onload = () => resolve(image);
    image.onerror = () => reject(new Error('Failed to load the selected image.'));
    image.src = source;
  });
}

export async function createCroppedImage(source: string, crop: PixelCrop): Promise<File> {
  const image = await loadImage(source);
  const canvas = document.createElement('canvas');
  const context = canvas.getContext('2d');

  if (context === null) {
    throw new Error('Your browser does not support image cropping.');
  }

  canvas.width = crop.width;
  canvas.height = crop.height;

  context.drawImage(
    image,
    crop.x,
    crop.y,
    crop.width,
    crop.height,
    0,
    0,
    crop.width,
    crop.height,
  );

  const blob = await new Promise<Blob>((resolve, reject) => {
    canvas.toBlob((result) => {
      if (result === null) {
        reject(new Error('Failed to generate the cropped image.'));
        return;
      }

      resolve(result);
    }, 'image/jpeg', 0.92);
  });

  return new File([blob], `avatar-${Date.now()}.jpg`, {
    type: 'image/jpeg',
    lastModified: Date.now(),
  });
}
