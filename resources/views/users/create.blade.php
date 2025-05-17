@extends('layouts/contentNavbarLayout')

@section('title', 'Add New User')

@section('content')

<script>
document.addEventListener('DOMContentLoaded', function () {
    fetch("https://api.ipify.org?format=json")
        .then(res => res.json())
        .then(data => {
            document.getElementById('ip_address').value = data.ip;
        });

    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('latitude').value = position.coords.latitude;
            document.getElementById('longitude').value = position.coords.longitude;
        });
    }

    document.getElementById('device_token').value = 'dummy_token_' + Math.random().toString(36).substring(2);
});
</script>

<h4 class="py-3 mb-4"><span class="text-muted fw-light">Users /</span> Add New User</h4>

@if(session('toast_error'))
  <div class="alert alert-danger">{{ session('toast_error') }}</div>
@endif

<div class="card mb-4">
  <div class="card-body">
    <form method="POST" action="{{ url('/users') }}">
      @csrf

      <!-- Name & Email -->
      <div class="row">
         <div class="col mb-3">
           <label class="form-label">Name</label>
           <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
           @error('name')
             <div class="invalid-feedback">{{ $message }}</div>
           @enderror
         </div>
   
         <div class="col mb-3">
           <label class="form-label">Email</label>
           <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
           @error('email')
             <div class="invalid-feedback">{{ $message }}</div>
           @enderror
         </div>
       </div>

      <!-- Password & Role -->
      <div class="row">
        <div class="col mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
          @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
  
        <div class="col mb-3">
          <label class="form-label">Role</label>
          <select name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
            <option value="">-- Select Role --</option>
            @foreach ($roles as $role)
              <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
            @endforeach
          </select>
          @error('role_id')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <!-- Designation & Wing -->
      <div class="row">
        <div class="col mb-3">
          <label class="form-label">Designation</label>
          <input type="text" name="designation" class="form-control @error('designation') is-invalid @enderror" value="{{ old('designation') }}">
          @error('designation')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
  
        <div class="col mb-3">
          <label class="form-label">Wing</label>
          <input type="text" name="wing" class="form-control @error('wing') is-invalid @enderror" value="{{ old('wing') }}">
          @error('wing')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      <!-- Supervisor -->
      <div class="mb-3">
        <label class="form-label">Supervisor</label>
        <select name="supervisor_id" class="form-control @error('supervisor_id') is-invalid @enderror">
          <option value="">-- Select Supervisor --</option>
          @foreach ($supervisors as $supervisor)
            <option value="{{ $supervisor->id }}" {{ old('supervisor_id') == $supervisor->id ? 'selected' : '' }}>
              {{ $supervisor->name }}
            </option>
          @endforeach
        </select>
        @error('supervisor_id')
          <div class="invalid-feedback">{{ $message }}</div>
        @enderror
      </div>

      <!-- Hidden Fields -->
      <input type="hidden" name="ip_address" id="ip_address" value="{{ old('ip_address') }}">
      @error('ip_address')
        <div class="text-danger">{{ $message }}</div>
      @enderror

      <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
      @error('latitude')
        <div class="text-danger">{{ $message }}</div>
      @enderror

      <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
      @error('longitude')
        <div class="text-danger">{{ $message }}</div>
      @enderror

      <input type="hidden" name="device_token" id="device_token" value="{{ old('device_token') }}">
      @error('device_token')
        <div class="text-danger">{{ $message }}</div>
      @enderror

      <button type="submit" class="btn btn-primary">Create User</button>
    </form>
  </div>
</div>

@endsection
 