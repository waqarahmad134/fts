<!-- BEGIN: Vendor JS-->
<script src="{{ asset(('public/assets/vendor/libs/jquery/jquery.js')) }}"></script>
<script src="{{ asset(('public/assets/vendor/libs/popper/popper.js')) }}"></script>
<script src="{{ asset(('public/assets/vendor/js/bootstrap.js')) }}"></script>
<script src="{{ asset(('public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')) }}"></script>
<script src="{{ asset(('public/assets/vendor/js/menu.js')) }}"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@yield('vendor-script')
<!-- END: Page Vendor JS-->
<script src="{{ asset(('public/assets/js/main.js')) }}"></script>

<!-- END: Theme JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->

<!-- Global session toasts (Bootstrap toast for duplicate/validation errors, etc.) -->
@if(Session::has('toast_error') || Session::has('toast_success'))
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var toastEl = document.getElementById('global-session-toast');
      if (!toastEl) return;
      @if(Session::has('toast_error'))
        toastEl.classList.add('bg-danger');
      @else
        toastEl.classList.add('bg-success');
      @endif
    var toast = new bootstrap.Toast(toastEl);
      toast.show();
    });
  </script>
@endif