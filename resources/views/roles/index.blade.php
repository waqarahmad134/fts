@extends('layouts/contentNavbarLayout')

@section('title', 'Roles - File Tracking System')

@section('page-script')
<script src="{{ asset('public/assets/js/ui-toasts.js') }}"></script>

<script>

$(document).ready(function () {
    $('#permissionsForm').submit(function(e) {
    e.preventDefault();

    var roleId = $('#modalRoleId').val();
    var formData = $(this).serialize();

    $.ajax({
      url: '{{ route('roles.permissions.update', ['role' => ':roleId']) }}'.replace(':roleId', roleId),
      method: 'POST',
      data: formData,
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // safer for production
      },
      success: function(response) {
        $('#permissionsModal').modal('hide'); // Hide Bootstrap modal

        // Show success toast
        var toast = document.querySelector('.toast-placement-ex');
        toast.classList.remove('bg-danger');
        toast.classList.add('bg-success');
        toast.querySelector('.toast-body').textContent = 'Permissions updated successfully.';
        var toastInstance = new bootstrap.Toast(toast);
        toastInstance.show();

        // Optionally refresh or update table
        location.reload(); // Or: update row without reloading
      },
      error: function(xhr) {
        let msg = 'Failed to update permissions. Please try again.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
          msg = xhr.responseJSON.message;
        }
        alert(msg);
      }
    });
  });
});

</script> 

@endsection

@section('content')
<!-- Toast with Placements -->
<div class="bs-toast toast toast-placement-ex m-2" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
  <div class="toast-header">
    <i class='bx bx-bell me-2'></i>
    <div class="me-auto fw-medium">FTS</div>
    <small>Now</small>
    <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
  </div>
  <div class="toast-body">
    @if(Session::has('toast_success'))
      {{ Session::get('toast_success') }}
    @endif
    @if(Session::has('toast_error'))
      {{ Session::get('toast_error') }}
    @endif
  </div>
</div>
<!-- Toast with Placements -->

<h4 class="py-3 mb-4">
  <span class="text-muted fw-light">Roles /</span> All Roles
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Role Records</h5>
    <a href="{{ url('/roles/create') }}" class="btn btn-primary">
      <i class="bx bx-plus me-1"></i> Add New Role
    </a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead>
        <tr>
          <th>Role Name</th>
          <th>Level</th>
          <th>Permissions</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($roles as $role)
        <tr>
          <td>{{ $role->name }}</td>
          <td>{{ $role->level ?? '-' }}</td>
          <td>
            @if ($role->permissions && $role->permissions->count())
              <!-- {{ $role->permissions->pluck('name')->implode(', ') }}  -->
                <span class="cursor-pointer badge bg-label-warning">Permissions Assigned</span>
            @else
              <em>No permissions assigned</em>
            @endif
          </td>
          <td>
            <div>

            </div>
            <div class="d-flex gap-2">
              <button class="btn btn-sm btn-outline-primary manage-permissions-btn" 
                    onclick="managePermissions({{ $role->id }}, '{{ $role->name }}')">
                    Manage Permissions
              </button>
              <div>
                <a class="dropdown-item" href="{{ url('/roles/' . $role->id . '/edit') }}">
                  <i class="bx bx-show-alt me-1"></i> Edit
                </a>
              </div>
              <div>
                <form action="{{ url('/roles/' . $role->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this role?')">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="dropdown-item">
                    <i class="bx bx-trash me-1"></i> Delete
                  </button>
                </form>
              </div>
            </div>            
          </td>
        </tr>
        @empty
        <tr>
          <td colspan="4" class="text-center">No roles found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="card-footer">
    <div class="d-flex justify-content-center">
      {{ $roles->links() }}
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-labelledby="permissionsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="permissionsForm">
      @csrf
      <input type="hidden" name="role_id" id="modalRoleId" />
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="permissionsModalLabel">Manage Permissions for Role: <span id="modalRoleName"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="permissionsCheckboxes" class="row">
            <!-- Permissions checkboxes loaded here dynamically -->
            Loading permissions...
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Save Permissions</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  function managePermissions(roleId, roleName) {
    $('#permissionsModal').modal('show');
    $('#modalRoleId').val(roleId);
    $('#modalRoleName').text(roleName);
    $('#permissionsCheckboxes').html('Loading permissions...');

    $.ajax({
      url: '{{ route('roles.permissions', ['role' => ':roleId']) }}'.replace(':roleId', roleId),
      method: 'GET',
      success: function(data) {
        let html = '';

        Object.keys(data.permissions).forEach(function(group) {
          const groupName = group.charAt(0).toUpperCase() + group.slice(1).replace('_', ' ');
          html += `
            <div class="border rounded mb-3 p-3">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <strong>${groupName}</strong>
                <label class="text-sm text-primary cursor-pointer">
                  <input type="checkbox" class="select-all-group" data-group="${group}"> Select All
                </label>
              </div>
              <div class="row">`;

          data.permissions[group].forEach(function(perm) {
            const checked = data.assigned.includes(perm.id) ? 'checked' : '';
            html += `
              <div class="col-6 col-md-4">
                <div class="form-check">
                  <input class="form-check-input group-${group}" type="checkbox" name="permissions[]" value="${perm.id}" id="perm_${perm.id}" ${checked}>
                  <label class="form-check-label" for="perm_${perm.id}">${perm.name}</label>
                </div>
              </div>`;
          });

          html += `</div></div>`; // close row & card
        });

        $('#permissionsCheckboxes').html(html);

        // Select All logic
        $('.select-all-group').off('change').on('change', function () {
          const group = $(this).data('group');
          const checkboxes = $(`.group-${group}`);
          checkboxes.prop('checked', this.checked);
        });
      },
      error: function() {
        $('#permissionsCheckboxes').html('<p class="text-danger">Failed to load permissions.</p>');
      }
      });
  }



  
</script>
@endsection
