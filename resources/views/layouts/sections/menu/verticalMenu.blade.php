<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">

  <div class="app-brand demo">
    <a href="{{url('/')}}" class="app-brand-link" style="margin:auto;">
      <span class="app-brand-logo demo">
        @include('_partials.macros',["width"=>25,"withbg"=>'var(--bs-primary)'])
      </span>
    </a>

    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
      <i class="bx bx-chevron-left bx-sm align-middle"></i>
    </a>
  </div>

  <!-- <div class="menu-inner-shadow"></div> -->

  <ul class="menu-inner py-1">

    {{-- Dashboards --}}
    <li class="menu-item {{ request()->is('/') ? 'active' : '' }}">
      <a href="{{ url('/') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-home-circle"></i>
        <div>Dashboards</div>
      </a>
    </li>

    {{-- Menu Header --}}
    <li class="menu-header small text-uppercase">
      <span class="menu-header-text">Apps & Pages</span>
    </li>
    

    {{-- Users Menu --}}
    @if(auth()->user()->role->name == 'admin' || auth()->user()->role->name == 'Admin')
    <li class="menu-item {{ request()->is('users*') ? 'active open' : '' }}">
        <a href="javascript:void(0);" class="menu-link menu-toggle">
            <i class="menu-icon tf-icons bx bx-table"></i>
            <div>Users</div>
        </a>
        <ul class="menu-sub">
            <li class="menu-item {{ request()->is('users') ? 'active' : '' }}">
                <a href="{{ url('users') }}" class="menu-link"><div>All Users</div></a>
            </li>
            @if (auth()->user()->role->name == 'Junior Clerk' || auth()->user()->role->name == 'Assistant Registrar' || auth()->user()->role->name == 'Admin')
            <li class="menu-item {{ request()->is('users/create') ? 'active' : '' }}">
                <a href="{{ url('users/create') }}" class="menu-link"><div>Create New User</div></a>
            </li>
            @endif
        </ul>
    </li>
    @endif


    {{-- Files Menu --}}
    <li class="menu-item {{ request()->is('files*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-table"></i>
        <div>Files</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->is('files') ? 'active' : '' }}">
          <a href="{{ url('files') }}" class="menu-link"><div>All Files</div></a>
        </li>
        @if (auth()->user()->role->name == 'Junior Clerk' || auth()->user()->role->name == 'Assistant Registrar' || auth()->user()->role->name == 'Admin')
        <li class="menu-item {{ request()->is('files/create') ? 'active' : '' }}">
          <a href="{{ url('files/create') }}" class="menu-link"><div>Create New File</div></a>
        </li>
        @endif
      </ul>
    </li>
    

    {{-- Files History --}}
    <li class="menu-item {{ request()->is('history') ? 'active' : '' }}">
      <a href="{{ route('files.history') }}" class="menu-link">
        <i class="menu-icon tf-icons bx bx-table"></i>
        <div>Files History (Closed)</div>
      </a>
    </li>

    {{-- Roles --}}
    @if(auth()->user()->role->name == 'admin' || auth()->user()->role->name == 'Admin')

    <li class="menu-item {{ request()->is('roles*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-table"></i>
        <div>Roles</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->is('roles') ? 'active' : '' }}">
          <a href="{{ url('roles') }}" class="menu-link"><div>All Roles</div></a>
        </li>
        <li class="menu-item {{ request()->is('roles/create') ? 'active' : '' }}">
          <a href="{{ url('roles/create') }}" class="menu-link"><div>Create New Role</div></a>
        </li>
      </ul>
    </li>

    {{-- Wings --}}
    <li class="menu-item {{ request()->is('wings*') ? 'active open' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-table"></i>
        <div>Wing</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item {{ request()->is('wings') ? 'active' : '' }}">
          <a href="{{ url('wings') }}" class="menu-link"><div>All Wings</div></a>
        </li>
        <li class="menu-item {{ request()->is('wings/create') ? 'active' : '' }}">
          <a href="{{ url('wings/create') }}" class="menu-link"><div>Create New Wing</div></a>
        </li>
      </ul>
    </li>
    @endif

    {{-- Settings --}}
    <li class="menu-item {{ request()->is('profile') ? 'active' : '' }}">
      <a href="javascript:void(0);" class="menu-link menu-toggle">
        <i class="menu-icon tf-icons bx bx-user"></i>
        <div>Settings</div>
      </a>
      <ul class="menu-sub">
        <li class="menu-item">
          <a href="{{ url('profile') }}" class="menu-link"><div>Profile</div></a>
        </li>
      </ul>
    </li>
  </ul>
</aside>
