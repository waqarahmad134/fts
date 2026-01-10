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
        <p class="mb-3">Position the QR code within the camera frame</p>
        <div id="qr-reader" style="width: 100%; max-width: 500px; margin: 0 auto;"></div>
        <div id="qr-reader-results" class="mt-3"></div>
        <div id="scanning-status" class="alert alert-info mt-3" style="display: none;"></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="stopQrScanner()">Close</button>
      </div>
    </div>
  </div>
</div>

<script>
// Global variables for QR scanner
let html5QrCode = null;
let isScanning = false;

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

window.startQrScanner = async function() {
  if (isScanning) {
    return;
  }
  
  const readerElementId = "qr-reader";
  
  // Clear previous content
  const readerEl = document.getElementById(readerElementId);
  if (!readerEl) return;
  
  readerEl.innerHTML = '';
  
  if (typeof Html5Qrcode === 'undefined') {
    updateScanningStatus('<span class="text-danger">QR Scanner library not loaded. Please refresh the page.</span>');
    return;
  }
  
  html5QrCode = new Html5Qrcode(readerElementId);
  
  // Try to get available cameras first
  let cameraId = null;
  let facingMode = "user"; // Start with front camera (laptop embedded camera)
  
  try {
    // Get list of available cameras
    const devices = await Html5Qrcode.getCameras();
    
    if (devices && devices.length > 0) {
      // Try to find front camera first (usually has "front" in label or is videoinput:0)
      const frontCamera = devices.find(device => 
        device.label.toLowerCase().includes('front') || 
        device.label.toLowerCase().includes('facing: user') ||
        device.label.toLowerCase().includes('integrated')
      );
      
      // If front camera found, use its deviceId, otherwise use first available camera
      cameraId = frontCamera ? frontCamera.id : devices[0].id;
      facingMode = null; // Use deviceId instead of facingMode when we have specific camera
    }
  } catch (err) {
    console.warn('Could not enumerate cameras, using facingMode:', err);
    // Fall back to facingMode if enumeration fails
    cameraId = null;
  }
  
  // Configuration for scanning
  const config = {
    fps: 10, // Frames per second
    qrbox: { width: 250, height: 250 }, // Scanning area
    aspectRatio: 1.0
  };
  
  let startPromise = null;
  
  if (cameraId) {
    try {
      startPromise = html5QrCode.start(
        cameraId,
        {
          fps: config.fps,
          qrbox: config.qrbox
        },
        onScanSuccess,
        onScanError
      );
    } catch (err) {
      console.warn('Failed to start with cameraId, trying facingMode:', err);
      startPromise = html5QrCode.start(
        { facingMode: facingMode },
        config,
        onScanSuccess,
        onScanError
      );
    }
  } else {
    startPromise = html5QrCode.start(
      { facingMode: facingMode },
      config,
      onScanSuccess,
      onScanError
    );
  }
  
  startPromise.then(() => {
    isScanning = true;
    updateScanningStatus('<i class="bx bx-camera me-1"></i> Camera started. Point at QR code to scan.');
  }).catch((err) => {
    console.error('Failed to start QR scanner:', err);
    updateScanningStatus('<span class="text-danger">Failed to start camera. Please check permissions and try again.</span>');
  });
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
  const scanUrl = '/file-movements/scan/' + fileId;
  const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
  
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
    .then(response => response.json())
    .then(data => {
      const modalEl = document.getElementById('qrScannerModal');
      const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
        ? (window.jQuery || window.$) 
        : null;
      
      if (data.success) {
        if ($ && modalEl) {
          $('#qrScannerModal').modal('hide');
          // Redirect to files page after a short delay
          setTimeout(() => {
            window.location.href = '/files';
          }, 500);
        } else if (modalEl) {
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) {
            modal.hide();
            setTimeout(() => {
              window.location.href = '/files';
            }, 500);
          }
        }
        
        // Show success message
        if ($) {
          if (typeof toastr !== 'undefined') {
            toastr.success(data.message || 'File received successfully!');
          }
        }
      } else {
        if ($ && statusEl) {
          $(statusEl).html('<span class="text-danger">' + (data.message || 'Failed to receive file. Please try again.') + '</span>').show();
        } else if (statusEl) {
          statusEl.innerHTML = '<span class="text-danger">' + (data.message || 'Failed to receive file. Please try again.') + '</span>';
          statusEl.style.display = 'block';
        }
      }
    })
    .catch(error => {
      console.error('Error:', error);
      const statusEl = document.getElementById('scanning-status');
      if (statusEl) {
        statusEl.innerHTML = '<span class="text-danger">Network error. Please try again.</span>';
        statusEl.style.display = 'block';
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
</script>

@endsection
