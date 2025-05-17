@extends('layouts/contentNavbarLayout')

@section('title', 'File Movements')

@section('content')
<h4 class="py-3 mb-4">File Movements</h4>

@foreach($files as $file)
<div class="card mb-4">
  <div class="card-body">
    <h5 class="card-title">{{ $file->subject }}</h5>

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
@endforeach
@endsection
