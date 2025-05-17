@extends('layouts/contentNavbarLayout')

@section('title', 'Edit File')

@section('content')
<h4 class="py-3 mb-4"><span class="text-muted fw-light">Forms /</span> Edit File</h4>

<div class="row">
  <div class="col-xxl">
    <div class="card mb-4">
      <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">File Details</h5>
      </div>
      <div class="card-body">
        <form method="POST" action="{{ url('/files/' . $file->id) }}" enctype="multipart/form-data">
          @csrf
          @method('PUT')

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">File No</label>
            <div class="col-sm-10">
              <input type="text" name="file_no" class="form-control" value="{{ $file->file_no }}" required>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Subject</label>
            <div class="col-sm-10">
              <input type="text" name="subject" class="form-control" value="{{ $file->subject }}" required>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">PUC Proposal</label>
            <div class="col-sm-10">
              <textarea name="puc_proposal" class="form-control" rows="4" required>{{ $file->puc_proposal }}</textarea>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Status</label>
            <div class="col-sm-10">
              <select name="status" class="form-select" required>
                <option value="pending" {{ $file->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="closed" {{ $file->status == 'closed' ? 'selected' : '' }}>Closed</option>
                <option value="reopened" {{ $file->status == 'reopened' ? 'selected' : '' }}>Reopened</option>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Current Attachment</label>
            <div class="col-sm-10">
              @if ($file->attachment)
                <a href="{{ asset($file->attachment) }}" target="_blank" class="btn btn-outline-primary btn-sm">View Current Attachment</a>
              @else
                <span class="text-muted">No attachment</span>
              @endif
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">New Attachment (PDF/DOC)</label>
            <div class="col-sm-10">
              <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx">
              <small class="text-muted">Leave empty to keep current attachment</small>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">Current Image</label>
            <div class="col-sm-10">
              @if ($file->file_image)
                <img src="{{ asset($file->file_image) }}" alt="Current Image" class="img-fluid mb-2" style="max-height: 200px;">
              @else
                <span class="text-muted">No image</span>
              @endif
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-2 col-form-label">New Image (Optional)</label>
            <div class="col-sm-10">
              <input type="file" name="file_image" class="form-control" accept="image/*">
              <small class="text-muted">Leave empty to keep current image</small>
            </div>
          </div>

          <div class="row justify-content-end">
            <div class="col-sm-10">
              <button type="submit" class="btn btn-primary">Update File</button>
              <a href="{{ url('/files') }}" class="btn btn-secondary">Cancel</a>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection 