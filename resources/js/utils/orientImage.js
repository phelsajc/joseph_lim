/**
 * Bake EXIF orientation into JPEG pixels so uploads display upright everywhere.
 * Prefers raw sensor pixels via createImageBitmap(..., { imageOrientation: 'none' }).
 */

function getJpegExifOrientation(arrayBuffer) {
  const view = new DataView(arrayBuffer);
  if (view.byteLength < 4 || view.getUint16(0, false) !== 0xffd8) {
    return 1;
  }
  let offset = 2;
  const length = view.byteLength;
  while (offset + 4 < length) {
    const marker = view.getUint16(offset, false);
    if (marker === 0xffe1) {
      const exifStart = offset + 4;
      if (exifStart + 6 > length) {
        return 1;
      }
      if (view.getUint32(exifStart, false) !== 0x45786966) {
        return 1;
      }
      const tiffOffset = exifStart + 6;
      const littleEndian = view.getUint16(tiffOffset, false) === 0x4949;
      const getUint16 = (o) => view.getUint16(o, littleEndian);
      const getUint32 = (o) => view.getUint32(o, littleEndian);
      const ifdOffset = tiffOffset + getUint32(tiffOffset + 4);
      if (ifdOffset + 2 > length) {
        return 1;
      }
      const entries = getUint16(ifdOffset);
      for (let i = 0; i < entries; i++) {
        const entryOffset = ifdOffset + 2 + i * 12;
        if (entryOffset + 12 > length) {
          break;
        }
        if (getUint16(entryOffset) === 0x0112) {
          return getUint16(entryOffset + 8);
        }
      }
      return 1;
    }
    if ((marker & 0xff00) !== 0xff00) {
      break;
    }
    const segmentLength = view.getUint16(offset + 2, false);
    if (segmentLength < 2) {
      break;
    }
    offset += 2 + segmentLength;
  }
  return 1;
}

async function readExifOrientation(file) {
  const isJpeg =
    file.type === 'image/jpeg' ||
    file.type === 'image/jpg' ||
    /\.jpe?g$/i.test(file.name || '');
  if (!isJpeg || typeof file.arrayBuffer !== 'function') {
    return 1;
  }
  try {
    const buf = await file.arrayBuffer();
    return getJpegExifOrientation(buf);
  } catch (e) {
    return 1;
  }
}

function scaleDimensions(width, height, maxWidth, maxHeight) {
  let w = width;
  let h = height;
  if (w > maxWidth || h > maxHeight) {
    const ratio = Math.min(maxWidth / w, maxHeight / h);
    w = Math.max(1, Math.round(w * ratio));
    h = Math.max(1, Math.round(h * ratio));
  }
  return { width: w, height: h };
}

/**
 * Draw raw (unoriented) image onto canvas with EXIF orientation applied.
 * srcW/srcH are the raw pixel dimensions of the source.
 */
function drawOriented(ctx, source, orientation, srcW, srcH) {
  switch (orientation) {
    case 2:
      ctx.transform(-1, 0, 0, 1, srcW, 0);
      break;
    case 3:
      ctx.transform(-1, 0, 0, -1, srcW, srcH);
      break;
    case 4:
      ctx.transform(1, 0, 0, -1, 0, srcH);
      break;
    case 5:
      ctx.transform(0, 1, 1, 0, 0, 0);
      break;
    case 6:
      ctx.transform(0, 1, -1, 0, srcH, 0);
      break;
    case 7:
      ctx.transform(0, -1, -1, 0, srcH, srcW);
      break;
    case 8:
      ctx.transform(0, -1, 1, 0, 0, srcW);
      break;
    default:
      break;
  }
  ctx.drawImage(source, 0, 0, srcW, srcH);
}

function canvasToJpegFile(canvas, fileName, quality) {
  return new Promise((resolve, reject) => {
    canvas.toBlob(
      (blob) => {
        if (!blob) {
          reject(new Error('Canvas toBlob failed'));
          return;
        }
        const name = (fileName || 'image.jpg').replace(/\.[^.]+$/, '.jpg');
        resolve(
          new File([blob], name, {
            type: 'image/jpeg',
            lastModified: Date.now(),
          })
        );
      },
      'image/jpeg',
      quality
    );
  });
}

function loadHtmlImage(file) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    const objectUrl = URL.createObjectURL(file);
    img.onload = () => {
      URL.revokeObjectURL(objectUrl);
      resolve(img);
    };
    img.onerror = () => {
      URL.revokeObjectURL(objectUrl);
      reject(new Error('Image load failed'));
    };
    img.src = objectUrl;
  });
}

/**
 * @returns {Promise<{ source: ImageBitmap|HTMLImageElement, isRaw: boolean, close: boolean }>}
 */
async function decodeSource(file) {
  if (typeof createImageBitmap !== 'undefined') {
    try {
      const bitmap = await createImageBitmap(file, { imageOrientation: 'none' });
      return { source: bitmap, isRaw: true, close: true };
    } catch (e) {
      try {
        const bitmap = await createImageBitmap(file);
        // Without 'none', decoder may already have applied EXIF.
        return { source: bitmap, isRaw: false, close: true };
      } catch (e2) {
        /* fall through */
      }
    }
  }
  const img = await loadHtmlImage(file);
  return { source: img, isRaw: false, close: false };
}

/**
 * @param {File|Blob} file
 * @param {{ quality?: number, maxWidth?: number, maxHeight?: number }} options
 * @returns {Promise<File>}
 */
export async function orientAndCompressImage(file, options = {}) {
  const quality = options.quality != null ? options.quality : 0.8;
  const maxWidth = options.maxWidth != null ? options.maxWidth : 1920;
  const maxHeight = options.maxHeight != null ? options.maxHeight : 1080;

  let orientation = await readExifOrientation(file);
  const { source, isRaw, close } = await decodeSource(file);

  // If pixels were already auto-oriented by the decoder, do not transform again.
  if (!isRaw && orientation >= 5 && orientation <= 8 && source.width < source.height) {
    orientation = 1;
  }

  const srcW = source.width;
  const srcH = source.height;
  const swap = orientation >= 5 && orientation <= 8;
  const orientedW = swap ? srcH : srcW;
  const orientedH = swap ? srcW : srcH;
  const { width: outW, height: outH } = scaleDimensions(
    orientedW,
    orientedH,
    maxWidth,
    maxHeight
  );

  const canvas = document.createElement('canvas');
  canvas.width = outW;
  canvas.height = outH;
  const ctx = canvas.getContext('2d');

  if (orientation === 1) {
    ctx.drawImage(source, 0, 0, outW, outH);
  } else {
    const tmp = document.createElement('canvas');
    tmp.width = orientedW;
    tmp.height = orientedH;
    const tctx = tmp.getContext('2d');
    drawOriented(tctx, source, orientation, srcW, srcH);
    ctx.drawImage(tmp, 0, 0, outW, outH);
  }

  if (close && typeof source.close === 'function') {
    try {
      source.close();
    } catch (e) {
      /* ignore */
    }
  }

  return canvasToJpegFile(canvas, file.name, quality);
}

export default orientAndCompressImage;
