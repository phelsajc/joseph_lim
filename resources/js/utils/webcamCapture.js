export function isWebcamSupported() {
  return !!(
    typeof navigator !== 'undefined' &&
    navigator.mediaDevices &&
    typeof navigator.mediaDevices.getUserMedia === 'function'
  );
}

export function startWebcamStream(videoEl) {
  if (!isWebcamSupported()) {
    return Promise.reject(new Error('Webcam is not supported in this browser.'));
  }

  return navigator.mediaDevices.getUserMedia({
    video: { facingMode: 'user' },
    audio: false,
  }).then((stream) => {
    videoEl.srcObject = stream;
    return videoEl.play().then(() => stream);
  });
}

export function stopWebcamStream(stream) {
  if (!stream) {
    return;
  }

  stream.getTracks().forEach((track) => track.stop());
}

export function captureVideoFrameAsFile(videoEl, fileName = 'profile-photo.jpg', quality = 0.92) {
  const width = videoEl.videoWidth;
  const height = videoEl.videoHeight;

  if (!width || !height) {
    return Promise.reject(new Error('Camera is not ready yet.'));
  }

  const canvas = document.createElement('canvas');
  canvas.width = width;
  canvas.height = height;

  const ctx = canvas.getContext('2d');
  ctx.drawImage(videoEl, 0, 0, width, height);

  return new Promise((resolve, reject) => {
    canvas.toBlob(
      (blob) => {
        if (!blob) {
          reject(new Error('Failed to capture photo.'));
          return;
        }

        const baseName = fileName.replace(/\.[^.]+$/, '') || 'profile-photo';
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

export function getWebcamErrorMessage(error) {
  if (!error) {
    return 'Unable to access the camera.';
  }

  const name = error.name || '';
  if (name === 'NotAllowedError' || name === 'PermissionDeniedError') {
    return 'Camera access was denied. Please allow camera permission and try again.';
  }
  if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
    return 'No camera was found on this device.';
  }
  if (name === 'NotReadableError' || name === 'TrackStartError') {
    return 'The camera is already in use by another application.';
  }
  if (name === 'SecurityError') {
    return 'Camera access requires a secure connection (HTTPS).';
  }

  return error.message || 'Unable to access the camera.';
}
