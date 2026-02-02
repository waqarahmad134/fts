@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - FTS')

@section('page-script')
<!-- <script src="{{asset('public/assets/js/dashboards-analytics.js')}}"></script> -->
<!-- HTML5 QR Code Scanner Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endsection

@section('content')

<style>
  /* Mobile: prevent cards and content from stretching */
  @media (max-width: 575.98px) {
    .dashboard-welcome .card { overflow: hidden; }
    .dashboard-welcome .card-body { min-width: 0; }
    .dashboard-stats .card { min-height: 0; height: auto !important; }
    .dashboard-stats .card-body { min-width: 0; overflow: hidden; }
  }
</style>

<div class="row">
  <div class="col-12 col-lg-12 mb-4 order-0 dashboard-welcome">
    <div class="card">
      <div class="row g-3 align-items-end">
        <div class="col-12 col-sm-7 order-2 order-sm-1">
          <div class="card-body">
              @php
                  $hour = now()->format('H'); // 24-hour format
                  $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                  $userRole = auth()->user()->role->name ?? 'User';
              @endphp
              <p class="mb-2">You have created <span class="fw-medium">{{ $todayFilesCount }}</span> files today.</p>
              
              @if($pendingFilesForUser > 0)
                <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                  <h6 class="alert-heading mb-1">
                    <i class="bx bx-bell bx-tada me-1"></i> You have {{ $pendingFilesForUser }} pending {{ $pendingFilesForUser == 1 ? 'file' : 'files' }} to review!
                  </h6>
                  <p class="mb-0 small">Files have been assigned to you and are waiting for your action.</p>
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              @endif
              
              {{-- Buttons: stack vertically on mobile, row on larger screens --}}
              <div class="d-flex flex-column flex-sm-row gap-2">
                <a href="{{ route('files.index') }}" class="btn btn-outline-primary btn-lg w-100 flex-grow-0">
                  View Files
                  @if($pendingFilesForUser > 0)
                    <span class="badge rounded-pill bg-danger ms-1">{{ $pendingFilesForUser }}</span>
                  @endif
                </a>
                <button type="button" class="btn btn-success btn-lg w-100 flex-grow-0" onclick="openQrScanner()" title="Scan QR Code to Receive File">
                  <i class="bx bx-scan me-1"></i> Scan QR Code
                </button>
              </div>
            </div>
        </div>
        <div class="col-12 col-sm-5 order-1 order-sm-2 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4 d-flex justify-content-center justify-content-sm-start">
            <img src="{{asset('public/assets/img/illustrations/man-with-laptop-light.png')}}" height="140" alt="View Badge User" class="img-fluid" style="max-height: 140px;" data-app-dark-img="illustrations/man-with-laptop-dark.png" data-app-light-img="illustrations/man-with-laptop-light.png">
          </div>
        </div>
      </div>
    </div>
  </div>
 
 
  <div class="col-12 order-3 order-md-2 dashboard-stats">
    <div class="row g-3">
      <div class="col-12 col-sm-6 col-xl-3 mb-4 mb-sm-0">
        <div class="card h-100">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('public/assets/img/icons/unicons/chart-success.png')}}" alt="chart success" class="rounded">
              </div>
            </div>
            <span class="fw-semibold d-block mb-1">Total Files</span>
            <h3 class="card-title mb-2">{{$totalFiles}}</h3>
          </div>
        </div>
      </div>
      <div class="col-12 col-sm-6 col-xl-3 mb-4 mb-sm-0">
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
      <div class="col-12 col-sm-6 col-xl-3 mb-4 mb-sm-0">
        <div class="card h-100">
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
      <div class="col-12 col-sm-6 col-xl-3 mb-4 mb-sm-0">
        <div class="card h-100">
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
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Scan QR Code to Receive File</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        {{-- Manual Upload Option - commented out for now
        <div class="mb-3">
          <label for="qr-image-upload" class="btn btn-outline-primary btn-sm">
            <i class="bx bx-upload me-1"></i> Upload QR Code Image
          </label>
          <input type="file" id="qr-image-upload" accept="image/*" style="display: none;" />
        </div>
        <div class="text-muted small mb-3">OR</div>
        --}}
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

<!-- Note Input Modal (shown BEFORE QR scan) -->
<div class="modal fade" id="qrNoteModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Note Before Scanning</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="mb-3 text-muted">Please enter your note before scanning the QR code. This note will be saved when you receive the file.</p>
        <div class="mb-3">
          <label for="receiver-note" class="form-label">Your Note <span class="text-danger">*</span></label>
          <textarea 
            id="receiver-note" 
            class="form-control" 
            rows="4" 
            placeholder="Enter your note about receiving this file (required)"
            required
          ></textarea>
          <small class="text-muted">This note will be saved with the file movement record</small>
        </div>
        <div id="note-error" class="alert alert-danger" style="display: none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" onclick="proceedToScan()">
          <i class="bx bx-scan me-1"></i> Proceed to Scan
        </button>
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
let receiverNote = null; // Store receiver's note entered before scanning

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

// QR Scanner functions - Show note modal first
window.openQrScanner = function() {
  // Stop any existing scanner
  stopQrScanner();
  
  const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
    ? (window.jQuery || window.$) 
    : null;
  
  // Reset note and error
  document.getElementById('receiver-note').value = '';
  document.getElementById('note-error').style.display = 'none';
  receiverNote = null;
  
  // Show note modal first
  if ($) {
    $('#qrNoteModal').modal('show');
  } else {
    const noteModalEl = document.getElementById('qrNoteModal');
    if (noteModalEl) {
      const noteModal = new bootstrap.Modal(noteModalEl);
      noteModal.show();
    }
  }
};

// Proceed to scan after note is entered
window.proceedToScan = function() {
  const note = document.getElementById('receiver-note').value.trim();
  const errorEl = document.getElementById('note-error');
  
  // Validate note
  if (!note) {
    errorEl.textContent = 'Please enter a note before proceeding to scan.';
    errorEl.style.display = 'block';
    return;
  }
  
  // Store the note
  receiverNote = note;
  errorEl.style.display = 'none';
  
  const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
    ? (window.jQuery || window.$) 
    : null;
  
  // Close note modal
  if ($) {
    $('#qrNoteModal').modal('hide');
  } else {
    const noteModalEl = document.getElementById('qrNoteModal');
    if (noteModalEl) {
      const noteModal = bootstrap.Modal.getInstance(noteModalEl);
      if (noteModal) noteModal.hide();
    }
  }
  
  // Open scanner modal after a short delay
  setTimeout(() => {
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
  }, 300);
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
  
  // Request camera permission first using getUserMedia
  updateScanningStatus('<i class="bx bx-loader-alt bx-spin me-1"></i> Requesting camera access...');
  
  try {
    // Request permission by attempting to access camera
    const stream = await navigator.mediaDevices.getUserMedia({ video: true });
    // Stop the stream immediately - we just needed permission
    stream.getTracks().forEach(track => track.stop());
    console.log('Camera permission granted');
  } catch (permissionErr) {
    console.error('Camera permission error:', permissionErr);
    let permissionMsg = 'Camera access denied. ';
    
    if (permissionErr.name === 'NotAllowedError' || permissionErr.name === 'PermissionDeniedError') {
      permissionMsg += 'Please click the camera icon in your browser\'s address bar and allow camera access, then try again.';
    } else if (permissionErr.name === 'NotFoundError') {
      permissionMsg += 'No camera found. Please check if a camera is connected to your device.';
    } else if (permissionErr.name === 'NotReadableError' || permissionErr.name === 'TrackStartError') {
      permissionMsg += 'Camera is being used by another application. Please close other apps using the camera and try again.';
    } else {
      permissionMsg += 'Please check your browser settings and allow camera access.';
    }
    
    updateScanningStatus('<span class="text-danger"><i class="bx bx-error me-1"></i> ' + permissionMsg + '</span>');
    isScanning = false;
    return;
  }
  
  // Get available cameras if not already loaded
  if (availableCameras.length === 0) {
    updateScanningStatus('<i class="bx bx-loader-alt bx-spin me-1"></i> Detecting cameras...');
    try {
      availableCameras = await Html5Qrcode.getCameras();
      console.log('Available cameras:', availableCameras.map(d => d.label));
      
      if (availableCameras.length === 0) {
        updateScanningStatus('<span class="text-danger"><i class="bx bx-error me-1"></i> No cameras detected. Please ensure a camera is connected and try again.</span>');
        isScanning = false;
        return;
      }
      
      // Show switch button if more than one camera
      const switchBtn = document.getElementById('switch-camera-btn');
      if (switchBtn && availableCameras.length > 1) {
        switchBtn.style.display = 'inline-block';
      }
    } catch (err) {
      console.error('Could not enumerate cameras:', err);
      updateScanningStatus('<span class="text-danger"><i class="bx bx-error me-1"></i> Failed to detect cameras. Please check camera connection and browser permissions.</span>');
      availableCameras = [];
      isScanning = false;
      return;
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
  
  // Start the camera
  updateScanningStatus('<i class="bx bx-loader-alt bx-spin me-1"></i> Starting camera...');
  
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
    updateScanningStatus('<span class="text-success"><i class="bx bx-check-circle me-1"></i> Camera started (' + cameraLabel + '). Point at QR code to scan.</span>');
  } catch (err) {
    console.error('Failed to start QR scanner:', err);
    let errorMsg = 'Failed to start camera. ';
    
    const errMsg = err && err.message ? err.message : String(err);
    const errName = err && err.name ? err.name : '';
    
    if (errName === 'NotAllowedError' || errMsg.includes('Permission denied') || errMsg.includes('NotAllowedError')) {
      errorMsg += 'Camera permission denied. Please click the camera icon in your browser\'s address bar, allow camera access, then try again.';
    } else if (errName === 'NotFoundError' || errMsg.includes('Requested device not found') || errMsg.includes('NotFoundError')) {
      errorMsg += 'No camera found. Please check if a camera is connected and try again.';
    } else if (errName === 'NotReadableError' || errMsg.includes('Could not start video source') || errMsg.includes('NotReadableError')) {
      errorMsg += 'Camera is being used by another application. Please close other apps using the camera (Zoom, Teams, Skype, etc.) and try again.';
    } else if (errMsg.includes('OverconstrainedError') || errMsg.includes('constraint')) {
      errorMsg += 'Camera constraints not supported. Trying different camera settings...';
      // Try with facingMode instead if deviceId failed
      if (useCameraId && !useFacingMode) {
        setTimeout(function() {
          startQrScanner(null, 'environment');
        }, 1000);
        return;
      }
    } else {
      errorMsg += 'Error: ' + errMsg + '. Please check camera connection and browser permissions.';
    }
    
    updateScanningStatus('<span class="text-danger"><i class="bx bx-error me-1"></i> ' + errorMsg + '</span>');
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
  
  // Make AJAX call to scan endpoint with note
  const scanUrl = "{{ url('/file-movements/scan') }}/" + fileId + (receiverNote ? "?file_note=" + encodeURIComponent(receiverNote) : "");
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
      
      // Close scanner modal
      const modalEl = document.getElementById('qrScannerModal');
      const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
        ? (window.jQuery || window.$) 
        : null;
      
      if (modalEl) {
        if ($) {
          $('#qrScannerModal').modal('hide');
        } else {
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();
        }
      }
      
      // Show error in SweetAlert for better formatting
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'error',
          title: 'File Transfer Failed',
          html: errorMsg.replace(/\n/g, '<br>'),
          confirmButtonColor: '#696cff',
          confirmButtonText: 'OK, Got it!'
        });
      } else {
        // Fallback
        updateScanningStatus('<span class="text-danger"><i class="bx bx-error me-1"></i> ' + errorMsg + '</span>');
        setTimeout(() => {
          if (modalEl) {
            if ($) {
              $('#qrScannerModal').modal('hide');
            } else {
              const modal = bootstrap.Modal.getInstance(modalEl);
              if (modal) modal.hide();
            }
          }
        }, 5000);
      }
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

// Manual QR code image upload handler - commented out for now
/*
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
      
      if (typeof Html5Qrcode === 'undefined') {
        updateScanningStatus('<span class="text-danger">QR Scanner library not loaded. Please refresh the page.</span>');
        return;
      }
      
      if (isScanning && html5QrCode) {
        stopQrScanner();
      }
      
      const fileBasedInstance = new Html5Qrcode("qr-reader");
      
      updateScanningStatus('<i class="bx bx-loader-alt bx-spin me-1"></i> Scanning uploaded image...');
      
      fileBasedInstance.scanFile(file, true)
        .then(decodedText => {
          updateScanningStatus('<span class="text-success"><i class="bx bx-check-circle me-1"></i> QR Code detected! Processing...</span>');
          fileBasedInstance.clear();
          handleScannedQrCode(decodedText);
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
          uploadInput.value = '';
        });
    });
  }
});
*/

// Handle modal close to reset camera list for next time
document.addEventListener('DOMContentLoaded', function() {
  const modalEl = document.getElementById('qrScannerModal');
  if (modalEl) {
    modalEl.addEventListener('hidden.bs.modal', function() {
      availableCameras = [];
      currentCameraIndex = 0;
    });
  }
  
  // Auto-open QR scanner modal after login (only on dashboard)
  // Check if user just logged in by checking session flag
  @if(Session::has('just_logged_in'))
    // Wait a moment for page to fully load
    setTimeout(function() {
      if (typeof openQrScanner === 'function') {
        openQrScanner();
      }
    }, 1000);
  @endif
});

// Note: submitFileNote function removed - note is now entered before scanning
</script>

@endsection
