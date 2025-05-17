@extends('layouts/contentNavbarLayout')

@section('title', 'Profile Settings')

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
  <span class="text-muted fw-light">User /</span> Profile Settings
</h4>

<div class="row">
  <div class="col-md-12">
    <div class="card mb-4">
      <h5 class="card-header">Profile Details</h5>
      <div class="card-body">
        <form method="POST" action="{{ route('profile.update') }}">
          @csrf
          @method('PUT')
          
          <div class="row">
            <div class="mb-3 col-md-6">
              <label for="name" class="form-label">Full Name</label>
              <input class="form-control @error('name') is-invalid @enderror" type="text" id="name" name="name" value="{{ old('name', $user->name) }}" />
              @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3 col-md-6">
              <label for="email" class="form-label">Email</label>
              <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email', $user->email) }}" />
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3 col-md-6">
              <label for="designation" class="form-label">Designation</label>
              <input class="form-control @error('designation') is-invalid @enderror" type="text" id="designation" name="designation" value="{{ old('designation', $user->designation) }}" />
              @error('designation')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3 col-md-6">
              <label for="wing" class="form-label">Wing</label>
              <input class="form-control @error('wing') is-invalid @enderror" type="text" id="wing" name="wing" value="{{ old('wing', $user->wing) }}" />
              @error('wing')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          <h5 class="card-header px-0">Change Password</h5>
          
          <div class="row">
            <div class="mb-3 col-md-6">
              <label for="current_password" class="form-label">Current Password</label>
              <input class="form-control @error('current_password') is-invalid @enderror" type="password" id="current_password" name="current_password" />
              @error('current_password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3 col-md-6">
              <label for="new_password" class="form-label">New Password</label>
              <input class="form-control @error('new_password') is-invalid @enderror" type="password" id="new_password" name="new_password" />
              @error('new_password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3 col-md-6">
              <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
              <input class="form-control" type="password" id="new_password_confirmation" name="new_password_confirmation" />
            </div>
          </div>

          <div class="mt-4">
            <button type="submit" class="btn btn-primary me-2">Save changes</button>
            <button type="reset" class="btn btn-outline-secondary">Cancel</button>
          </div>
        </form>
      </div>
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