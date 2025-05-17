@extends('layouts/contentNavbarLayout')

@section('title', 'Wings - File Tracking System')

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
  <span class="text-muted fw-light">Wings /</span> All Wings
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Wing Records</h5>
    <a href="{{ url('/wings/create') }}" class="btn btn-primary">
      <i class="bx bx-plus me-1"></i> Add New Wing
    </a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Wing Name</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($wings as $wing)
        <tr>
          <td>{{ $wing->name }}</td>
          <td>
              <a class="dropdown-item" href="{{ url('/wings/' . $wing->id . '/edit') }}">
                <i class="bx bx-edit-alt me-1"></i> Edit
              </a>
              <form action="{{ url('/wings/' . $wing->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to delete this wing?')">
                  <i class="bx bx-trash me-1"></i> Delete
                </button>
              </form>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="2" class="text-center">No wings found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <div class="d-flex justify-content-center">
      {{ $wings->links() }}
    </div>
  </div>
</div>

<script>
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
