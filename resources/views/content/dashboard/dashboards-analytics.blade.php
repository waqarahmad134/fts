@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - FTS')

@section('page-script')
<!-- <script src="{{asset('public/assets/js/dashboards-analytics.js')}}"></script> -->
<!-- HTML5 QR Code Scanner Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endsection

@section('content')

<div class="row">
  <div class="col-lg-12 mb-4 order-0">
    <div class="card">
      <div class="d-flex align-items-end row">
        <div class="col-sm-7">
          <div class="card-body">
              @php
                  $hour = now()->format('H'); // 24-hour format
                  $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                  $userRole = auth()->user()->role->name ?? 'User';
              @endphp
              <h5 class="card-title text-primary">{{ $greeting }}, {{ $userRole }}! 🎉</h5>
              <p class="mb-2">Welcome <span class="fw-medium">{{ auth()->user()->name }}</span></p>
              <p class="mb-4">You have created <span class="fw-medium">{{ $todayFilesCount }}</span> files today.</p>
              <div class="d-flex gap-2">
                <a href="{{ route('files.index') }}" class="btn btn-sm btn-outline-primary">View Files</a>
                <button type="button" class="btn btn-sm btn-success" onclick="openQrScanner()" title="Scan QR Code to Receive File">
                  <i class="bx bx-scan me-1"></i> Scan QR Code
                </button>
              </div>
            </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="{{asset('public/assets/img/illustrations/man-with-laptop-light.png')}}" height="140" alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png" data-app-light-img="illustrations/man-with-laptop-light.png">
          </div>
        </div>
      </div>
    </div>
  </div>
 
 
  <div class="col-12 order-3 order-md-2">
    <div class="row">
      <div class="col mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('public/assets/img/icons/unicons/chart-success.png')}}" alt="chart success" class="rounded">
              </div>
            </div>
            <span class="fw-semibold d-block mb-1">Total Files</span>
            <h3 class="card-title mb-2">{{$totalFiles}}</h3>
            <!-- <small class="text-success fw-semibold"><i class='bx bx-up-arrow-alt'></i> +72.80%</small> -->
          </div>
        </div>
      </div>
      <div class="col mb-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('public/assets/img/icons/unicons/wallet-info.png')}}" alt="Credit Card" class="rounded">
              </div>
            </div>
            <span>Pending Files</span>
            <h3 class="card-title text-nowrap mb-1">{{$pendingFiles}}</h3>
          </div>
        </div>
      </div>
      <div class="col mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('public/assets/img/icons/unicons/paypal.png')}}" alt="Credit Card" class="rounded">
              </div>
            </div>
            <span class="d-block mb-1">Closed Files</span>
            <h3 class="card-title text-nowrap mb-2">{{$closedFiles}}</h3>
          </div>
        </div>
      </div>
      <div class="col mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('public/assets/img/icons/unicons/cc-primary.png')}}" alt="Credit Card" class="rounded">
              </div>
            </div>
            <span class="fw-semibold d-block mb-1">Reopened Files</span>
            <h3 class="card-title mb-2">{{$reopenedFiles}}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- QR Scanner Modal -->
<div class="modal fade" id="qrScannerModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Scan QR Code to Receive File</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <!-- Manual Upload Option -->
        <div class="mb-3">
          <label for="qr-image-upload" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-upload me-1"></i> Upload QR Code Image
          </label>
          <input type="file" id="qr-image-upload" accept="image/*" style="display: none;" />
        </div>
        <div class="text-muted small mb-3">OR</div>
        <p class="mb-3">Position the QR code within the camera frame</p>
        <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
        <div id="qr-reader-results" class="mt-3"></div>
        <div id="scanning-status" class="alert alert-info mt-3" style="display: none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary btn-sm" id="switch-camera-btn" onclick="switchCamera()" style="display: none;">
          <i class="bx bx-camera me-1"></i> Switch Camera
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="stopQrScanner()">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// Global variables for QR scanner
let html5QrCode = null;
let isScanning = false;
let availableCameras = [];
let currentCameraIndex = 0;
let currentCameraId = null;
let currentFacingMode = 'user'; // 'user' = front, 'environment' = back

// Wait for jQuery to be loaded before executing jQuery-dependent code
(function() {
  'use strict';
  
  function waitForJQuery(callback) {
    if (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') {
      callback(window.jQuery || window.$);
    } else {
      setTimeout(function() {
        waitForJQuery(callback);
      }, 50);
    }
  }
  
  // Initialize when DOM and jQuery are ready
  waitForJQuery(function($) {
    // Make $ available globally
    window.$ = window.$ || $;
    window.jQuery = window.jQuery || $;
  });
})();

// QR Scanner functions
window.openQrScanner = function() {
  // Stop any existing scanner
  stopQrScanner();
  
  // Use jQuery if available, otherwise use vanilla JS
  const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
    ? (window.jQuery || window.$) 
    : null;
  
  if ($) {
    $('#qrScannerModal').modal('show');
    $('#qrScannerModal').off('shown.bs.modal').on('shown.bs.modal', function() {
      startQrScanner();
    });
    $('#qrScannerModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
      stopQrScanner();
    });
  } else {
    // Fallback for vanilla JS
    const modalEl = document.getElementById('qrScannerModal');
    if (modalEl) {
      const modal = new bootstrap.Modal(modalEl);
      modal.show();
      modalEl.addEventListener('shown.bs.modal', function() {
        startQrScanner();
      }, { once: true });
      modalEl.addEventListener('hidden.bs.modal', function() {
        stopQrScanner();
      }, { once: true });
    }
  }
};

window.startQrScanner = async function(cameraIdToUse = null, facingModeToUse = null) {
  if (isScanning && !cameraIdToUse && !facingModeToUse) {
    return;
  }
  
  const readerElementId = "qr-reader";
  
  // If switching cameras, stop current scanner first
  if (isScanning && html5QrCode) {
    try {
      await html5QrCode.stop();
      html5QrCode.clear();
    } catch (e) {
      console.warn('Error stopping scanner:', e);
    }
    isScanning = false;
  }
  
  // Clear previous content
  const readerEl = document.getElementById(readerElementId);
  if (!readerEl) return;
  readerEl.innerHTML = '';
  
  if (typeof Html5Qrcode === 'undefined') {
    updateScanningStatus('<span class="text-danger">QR Scanner library not loaded. Please refresh the page.</span>');
    return;
  }
  
  html5QrCode = new Html5Qrcode(readerElementId);
  
  // Get available cameras if not already loaded
  if (availableCameras.length === 0) {
    try {
      availableCameras = await Html5Qrcode.getCameras();
      console.log('Available cameras:', availableCameras.map(d => d.label));
      
      // Show switch button if more than one camera
      const switchBtn = document.getElementById('switch-camera-btn');
      if (switchBtn && availableCameras.length > 1) {
        switchBtn.style.display = 'inline-block';
      }
    } catch (err) {
      console.warn('Could not enumerate cameras:', err);
      availableCameras = [];
    }
  }
  
  // Determine which camera to use
  let useCameraId = cameraIdToUse;
  let useFacingMode = facingModeToUse;
  
  if (!useCameraId && !useFacingMode) {
    // First time starting - determine initial camera
    if (availableCameras.length > 0) {
      // Try to find front camera first
      const frontCamera = availableCameras.find(device => 
        device.label.toLowerCase().includes('front') || 
        device.label.toLowerCase().includes('facing: user') ||
        device.label.toLowerCase().includes('integrated')
      );
      
      useCameraId = frontCamera ? frontCamera.id : availableCameras[0].id;
      currentCameraIndex = frontCamera ? availableCameras.indexOf(frontCamera) : 0;
      currentCameraId = useCameraId;
      currentFacingMode = null;
    } else {
      // No cameras enumerated, use facingMode
      useFacingMode = currentFacingMode || 'user';
      currentCameraId = null;
    }
  } else {
    // Switching cameras
    if (useCameraId) {
      currentCameraId = useCameraId;
      currentFacingMode = null;
      currentCameraIndex = availableCameras.findIndex(cam => cam.id === useCameraId);
    } else if (useFacingMode) {
      currentFacingMode = useFacingMode;
      currentCameraId = null;
    }
  }
  
  // Configuration for scanning
  const config = {
    fps: 10,
    qrbox: { width: 250, height: 250 },
    aspectRatio: 1.0
  };
  
  try {
    let startPromise = null;
    
    if (useCameraId) {
      console.log('Starting with camera deviceId:', useCameraId);
      startPromise = html5QrCode.start(
        { deviceId: { exact: useCameraId } },
        config,
        onScanSuccess,
        onScanError
      );
    } else if (useFacingMode) {
      console.log('Starting with facingMode:', useFacingMode);
      startPromise = html5QrCode.start(
        { facingMode: useFacingMode },
        config,
        onScanSuccess,
        onScanError
      );
    } else {
      throw new Error('No camera selected');
    }
    
    await startPromise;
    isScanning = true;
    const selectedCamera = availableCameras.find(c => c.id === useCameraId);
    const cameraLabel = selectedCamera
      ? selectedCamera.label
      : (useFacingMode === 'user' ? 'Front Camera' : 'Back Camera');
    updateScanningStatus('<i class="bx bx-camera me-1"></i> Camera started (' + cameraLabel + '). Point at QR code to scan.');
  } catch (err) {
    console.error('Failed to start QR scanner:', err);
    let errorMsg = 'Failed to start camera. ';
    
    const errMsg = err && err.message ? err.message : '';
    const errName = err && err.name ? err.name : '';
    
    if (errName === 'NotAllowedError' || errMsg.includes('Permission denied')) {
      errorMsg += 'Camera permission denied. Please allow camera access in your browser settings and try again.';
    } else if (errName === 'NotFoundError' || errMsg.includes('Requested device not found')) {
      errorMsg += 'No camera found. Please check if a camera is connected and try again.';
    } else if (errName === 'NotReadableError' || errMsg.includes('Could not start video source')) {
      errorMsg += 'Camera is being used by another application. Please close other apps using the camera and try again.';
    } else {
      errorMsg += 'Please check camera permissions and try again.';
    }
    
    updateScanningStatus('<span class="text-danger">' + errorMsg + '</span>');
    isScanning = false;
  }
};

// Switch between cameras
window.switchCamera = async function() {
  if (!html5QrCode || !isScanning) {
    return;
  }
  
  try {
    if (availableCameras.length > 1) {
      // Switch to next camera in list
      currentCameraIndex = (currentCameraIndex + 1) % availableCameras.length;
      const nextCamera = availableCameras[currentCameraIndex];
      
      updateScanningStatus('<i class="bx bx-loader-alt bx-spin me-1"></i> Switching camera...');
      await startQrScanner(nextCamera.id, null);
    } else {
      // Switch between front and back using facingMode
      currentFacingMode = currentFacingMode === 'user' ? 'environment' : 'user';
      updateScanningStatus('<i class="bx bx-loader-alt bx-spin me-1"></i> Switching camera...');
      await startQrScanner(null, currentFacingMode);
    }
  } catch (err) {
    console.error('Failed to switch camera:', err);
    updateScanningStatus('<span class="text-danger">Failed to switch camera. Please try again.</span>');
  }
};

// Alias for manual upload compatibility
window.handleScannedQrCode = function(decodedText) {
  onScanSuccess(decodedText, null);
};

function onScanSuccess(decodedText, decodedResult) {
  // Stop scanning immediately after successful scan
  stopQrScanner();
  
  // Extract file ID from the scanned URL
  // Expected format: http://localhost/fts/file-movements/scan/{fileId}
  const urlPattern = /\/file-movements\/scan\/(\d+)/;
  const match = decodedText.match(urlPattern);
  
  if (!match || !match[1]) {
    const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
      ? (window.jQuery || window.$) 
      : null;
    const statusEl = document.getElementById('scanning-status');
    const modalEl = document.getElementById('qrScannerModal');
    
    if ($ && statusEl) {
      $(statusEl).html('<span class="text-danger">Invalid QR code format. Please scan a valid file QR code.</span>').show();
      setTimeout(() => {
        $('#qrScannerModal').modal('hide');
      }, 2000);
    } else if (statusEl) {
      statusEl.innerHTML = '<span class="text-danger">Invalid QR code format. Please scan a valid file QR code.</span>';
      statusEl.style.display = 'block';
      setTimeout(() => {
        if (modalEl) {
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();
        }
      }, 2000);
    }
    return;
  }
  
  const fileId = match[1];
  
  // Show processing status
  const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
    ? (window.jQuery || window.$) 
    : null;
  const statusEl = document.getElementById('scanning-status');
  
  if ($ && statusEl) {
    $(statusEl).html('<span class="text-info"><i class="bx bx-loader-alt bx-spin me-1"></i> Processing file transfer...</span>').show();
  } else if (statusEl) {
    statusEl.innerHTML = '<span class="text-info"><i class="bx bx-loader-alt bx-spin me-1"></i> Processing file transfer...</span>';
    statusEl.style.display = 'block';
  }
  
  // Make AJAX call to scan endpoint
  const scanUrl = "{{ url('/file-movements/scan') }}/" + fileId;
  const tokenMeta = document.querySelector('meta[name="csrf-token"]');
  const token = tokenMeta ? tokenMeta.getAttribute('content') : '';
  
  if (typeof fetch !== 'undefined') {
    fetch(scanUrl, {
      method: 'GET',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': token || '',
        'Accept': 'application/json'
      },
      credentials: 'same-origin'
    })
    .then(response => {
      if (!response.ok) {
        return response.json().then(errData => {
          throw new Error(errData.error || errData.message || 'Server error');
        }).catch(() => {
          throw new Error('HTTP error ' + response.status);
        });
      }
      return response.json();
    })
    .then(data => {
      console.log('QR Scan response:', data);
      const modalEl = document.getElementById('qrScannerModal');
      const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
        ? (window.jQuery || window.$) 
        : null;
      
      if (data.success || data.message) {
        const message = data.message || 'File received successfully!';
        updateScanningStatus('<span class="text-success"><i class="bx bx-check-circle me-1"></i> ' + message + ' Redirecting...</span>');
        
        setTimeout(() => {
          if ($ && modalEl) {
            $('#qrScannerModal').modal('hide');
          } else if (modalEl) {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
          }
          window.location.href = '/files';
        }, 1500);
      } else if (data.error) {
        throw new Error(data.error);
      } else {
        throw new Error('Unknown response format');
      }
    })
    .catch(error => {
      console.error('QR Scan error:', error);
      const errorMsg = error.message || 'Network error. Please try again.';
      updateScanningStatus('<span class="text-danger"><i class="bx bx-error me-1"></i> ' + errorMsg + '</span>');
      
      setTimeout(() => {
        const modalEl = document.getElementById('qrScannerModal');
        if (modalEl) {
          const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
            ? (window.jQuery || window.$) 
            : null;
          if ($) {
            $('#qrScannerModal').modal('hide');
          } else {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
          }
        }
      }, 3000);
    });
  }
}

function onScanError(errorMessage) {
  // Ignore scanning errors - they're common and expected
  // Only log if it's a critical error
  if (errorMessage && !errorMessage.includes('NotFoundException') && !errorMessage.includes('No MultiFormat Readers')) {
    console.debug('QR Scan error:', errorMessage);
  }
}

function updateScanningStatus(message) {
  const statusEl = document.getElementById('scanning-status');
  if (!statusEl) return;
  
  const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
    ? (window.jQuery || window.$) 
    : null;
  
  if ($) {
    $(statusEl).html(message).show();
  } else {
    statusEl.innerHTML = message;
    statusEl.style.display = message ? 'block' : 'none';
  }
}

function updateScanningStatus(message) {
  const statusEl = document.getElementById('scanning-status');
  if (!statusEl) return;
  
  const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
    ? (window.jQuery || window.$) 
    : null;
  
  if ($) {
    $(statusEl).html(message).show();
  } else {
    statusEl.innerHTML = message;
    statusEl.style.display = message ? 'block' : 'none';
  }
}

window.stopQrScanner = function() {
  if (html5QrCode && isScanning) {
    html5QrCode.stop().then(() => {
      html5QrCode.clear();
      html5QrCode = null;
      isScanning = false;
      updateScanningStatus('');
    }).catch((err) => {
      console.error('Error stopping scanner:', err);
      html5QrCode = null;
      isScanning = false;
      updateScanningStatus('');
    });
  } else {
    // Clear the reader element if scanner wasn't properly initialized
    const readerEl = document.getElementById('qr-reader');
    if (readerEl) {
      readerEl.innerHTML = '';
    }
    isScanning = false;
    updateScanningStatus('');
  }
  
  // Also handle modal close event
  const modalEl = document.getElementById('qrScannerModal');
  if (modalEl) {
    const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
      ? (window.jQuery || window.$) 
      : null;
    
    if ($) {
      $('#qrScannerModal').off('hidden.bs.modal');
    } else {
      modalEl.removeEventListener('hidden.bs.modal', stopQrScanner);
    }
  }
};

// Manual QR code image upload handler
document.addEventListener('DOMContentLoaded', function() {
  const uploadInput = document.getElementById('qr-image-upload');
  if (uploadInput) {
    uploadInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (!file) return;
      
      if (!file.type.startsWith('image/')) {
        updateScanningStatus('<span class="text-danger">Please select an image file (PNG, JPG, etc.)</span>');
        return;
      }
      
      // Use Html5Qrcode to scan from image file
      if (typeof Html5Qrcode === 'undefined') {
        updateScanningStatus('<span class="text-danger">QR Scanner library not loaded. Please refresh the page.</span>');
        return;
      }
      
      // Stop any active camera scanning
      if (isScanning && html5QrCode) {
        stopQrScanner();
      }
      
      // Create a temporary Html5Qrcode instance for file scanning
      const fileBasedInstance = new Html5Qrcode("qr-reader");
      
      updateScanningStatus('<i class="bx bx-loader-alt bx-spin me-1"></i> Scanning uploaded image...');
      
      // Pass the File object directly (not data URL)
      fileBasedInstance.scanFile(file, true)
        .then(decodedText => {
          // Successfully decoded
          updateScanningStatus('<span class="text-success"><i class="bx bx-check-circle me-1"></i> QR Code detected! Processing...</span>');
          fileBasedInstance.clear();
          handleScannedQrCode(decodedText);
          // Clear the input so user can upload again
          uploadInput.value = '';
        })
        .catch(err => {
          console.error('Error scanning file:', err);
          let errorMsg = 'Failed to read QR code from image. ';
          if (err && err.message && err.message.includes('No QR code')) {
            errorMsg += 'No QR code found in the image. Please ensure the image contains a valid QR code.';
          } else {
            errorMsg += 'Please try again with a clearer image.';
          }
          updateScanningStatus('<span class="text-danger">' + errorMsg + '</span>');
          fileBasedInstance.clear();
          
          // Clear the input so user can try again
          uploadInput.value = '';
        });
    });
  }
  
  // Handle modal close to reset camera list for next time
  const modalEl = document.getElementById('qrScannerModal');
  if (modalEl) {
    modalEl.addEventListener('hidden.bs.modal', function() {
      availableCameras = [];
      currentCameraIndex = 0;
    });
  }
});
</script>

@endsection
