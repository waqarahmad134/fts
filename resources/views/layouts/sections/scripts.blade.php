<!-- BEGIN: Vendor JS-->
<script src="{{ asset(('public/assets/vendor/libs/jquery/jquery.js')) }}"></script>
<script src="{{ asset(('public/assets/vendor/libs/popper/popper.js')) }}"></script>
<script src="{{ asset(('public/assets/vendor/js/bootstrap.js')) }}"></script>
<script src="{{ asset(('public/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')) }}"></script>
<script src="{{ asset(('public/assets/vendor/js/menu.js')) }}"></script>
@yield('vendor-script')
<!-- END: Page Vendor JS-->
<!-- BEGIN: Theme JS-->
<script src="{{ asset(('public/assets/js/main.js')) }}"></script>

<!-- END: Theme JS-->
<!-- Pricing Modal JS-->
@stack('pricing-script')
<!-- END: Pricing Modal JS-->
<!-- BEGIN: Page JS-->
@yield('page-script')
<!-- END: Page JS-->
