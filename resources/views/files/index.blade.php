@extends('layouts/contentNavbarLayout')

@section('title', 'Files - File Tracking System')

@section('page-script')
<script src="{{asset('public/assets/js/ui-toasts.js')}}"></script>
<!-- HTML5 QR Code Scanner Library -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
@endsection

@section('content')
<!-- Toast with Placements -->
<div class="bs-toast toast toast-placement-ex m-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
  <div class="toast-header">
    <i class='bx bx-bell me-2'></i>
    <div class="me-auto fw-medium">FTS</div>
    <small>Now</small>
    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
  <div class="toast-body">
    @if(Session::has('toast_success'))
      {{ Session::get('toast_success') }}
    @endif
    @if(Session::has('toast_error'))
      {{ Session::get('toast_error') }}
    @endif
  </div>
</div>
<!-- Toast with Placements -->

<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">File Tracking /</span> All Files
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">File Records</h5>
    @if (auth()->user()->role->name == 'Junior Clerk' || auth()->user()->role->name == 'Assistant Registrar' || auth()->user()->role->name == 'Admin')
    <a href="{{ url('/files/create') }}" class="btn btn-primary">
      <i class="bx bx-plus me-1"></i> Add New File
    </a>
    @endif
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>File No</th>
          <th>Subject</th>
          <th>PUC Proposal</th>
          <th>Created By</th>
          <th>Status</th>
          <th>Assigned To</th>
          <th>Sent To</th>
          <th>Image</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($files as $file)
        <tr>
          <td>{{ $file->file_no }}</td>
          <td>{{ $file->subject }}</td>
          <td>{{ Str::limit($file->puc_proposal, 50) }}</td>
          <td>{{ $file->creator->name ?? 'N/A' }}</td>
          
          <td>
            @if (auth()->user()->role->name == 'HCJ' || auth()->user()->role->name == 'Admin')
              <span onclick="openStatusModal({{ $file->id }})" class="cursor-pointer badge bg-label-{{ $file->status == 'pending' ? 'warning' : ($file->status == 'closed' ? 'danger' : 'success') }}">
                {{ ucfirst($file->status) }}
              </span>
            @else
              <span class="cursor-pointer badge bg-label-{{ $file->status == 'pending' ? 'warning' : ($file->status == 'closed' ? 'danger' : 'success') }}">
                {{ ucfirst($file->status) }}
              </span>
            @endif
          </td>

          <td>
            @php
              $latestMovement = $file->movements->last();
            @endphp

            @if ($latestMovement && $latestMovement->receiver)
              {{ $latestMovement->receiver->name }} ({{ $latestMovement->receiver->role->name ?? 'No Role' }})
              <span class="badge rounded-pill bg-danger">{{$latestMovement->file_reject == 1 ? "Rejected" : ""}}</span>
            @else
              <span class="text-muted">Unassigned</span>
            @endif
          </td>
          <td>
                @php
                    $sender = null;
                    if ($latestMovement) {
                        $sender = \App\Models\User::find($latestMovement->sender_id);
                    }
                @endphp
            
                @if ($file->status == 'closed')
                    <span class="text-muted">File Closed</span>
                @else
                    @if ($file->created_by == auth()->id() || (auth()->user()->role->name == 'Admin' || auth()->user()->role->name == 'HCJ'))
                        {{-- Show QR Code instead of Send button for file creator --}}
                        <button class="btn btn-info btn-sm" onclick="showQrCodeModal({{ $file->id }})" title="Show QR Code">
                            <i class="bx bx-qr-scan me-1"></i> QR Code
                        </button>
                    @else
                        {{-- Show Scan QR Code button for receivers --}}
                        <button class="btn btn-success btn-sm" onclick="openQrScanner()" title="Scan QR Code to Receive File">
                            <i class="bx bx-scan me-1"></i> Scan QR Code
                        </button>
                    @endif
                @endif
          </td>
          <td>
            @if ($file->file_image)
              <img width="100" class="img-fluid border rounded" src="{{ asset('/public/' . $file->file_image) }}" alt="Img">
            @else
              <span class="text-muted">None</span>
            @endif
          </td>
          <td>
            <a class="dropdown-item cursor-pointer" onclick="openViewModal({{ $file }})">
              <i class="bx bx-show-alt me-1"></i> View
            </a>
              @if (auth()->user()->role->name == 'HCJ' || auth()->user()->role->name == 'Admin')
              <a class="dropdown-item" href="{{ url('/files/' . $file->id . '/edit') }}">
                <i class="bx bx-edit-alt me-1"></i> Edit
              </a>
              
              <form action="{{ url('/files/' . $file->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to delete this file?')">
                  <i class="bx bx-trash me-1"></i> Delete
                </button>
              </form>
              @endif
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="8" class="text-center">No files found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <div class="d-flex justify-content-center">
      {{ $files->links() }}
    </div>
  </div>
</div>

<div class="modal fade" id="sendToModal" tabindex="-1"  >
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Send To</h5>  
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ url('/file-movements') }}" method="POST">
          @csrf
          <input type="hidden" name="file_id" id="file_id" value="" readonly>
          <input type="hidden" name="file_reject" id="file_reject" value="0"> 
          <div class="mb-3">
            <label for="receiver_id" class="form-label">Receiver</label>
            <select class="form-select" id="receiver_id" name="receiver_id"  onchange="checkReceiverRole()">
              @foreach ($users as $user)
              <option value="{{ $user->id }}" data-role="{{ $user->role->name }}">
                {{ $user->name }} ({{ $user->role->name }})
              </option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label for="file_note" class="form-label">File Note</label>
            <textarea class="form-control" id="file_note" name="file_note" rows="3"></textarea>
          </div>
          <button type="submit" class="btn btn-primary">Send</button>
          <button type="submit" id="rejectBtn" class="btn btn-danger" style="display: none;">Return this file</button>
        </form>
      </div>  
    </div>
  </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1"  >
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Status</h5> 
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="{{ url('/file-statuses') }}" method="POST">
          @csrf
          @method('PUT')
          <input type="hidden" name="file_id" id="file_id_status" value="" readonly>
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" id="status" name="status">
              <option value="pending">Pending</option>
              <option value="closed">Closed</option>
              <option value="reopened">Reopened</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Update</button>
        </form>
      </div>  
    </div>
  </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1"  >
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">File Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="file-content"></div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="qrCodeModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">File QR Code</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="mb-3">Scan this QR code to receive the file</p>
        <div id="qr-code-container" class="d-flex justify-content-center align-items-center mb-3">
          <img id="qr-code-image" src="" alt="QR Code" class="img-fluid" style="max-width: 300px;">
        </div>
        <p class="text-muted small">Share this QR code with others to transfer the file</p>
      </div>
    </div>
  </div>
</div>

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
    
    // Define functions that use jQuery
    window.openSendToModal = function(fileId) {
  $('#sendToModal').modal('show');
  $('#file_id').val(fileId);
  $('#receiver_id').val('');
  $('#file_note').val('');
  $('#file_reject').val(0);
  $('#sendBtn').show();
  $('#rejectBtn').hide();
    };


    window.checkReceiverRole = function() {
  const currentUserRole = "{{ auth()->user()->role->name }}";
  const receiverSelect = document.getElementById("receiver_id");
  const selectedOption = receiverSelect.options[receiverSelect.selectedIndex];
  const receiverRole = selectedOption.getAttribute("data-role");

  const roleHierarchy = [
    'Junior Clerk',
    'Assistant Registrar',
    'Deputy Registrar',
    'Additional Registrar',
    'DG',
    'Registrar',
    'HCJ'
  ];

  const currentIndex = roleHierarchy.indexOf(currentUserRole);
  const receiverIndex = roleHierarchy.indexOf(receiverRole);

  if (receiverIndex > currentIndex) {
    $('#file_reject').val(0);
    $('#sendBtn').show();
    $('#rejectBtn').hide();
  } else if (receiverIndex < currentIndex) {
    $('#file_reject').val(1);
    $('#sendBtn').hide();
    $('#rejectBtn').show();
  } else {
    $('#file_reject').val(0);
    $('#sendBtn').show();
    $('#rejectBtn').hide();
  }
    };

    window.openStatusModal = function(fileId) {
  $('#statusModal').modal('show');
  $('#statusModal #file_id_status').val(fileId);
    };

    window.showQrCodeModal = function(fileId) {
      // Add cache-busting parameter to ensure fresh QR code
      const timestamp = new Date().getTime();
      const qrCodeUrl = "{{ url('/files') }}/" + fileId + "/qr-code?t=" + timestamp;
      $('#qr-code-image').attr('src', qrCodeUrl);
      $('#qrCodeModal').modal('show');
      
      // Handle image load errors
      $('#qr-code-image').off('error').on('error', function() {
        $(this).attr('src', 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzAwIiBoZWlnaHQ9IjMwMCIgZmlsbD0iI2VlZSIvPjx0ZXh0IHg9IjUwJSIgeT0iNTAlIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiM5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5RUiBDb2RlIExvYWRpbmcuLi48L3RleHQ+PC9zdmc+');
        alert('Failed to load QR code. Please try again.');
      });
    };
    
    // Modal close handler will be attached in DOMContentLoaded below
  });
})();

// QR Scanner functions (don't require jQuery immediately) - variables already declared above
window.openQrScanner = function() {
  // Stop any existing scanner
  stopQrScanner();
  
  // Use jQuery if available, otherwise use vanilla JS
  const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
    ? (window.jQuery || window.$) 
    : null;
  
  if ($) {
    // Clear previous results
    $('#qr-reader-results').html('');
    $('#scanning-status').hide().html('');
    
    // Show modal
    $('#qrScannerModal').modal('show');
    
    // Initialize scanner after modal is fully shown
    $('#qrScannerModal').off('shown.bs.modal').on('shown.bs.modal', function() {
      startQrScanner();
    });
  } else {
    // Fallback if jQuery not available - wait a bit for jQuery to load
    setTimeout(function() {
      const $retry = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
        ? (window.jQuery || window.$) 
        : null;
      if ($retry) {
        $retry('#qr-reader-results').html('');
        $retry('#scanning-status').hide().html('');
        $retry('#qrScannerModal').modal('show');
        $retry('#qrScannerModal').off('shown.bs.modal').on('shown.bs.modal', function() {
          startQrScanner();
        });
      } else {
        // Final fallback - use vanilla JS with Bootstrap
        const resultsEl = document.getElementById('qr-reader-results');
        const statusEl = document.getElementById('scanning-status');
        if (resultsEl) resultsEl.innerHTML = '';
        if (statusEl) {
          statusEl.innerHTML = '';
          statusEl.style.display = 'none';
        }
        
        const modalEl = document.getElementById('qrScannerModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
          const modal = new bootstrap.Modal(modalEl);
          modal.show();
          modalEl.addEventListener('shown.bs.modal', function() {
            startQrScanner();
          }, { once: true });
        }
      }
    }, 100);
  }
};

window.startQrScanner = async function() {
  if (isScanning) {
    return;
  }
  
  const readerElementId = "qr-reader";
  
  // Clear previous content
  document.getElementById(readerElementId).innerHTML = '';
  
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
      
      console.log('Available cameras:', devices.map(d => d.label));
      console.log('Selected camera:', frontCamera ? frontCamera.label : devices[0].label);
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
  
  // Try starting scanner with front camera first, then fallback to any available
  // Priority: 1) Front camera via deviceId, 2) Front camera via facingMode, 3) Any camera via facingMode, 4) Any camera via deviceId
  
  let startPromise = null;
  
  if (cameraId) {
    // Try with specific front camera deviceId first
    console.log('Attempting to start with camera deviceId:', cameraId);
    startPromise = html5QrCode.start(
      { deviceId: { exact: cameraId } },
      config,
      (decodedText, decodedResult) => {
        handleScannedQrCode(decodedText);
      },
      (errorMessage) => {
        // Scan error - ignore, just continue scanning
      }
    );
  } else {
    // Fallback to facingMode: "user" (front camera for laptops) - this is what we want for laptop embedded cameras
    console.log('Attempting to start with facingMode: user (front/laptop camera)');
    startPromise = html5QrCode.start(
      { facingMode: "user" }, // Use front camera (laptop embedded camera) - NOT "environment" which is back camera
      config,
      (decodedText, decodedResult) => {
        handleScannedQrCode(decodedText);
      },
      (errorMessage) => {
        // Scan error - ignore, just continue scanning
      }
    );
  }
  
  startPromise.then(() => {
    isScanning = true;
    updateScanningStatus('<i class="bx bx-camera me-1"></i> Camera started. Point at QR code to scan.');
  }).catch(async (err) => {
    console.error('Failed to start scanner with first attempt:', err);
    
    // If using specific camera or facingMode failed, try fallback to any available camera
    try {
      console.log('Retrying with fallback methods...');
      
      // Try 1: Use any available camera device (without facingMode constraint)
      try {
        const devices = await Html5Qrcode.getCameras();
        if (devices && devices.length > 0) {
          // Try first available camera (should be laptop camera if only one)
          await html5QrCode.start(
            { deviceId: devices[0].id },
            config,
            (decodedText, decodedResult) => {
              handleScannedQrCode(decodedText);
            },
            (errorMessage) => {}
          );
          isScanning = true;
          updateScanningStatus('<i class="bx bx-camera me-1"></i> Camera started. Point at QR code to scan.');
          return;
        }
      } catch (anyCamErr) {
        console.log('Direct camera device access failed, trying environment camera...');
      }
      
      // Try 2: Use facingMode "environment" (back/external camera) as last resort
      try {
        await html5QrCode.start(
          { facingMode: "environment" },
          config,
          (decodedText, decodedResult) => {
            handleScannedQrCode(decodedText);
          },
          (errorMessage) => {}
        );
        isScanning = true;
        updateScanningStatus('<i class="bx bx-camera me-1"></i> Camera started. Point at QR code to scan.');
        return;
      } catch (envErr) {
        console.log('Environment camera also failed...');
      }
      
      
      // All attempts failed
      throw new Error('No camera available or permissions denied');
      
    } catch (finalErr) {
      console.error('All camera attempts failed:', finalErr);
      const errorMsg = 'Failed to access camera. Please ensure:\n1. Camera permissions are granted\n2. A camera is available\n3. No other application is using the camera';
      updateScanningStatus('<span class="text-danger">' + errorMsg + '</span>', true);
      alert(errorMsg);
    }
  });
}

// Helper function to update scanning status (works with or without jQuery)
window.updateScanningStatus = function(html, show = true) {
  const statusElement = document.getElementById('scanning-status');
  if (!statusElement) return;
  
  const $ = (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') 
    ? (window.jQuery || window.$) 
    : null;
  
  if ($) {
    $(statusElement).html(html);
    if (show) $(statusElement).show();
  } else {
    statusElement.innerHTML = html;
    if (show) statusElement.style.display = 'block';
    else statusElement.style.display = 'none';
  }
}

window.handleScannedQrCode = function(decodedText) {
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
  // Use fetch API if jQuery not available, otherwise use jQuery
  if (!$ || typeof $.ajax === 'undefined') {
    // Use fetch API as fallback
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    fetch("{{ url('/file-movements/scan') }}/" + fileId, {
      method: 'GET',
      headers: {
        'X-CSRF-TOKEN': csrfToken || '',
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json'
      }
    })
    .then(response => response.json())
    .then(data => {
      const message = data.message || 'File received successfully!';
      if (statusEl) {
        statusEl.innerHTML = '<span class="text-success"><i class="bx bx-check-circle me-1"></i> ' + message + ' Redirecting...</span>';
        statusEl.style.display = 'block';
      }
      setTimeout(() => {
        const modalEl = document.getElementById('qrScannerModal');
        if (modalEl) {
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();
        }
        window.location.reload();
      }, 1500);
    })
    .catch(error => {
      console.error('Error:', error);
      const errorMsg = 'Failed to receive file. Please try again.';
      if (statusEl) {
        statusEl.innerHTML = '<span class="text-danger"><i class="bx bx-error me-1"></i> ' + errorMsg + '</span>';
        statusEl.style.display = 'block';
      }
      setTimeout(() => {
        const modalEl = document.getElementById('qrScannerModal');
        if (modalEl) {
          const modal = bootstrap.Modal.getInstance(modalEl);
          if (modal) modal.hide();
        }
      }, 3000);
    });
    return;
  }
  
  $.ajax({
    url: "{{ url('/file-movements/scan') }}/" + fileId,
    method: 'GET',
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
      'X-Requested-With': 'XMLHttpRequest',
      'Accept': 'application/json'
    },
    success: function(response) {
      // Success response
      const message = response.message || 'File received successfully!';
      $('#scanning-status').html('<span class="text-success"><i class="bx bx-check-circle me-1"></i> ' + message + ' Redirecting...</span>').show();
      
      // Close modal and reload page after short delay
      setTimeout(() => {
        $('#qrScannerModal').modal('hide');
        window.location.reload();
      }, 1500);
    },
    error: function(xhr) {
      let errorMessage = 'Failed to receive file. ';
      
      if (xhr.status === 401) {
        errorMessage = 'Please login first.';
      } else if (xhr.status === 404) {
        errorMessage = 'File not found.';
      } else if (xhr.status === 403) {
        errorMessage = 'You cannot receive this file.';
      } else if (xhr.status === 400) {
        errorMessage = xhr.responseJSON?.error || 'Invalid request.';
      } else if (xhr.responseJSON && xhr.responseJSON.error) {
        errorMessage = xhr.responseJSON.error;
      } else if (xhr.responseJSON && xhr.responseJSON.message) {
        errorMessage = xhr.responseJSON.message;
      } else {
        errorMessage = 'Please try again.';
      }
      
      $('#scanning-status').html('<span class="text-danger"><i class="bx bx-error me-1"></i> ' + errorMessage + '</span>').show();
      
      // Close modal after showing error
      setTimeout(() => {
        $('#qrScannerModal').modal('hide');
      }, 3000);
    }
  });
}

window.stopQrScanner = function() {
  if (html5QrCode && isScanning) {
    html5QrCode.stop().then(() => {
      html5QrCode.clear();
      html5QrCode = null;
      isScanning = false;
    }).catch((err) => {
      console.error('Failed to stop scanner:', err);
      html5QrCode = null;
      isScanning = false;
    });
  }
  
  // Clear scanner element - use vanilla JS to avoid jQuery dependency
  const readerEl = document.getElementById('qr-reader');
  const resultsEl = document.getElementById('qr-reader-results');
  const statusEl = document.getElementById('scanning-status');
  
  if (readerEl) readerEl.innerHTML = '';
  if (resultsEl) resultsEl.innerHTML = '';
  if (statusEl) {
    statusEl.innerHTML = '';
    statusEl.style.display = 'none';
  }
}

// Ensure jQuery is loaded and attach modal close handler
document.addEventListener('DOMContentLoaded', function() {
  // Wait for jQuery to be available
  function waitForJQuery(callback) {
    if (typeof window.jQuery !== 'undefined' || typeof window.$ !== 'undefined') {
      callback(window.jQuery || window.$);
    } else {
      setTimeout(function() {
        waitForJQuery(callback);
      }, 100);
    }
  }
  
  waitForJQuery(function($) {
    // Stop scanner when modal is closed (using Bootstrap event)
    const modalEl = document.getElementById('qrScannerModal');
    if (modalEl) {
      // Remove any existing listeners and add new one
      $(modalEl).off('hidden.bs.modal').on('hidden.bs.modal', function() {
        stopQrScanner();
      });
    }
  });
});

function openViewModal(file) {
  const BASE_URL = "{{ asset('public') }}/";

  let html = `
    <strong>File No:</strong> ${file.file_no}<br>
    <strong>Subject:</strong> ${file.subject}<br>
    <strong>PUC/Proposal:</strong> ${file.puc_proposal}<br>
    <strong>Status:</strong> ${file.status}<br>
    <strong>Created By:</strong> ${file.creator?.name ?? 'N/A'}<br><br>
    
    <strong>Image:</strong><br>
      ${file.file_image ? `<img src="${BASE_URL}/${file.file_image}" alt="Image" class="img-fluid mb-3">` : 'No image uploaded'}<br>
    
    <strong>Attachment:</strong><br>
    ${file.file_attachment ? `<a href="${BASE_URL}/${file.file_attachment}" target="_blank">Download Attachment</a>` : 'No attachment'}<br><br>
    
    <strong>File Movements:</strong><br>
    <ul class="list-group">
  `;

  file.movements.forEach((move, index) => {
    html += `
      <li class="list-group-item">
        <strong>Movement #${index + 1}</strong><br>
        <strong>Sender ID:</strong> ${move.sender_id}<br>
        <strong>Receiver:</strong> ${move.receiver?.name ?? 'N/A'} (${move.receiver?.role?.name ?? 'N/A'})<br>
        <strong>Note:</strong> ${move.file_note ?? 'N/A'}<br>
        <strong>Rejected:</strong> ${move.file_reject ? 'Yes' : 'No'}<br>
        <strong>Receive Date:</strong> ${move.receive_date}
      </li>
    `;
  });

  html += `</ul>`;

  document.getElementById('file-content').innerHTML = html;
  $('#viewModal').modal('show');
}


document.addEventListener('DOMContentLoaded', function() {
    @if(Session::has('toast_success'))
        var toast = document.querySelector('.toast-placement-ex');
        toast.classList.add('bg-success');
        var toastInstance = new bootstrap.Toast(toast);
        toastInstance.show();
    @endif

    @if(Session::has('toast_error'))
        var toast = document.querySelector('.toast-placement-ex');
        toast.classList.add('bg-danger');
        var toastInstance = new bootstrap.Toast(toast);
        toastInstance.show();
    @endif
});
</script>
@endsection
