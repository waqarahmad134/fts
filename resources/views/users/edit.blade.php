@extends('layouts/contentNavbarLayout')

@section('title', 'Edit User')

@section('content')
<h4 class="py-3 mb-4"><span class="text-muted fw-light">Users /</span> Edit User</h4>

<div class="card mb-4">
  <div class="card-body">
    <form method="POST" action="{{ url('/users/' . $user->id) }}">
      @csrf
      @method('PUT')

      <!-- Name -->
      <div class="mb-3">
        <label class="form-label">Name</label>
        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
      </div>

      <!-- Email -->
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
      </div>

      <!-- Password (optional) -->
      <div class="mb-3">
        <label class="form-label">Password (leave blank to keep current)</label>
        <input type="password" name="password" class="form-control">
      </div>

      <!-- Role -->
      <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role_id" class="form-control" required>
          @foreach ($roles as $role)
            <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
          @endforeach
        </select>
      </div>

      <!-- Designation -->
      <div class="mb-3">
        <label class="form-label">Designation</label>
        <input type="text" name="designation" class="form-control" value="{{ $user->designation }}">
      </div>

      <!-- Wing -->
      <div class="mb-3">
        <label class="form-label">Wing</label>
        <input type="text" name="wing" class="form-control" value="{{ $user->wing }}">
      </div>

      <!-- Supervisor -->
      <div class="mb-3">
        <label class="form-label">Supervisor</label>
        <select name="supervisor_id" class="form-control">
          <option value="">-- Select Supervisor --</option>
          @foreach ($supervisors as $supervisor)
            <option value="{{ $supervisor->id }}" {{ $user->supervisor_id == $supervisor->id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
          @endforeach
        </select>
      </div>
      <button type="submit" class="btn btn-primary">Update User</button>
    </form>
  </div>
</div>
@endsection
