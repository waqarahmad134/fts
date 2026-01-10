@extends('layouts/contentNavbarLayout')

@section('title', 'Error Logs - FTS')

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
  <span class="text-muted fw-light">System /</span> Error Logs
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div>
      <h5 class="mb-0">Laravel Error Logs</h5>
      <small class="text-muted">File Size: {{ $fileSizeFormatted }}</small>
    </div>
    <div>
      <button type="button" class="btn btn-danger" onclick="confirmClearLogs()">
        <i class="bx bx-trash me-1"></i> Clear All Logs
      </button>
    </div>
  </div>
  <div class="card-body">
    @if(count($logs) > 0)
      <div class="table-responsive">
        <div class="log-container" style="max-height: 600px; overflow-y: auto;">
          @foreach($logs as $index => $log)
            <div class="log-entry mb-3 p-3 border rounded 
              @if($log['level'] == 'error') border-danger bg-danger bg-opacity-10
              @elseif($log['level'] == 'warning') border-warning bg-warning bg-opacity-10
              @elseif($log['level'] == 'debug') border-secondary bg-secondary bg-opacity-10
              @else border-info bg-info bg-opacity-10
              @endif
            ">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <span class="badge 
                  @if($log['level'] == 'error') bg-danger
                  @elseif($log['level'] == 'warning') bg-warning
                  @elseif($log['level'] == 'debug') bg-secondary
                  @else bg-info
                  @endif
                ">
                  {{ strtoupper($log['level']) }}
                </span>
                <small class="text-muted">#{{ $index + 1 }}</small>
              </div>
              <pre class="mb-0" style="white-space: pre-wrap; word-wrap: break-word; font-size: 0.875rem;">{{ $log['content'] }}</pre>
            </div>
          @endforeach
        </div>
      </div>
      <div class="mt-3">
        <p class="text-muted small">Showing last {{ count($logs) }} log entries (newest first)</p>
      </div>
    @else
      <div class="alert alert-info">
        <i class="bx bx-info-circle me-2"></i>
        No logs found. The log file is empty or doesn't exist yet.
      </div>
    @endif
  </div>
</div>

<!-- Clear Logs Confirmation Modal -->
<div class="modal fade" id="clearLogsModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Clear Logs</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to clear all error logs? This action cannot be undone.</p>
        <div class="alert alert-warning">
          <i class="bx bx-error me-2"></i>
          <strong>Warning:</strong> All log entries will be permanently deleted.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form action="{{ route('logs.clear') }}" method="POST" style="display: inline;">
          @csrf
          <button type="submit" class="btn btn-danger">
            <i class="bx bx-trash me-1"></i> Clear All Logs
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
function confirmClearLogs() {
  $('#clearLogsModal').modal('show');
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
