@extends('layouts/contentNavbarLayout')

@section('title', 'Add New File')

@section('content')
<h4 class="py-3 mb-4"><span class="text-muted fw-light">Forms /</span> Add New File</h4>

<div class="row">
  <div class="col-xxl">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">File Details</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ url('/files') }}" enctype="multipart/form-data">
          @csrf

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">File No</label>
            <div class="col-sm-10">
              <input type="text" name="file_no" class="form-control" placeholder="Enter File No" required>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Subject</label>
            <div class="col-sm-10">
              <input type="text" name="subject" class="form-control" placeholder="Enter Subject" required>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">PUC Proposal</label>
            <div class="col-sm-10">
              <textarea name="puc_proposal" class="form-control" placeholder="Write PUC Proposal here" rows="4" required></textarea>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Attachment (PDF/DOC)</label>
            <div class="col-sm-10">
              <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx">
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Image (Optional)</label>
            <div class="col-sm-10">
              <input type="file" name="file_image" class="form-control" accept="image/*">
            </div>
          </div>

          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary">Save File</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
