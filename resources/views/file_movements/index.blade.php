@extends('layouts/contentNavbarLayout')

@section('title', 'File Movements')

@section('content')
<h4 class="py-3 mb-4">File Movements</h4>

@foreach($files as $file)
<div class="card mb-4">
  <div class="card-body">
    <h5 class="card-title">{{ $file->subject }}</h5>
    <p class="text-muted">File No: {{ $file->file_no }} | Status: <span class="badge bg-label-{{ $file->status == 'pending' ? 'warning' : ($file->status == 'closed' ? 'danger' : 'success') }}">{{ ucfirst($file->status) }}</span></p>

    @if ($file->status != 'closed' && ($file->created_by == auth()->id() || auth()->user()->role->name == 'Admin' || auth()->user()->role->name == 'HCJ'))
      <div class="row">
        <div class="col-md-6">
          <h6>QR Code for File Transfer</h6>
          <p class="text-muted small">Scan this QR code to receive the file</p>
          <div class="text-center mb-3">
            <img src="{{ url('/files/' . $file->id . '/qr-code') }}" alt="QR Code" class="img-fluid" style="max-width: 250px; border: 2px solid #dee2e6; padding: 10px; border-radius: 8px;">
          </div>
          <p class="text-muted small text-center">Share this QR code with others to transfer the file</p>
        </div>
        <div class="col-md-6">
          <div class="d-none">
            {{-- Hidden form - functionality preserved but UI hidden --}}
            <form method="POST" action="{{ route('file-movements.store') }}">
              @csrf
              <input type="hidden" name="file_id" value="{{ $file->id }}">
              <div class="row mb-3">
                <div class="col">
                  <label>Send To</label>
                  <select name="receiver_id" class="form-control" required>
                    <option value="">-- Select User --</option>
                    @foreach($users as $user)
                      <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role->name ?? 'No Role' }})</option>
                    @endforeach
                  </select>
                </div>
                <div class="col">
                  <label>Note</label>
                  <input type="text" name="file_note" class="form-control" placeholder="Add a note (optional)">
                </div>
                <div class="col d-flex align-items-end">
                  <button class="btn btn-primary">Send</button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    @else
      <div class="alert alert-info">
        @if($file->status == 'closed')
          This file is closed and cannot be transferred.
        @else
          Only the file creator or Admin/HCJ can generate QR codes for this file.
        @endif
      </div>
    @endif
  </div>
</div>
@endforeach
@endsection
