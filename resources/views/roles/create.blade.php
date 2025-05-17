@extends('layouts/contentNavbarLayout')

@section('title', 'Add New File')

@section('content')
<h4 class="py-3 mb-4"><span class="text-muted fw-light">Roles /</span> Add New Role</h4>

<div class="row">
  <div class="col-xxl">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Role Details</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ url('/roles') }}">
          @csrf

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Role Name</label>
            <div class="col-sm-10">
              <input type="text" name="name" class="form-control" placeholder="Enter Role Name" required>
            </div>
          </div>

          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary">Save Role</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
