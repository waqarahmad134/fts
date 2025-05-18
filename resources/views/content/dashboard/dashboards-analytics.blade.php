@extends('layouts/contentNavbarLayout')

@section('title', 'Dashboard - Analytics')

@section('vendor-style')
<link rel="stylesheet" href="{{asset('assets/vendor/libs/apex-charts/apex-charts.css')}}">
@endsection

@section('vendor-script')
<script src="{{asset('assets/vendor/libs/apex-charts/apexcharts.js')}}"></script>
@endsection

@section('page-script')
<script src="{{asset('assets/js/dashboards-analytics.js')}}"></script>
@endsection

@section('content')

<div class="row">
  <div class="col-lg-12 mb-4 order-0">
    <div class="card">
      <div class="d-flex align-items-end row">
        <div class="col-sm-7">
          <div class="card-body">
              <h5 class="card-title text-primary">Welcome {{auth()->user()->name}}! 🎉</h5>
              <p class="mb-4">You have created <span class="fw-medium">{{ $todayFilesCount }}</span> files today.</p>
              <a href="{{ route('files.index') }}" class="btn btn-sm btn-outline-primary">View Files</a>
            </div>
        </div>
        <div class="col-sm-5 text-center text-sm-left">
          <div class="card-body pb-0 px-0 px-md-4">
            <img src="{{asset('public/assets/img/illustrations/man-with-laptop-light.png')}}" height="140" alt="View Badge User" data-app-dark-img="illustrations/man-with-laptop-dark.png" data-app-light-img="illustrations/man-with-laptop-light.png">
          </div>
        </div>
      </div>
    </div>
  </div>
 
 
  <div class="col-12 order-3 order-md-2">
    <div class="row">
      <div class="col mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('public/assets/img/icons/unicons/chart-success.png')}}" alt="chart success" class="rounded">
              </div>
            </div>
            <span class="fw-semibold d-block mb-1">Total Files</span>
            <h3 class="card-title mb-2">{{$totalFiles}}</h3>
            <!-- <small class="text-success fw-semibold"><i class='bx bx-up-arrow-alt'></i> +72.80%</small> -->
          </div>
        </div>
      </div>
      <div class="col mb-4">
        <div class="card h-100">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('public/assets/img/icons/unicons/wallet-info.png')}}" alt="Credit Card" class="rounded">
              </div>
            </div>
            <span>Pending Files</span>
            <h3 class="card-title text-nowrap mb-1">{{$pendingFiles}}</h3>
          </div>
        </div>
      </div>
      <div class="col mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('public/assets/img/icons/unicons/paypal.png')}}" alt="Credit Card" class="rounded">
              </div>
            </div>
            <span class="d-block mb-1">Closed Files</span>
            <h3 class="card-title text-nowrap mb-2">{{$closedFiles}}</h3>
          </div>
        </div>
      </div>
      <div class="col mb-4">
        <div class="card">
          <div class="card-body">
            <div class="card-title d-flex align-items-start justify-content-between">
              <div class="avatar flex-shrink-0">
                <img src="{{asset('public/assets/img/icons/unicons/cc-primary.png')}}" alt="Credit Card" class="rounded">
              </div>
            </div>
            <span class="fw-semibold d-block mb-1">Reopened Files</span>
            <h3 class="card-title mb-2">{{$reopenedFiles}}</h3>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
