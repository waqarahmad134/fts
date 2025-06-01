@extends('layouts/contentNavbarLayout')

@section('title', 'Files - File Tracking System')

@section('page-script')
<script src="{{asset('public/assets/js/ui-toasts.js')}}"></script>
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
    <h5 class="mb-0">File History</h5>
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
            @if ($file->status == 'closed')
              <span class="text-muted">File Closed</span>
              @else
              <button class="btn btn-primary" onclick="openSendToModal({{ $file->id }})">Send To</button>
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
            <a class="dropdown-item" onclick="openViewModal({{ $file }})">
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


<script>
function openSendToModal(fileId) {
  $('#sendToModal').modal('show');
  $('#file_id').val(fileId);
  $('#receiver_id').val('');
  $('#file_note').val('');
  $('#file_reject').val(0);
  $('#sendBtn').show();
  $('#rejectBtn').hide();
}

function checkReceiverRole() {
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
}

function openStatusModal(fileId) {
  $('#statusModal').modal('show');
  $('#statusModal #file_id_status').val(fileId);
}

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
