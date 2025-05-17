@extends('layouts/contentNavbarLayout')

@section('title', 'Users - File Tracking System')

@section('page-script')
<script src="{{asset('public/assets/js/ui-toasts.js')}}"></script>
@endsection

@section('content')

<!-- Toast Notification -->
<div class="bs-toast toast toast-placement-ex m-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
  <div class="toast-header">
    <i class='bx bx-bell me-2'></i>
    <div class="me-auto fw-medium">FTS</div>
    <small>Now</small>
    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
  <div class="toast-body">
    @if(Session::has('toast_success')) {{ Session::get('toast_success') }} @endif
    @if(Session::has('toast_error')) {{ Session::get('toast_error') }} @endif
  </div>
</div>

<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Users /</span> All Users
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">User Records</h5>
    @if(Auth::user()->role->name == 'Admin')
    <a href="{{ url('/users/create') }}" class="btn btn-primary">
      <i class="bx bx-plus me-1"></i> Add New User
    </a>
    @endif
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Role</th>
          <th>Designation</th>
          <th>Wing</th>
          <th>Supervisor</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($users as $user)
        <tr>
          <td>{{ $user->name }}</td>
          <td>{{ $user->email }}</td>
          <td>{{ $user->role->name ?? 'N/A' }}</td>
          <td>{{ ucfirst($user->designation) }}</td>
          <td>{{ $user->wing }}</td>
          <td>{{ $user->supervisor->name ?? 'N/A' }}</td>
          <td>
            <div class="dropdown">
              <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                <i class="bx bx-dots-vertical-rounded"></i>
              </button>
              <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ url('/users/' . $user->id . '/edit') }}">
                  <i class="bx bx-edit-alt me-1"></i> Edit
                </a>
                <form action="{{ url('/users/' . $user->id) }}" method="POST">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item" onclick="return confirm('Are you sure you want to delete this user?')">
                    <i class="bx bx-trash me-1"></i> Delete
                  </button>
                </form>
              </div>
            </div>
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="7" class="text-center">No users found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <div class="d-flex justify-content-center">
      {{ $users->links() }}
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
