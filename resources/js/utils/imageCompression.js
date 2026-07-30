function canvasToJpegFile(canvas, fileName, quality) {
  return new Promise((resolve, reject) => {
    canvas.toBlob(
      (blob) => {
        if (!blob) {
          reject(new Error('Failed to encode image'));
          return;
        }
        const baseName = fileName.replace(/\.[^.]+$/, '') || 'image';
        resolve(new File([blob], `${baseName}.jpg`, {
          type: 'image/jpeg',
          lastModified: Date.now(),
        }));
      },
      'image/jpeg',
      quality
    );
  });
}

function loadImageSource(file) {
  if (typeof createImageBitmap === 'function') {
    return createImageBitmap(file, { imageOrientation: 'from-image' })
      .catch(() => loadImageElement(file));
  }

  return loadImageElement(file);
}

function loadImageElement(file) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    const objectUrl = URL.createObjectURL(file);

    img.onload = () => {
      URL.revokeObjectURL(objectUrl);
      resolve(img);
    };
    img.onerror = () => {
      URL.revokeObjectURL(objectUrl);
      reject(new Error('Failed to load image'));
    };
    img.src = objectUrl;
  });
}

function drawToCanvas(source, maxWidth, maxHeight) {
  let width = source.width;
  let height = source.height;

  if (width > maxWidth || height > maxHeight) {
    const ratio = Math.min(maxWidth / width, maxHeight / height);
    width *= ratio;
    height *= ratio;
  }

  const canvas = document.createElement('canvas');
  canvas.width = Math.max(1, Math.round(width));
  canvas.height = Math.max(1, Math.round(height));

  const ctx = canvas.getContext('2d');
  ctx.drawImage(source, 0, 0, canvas.width, canvas.height);

  if (typeof source.close === 'function') {
    source.close();
  }

  return canvas;
}

/**
 * Compress an image file with EXIF orientation applied.
 */
export function compressImage(file, quality = 0.8, maxWidth = 1920, maxHeight = 1080) {
  return loadImageSource(file)
    .then((source) => drawToCanvas(source, maxWidth, maxHeight))
    .then((canvas) => canvasToJpegFile(canvas, file.name, quality));
}

/**
 * Re-encode an image with correct orientation but without resizing.
 */
export function normalizeImageOrientation(file, quality = 0.92) {
  return loadImageSource(file)
    .then((source) => drawToCanvas(source, source.width, source.height))
    .then((canvas) => canvasToJpegFile(canvas, file.name, quality));
}

/**
 * Normalize orientation for image uploads; pass through non-images unchanged.
 */
export async function prepareImageForUpload(file, options = {}) {
  const mime = (file.type || '').toLowerCase();
  const isImage = mime.startsWith('image/') && mime !== 'image/gif' && mime !== 'image/svg+xml';

  if (!isImage) {
    return file;
  }

  try {
    if (options.compress) {
      return await compressImage(
        file,
        options.quality ?? 0.8,
        options.maxWidth ?? 1920,
        options.maxHeight ?? 1080
      );
    }

    return await normalizeImageOrientation(file, options.quality ?? 0.92);
  } catch (error) {
    return file;
  }
}
