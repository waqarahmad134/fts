@extends('layouts/contentNavbarLayout')

@section('title', 'Add New Wing')

@section('content')
<h4 class="py-3 mb-4"><span class="text-muted fw-light">Wings /</span> Add New Wing</h4>

<div class="row">
  <div class="col-xxl">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Wing Details</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ url('/wings') }}">
          @csrf

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Wing Name</label>
            <div class="col-sm-10">
              <input type="text" name="name" class="form-control" placeholder="Enter Wing Name" required>
            </div>
          </div>

          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary">Save Wing</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
