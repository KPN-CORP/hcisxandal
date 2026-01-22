@extends('layouts.app')
@section('title', 'Role Setting')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    /* Styling TomSelect agar match tema */
    .ts-control { border-radius: 0.375rem; padding: 0.5rem 0.75rem; }
    .ts-wrapper.multi .ts-control > div { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 2px 6px; }
    
    /* Warna Merah KPN */
    .text-primary { color: #AB2F2B !important; }
    .bg-primary { background-color: #AB2F2B !important; }
    .btn-primary { background-color: #AB2F2B; border-color: #AB2F2B; font-weight: 600; }
    .btn-primary:hover { background-color: #8f2623; border-color: #8f2623; }
    .btn-outline-danger { color: #dc3545; border-color: #dc3545; }
    .btn-outline-danger:hover { background-color: #dc3545; color: white; }

    /* Nav Tabs Custom */
    .nav-pills .nav-link.active { background-color: #AB2F2B; }
    .nav-pills .nav-link { color: #6c757d; font-weight: 600; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 fw-bold text-primary">Role Management</h1>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            
            {{-- Tabs --}}
            <ul class="nav nav-pills mb-4" id="roleTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active px-4" data-bs-toggle="pill" data-bs-target="#createRole">
                        <i class="bi bi-plus-lg me-2"></i>Create Role
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link px-4" data-bs-toggle="pill" data-bs-target="#manageRole">
                        <i class="bi bi-gear me-2"></i>Manage Role
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                
                {{-- 1. CREATE ROLE --}}
                <div class="tab-pane fade show active" id="createRole">
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">ROLE NAME</label>
                            <input type="text" name="role_name" class="form-control" required placeholder="e.g. HR Admin">
                        </div>

                        <div class="card bg-light border-0 mb-4">
                            <div class="card-body">
                                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-funnel me-2"></i>Scope Filters (Restricted View)</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">BUSINESS UNIT</label>
                                        <select name="business_unit[]" class="tom-select-filter" multiple placeholder="Select Business Units...">
                                            @foreach($filterData['businessUnits'] as $bu) <option value="{{$bu->group_company}}">{{$bu->group_company}}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">COMPANY</label>
                                        <select name="company[]" class="tom-select-filter" multiple placeholder="Select Companies...">
                                            @foreach($filterData['companies'] as $c) <option value="{{$c->company_name}}">{{$c->company_name}}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-muted">LOCATION</label>
                                        <select name="location[]" class="tom-select-filter" multiple placeholder="Select Locations...">
                                            @foreach($filterData['locations'] as $l) <option value="{{$l->office_area}}">{{$l->office_area}}</option> @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">ASSIGN EMPLOYEES</label>
                            <select name="employee_ids[]" id="create_employees" multiple placeholder="Search & Select Employees...">
                                @foreach($employees as $emp) 
                                    <option value="{{$emp->employee_id}}">{{$emp->fullname}} ({{$emp->employee_id}})</option> 
                                @endforeach
                            </select>
                            <div class="form-text text-muted">Only employees with user accounts can be assigned.</div>
                        </div>

                        <div class="text-end border-top pt-3">
                            <button type="submit" class="btn btn-primary px-5 shadow-sm">Save Role</button>
                        </div>
                    </form>
                </div>

                {{-- 2. MANAGE ROLE --}}
                <div class="tab-pane fade" id="manageRole">
                    <div class="mb-4">
                        <label class="form-label fw-bold">Select Role to Edit:</label>
                        <select id="roleSelector" class="form-select">
                            <option value="">-- Choose Role --</option>
                            @foreach($roles as $role) <option value="{{ $role->id }}">{{ $role->name }}</option> @endforeach
                        </select>
                    </div>

                    <div id="editFormContainer" style="display: none;">
                        <form id="editForm" method="POST">
                            @csrf @method('PUT')
                            
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-muted">ROLE NAME</label>
                                <input type="text" name="role_name" id="edit_role_name" class="form-control" required>
                            </div>

                            <div class="card bg-light border-0 mb-4">
                                <div class="card-body">
                                    <h6 class="fw-bold text-dark mb-3">Scope Filters</h6>
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted fw-bold">BUSINESS UNIT</label>
                                            <select name="business_unit[]" id="edit_bu" class="tom-select-edit" multiple>
                                                @foreach($filterData['businessUnits'] as $bu) <option value="{{$bu->group_company}}">{{$bu->group_company}}</option> @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted fw-bold">COMPANY</label>
                                            <select name="company[]" id="edit_company" class="tom-select-edit" multiple>
                                                @foreach($filterData['companies'] as $c) <option value="{{$c->company_name}}">{{$c->company_name}}</option> @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small text-muted fw-bold">LOCATION</label>
                                            <select name="location[]" id="edit_loc" class="tom-select-edit" multiple>
                                                @foreach($filterData['locations'] as $l) <option value="{{$l->office_area}}">{{$l->office_area}}</option> @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold small text-muted">ASSIGNED EMPLOYEES</label>
                                <select name="employee_ids[]" id="edit_employees" multiple>
                                    @foreach($employees as $emp) 
                                        <option value="{{$emp->employee_id}}">{{$emp->fullname}} ({{$emp->employee_id}})</option> 
                                    @endforeach
                                </select>
                            </div>

                            <div class="d-flex justify-content-between border-top pt-3">
                                <button type="button" class="btn btn-outline-danger" id="btnDeleteRole"><i class="bi bi-trash me-1"></i> Delete Role</button>
                                <button type="submit" class="btn btn-primary px-5 shadow-sm">Update Changes</button>
                            </div>
                        </form>
                        
                        <form id="deleteForm" method="POST" class="d-none">@csrf @method('DELETE')</form>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const allRoles = @json($roles->keyBy('id'));
    
    // Helper TomSelect Config
    const tsConfig = { 
        plugins: ['dropdown_input', 'remove_button'], 
        maxOptions: null,
        render: {
            option: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            },
            item: function(data, escape) {
                return '<div>' + escape(data.text) + '</div>';
            }
        } 
    };

    $(document).ready(function() {
        // Init TomSelect for Create Tab
        document.querySelectorAll('.tom-select-filter').forEach(el => new TomSelect(el, tsConfig));
        new TomSelect('#create_employees', tsConfig);

        // Init TomSelect for Edit Tab (Simpan instance agar bisa diset value nanti)
        let tsEditBU = new TomSelect('#edit_bu', tsConfig);
        let tsEditComp = new TomSelect('#edit_company', tsConfig);
        let tsEditLoc = new TomSelect('#edit_loc', tsConfig);
        let tsEditEmp = new TomSelect('#edit_employees', tsConfig);

        // Populate Edit Form
        $('#roleSelector').change(function() {
            let roleId = $(this).val();
            if(!roleId) {
                $('#editFormContainer').slideUp();
                return;
            }

            let role = allRoles[roleId];
            
            // Set Form Action
            $('#editForm').attr('action', `/roles/${roleId}`);
            $('#deleteForm').attr('action', `/roles/${roleId}`);

            // Fill Basic Info
            $('#edit_role_name').val(role.name);

            // Fill Filters (TomSelect setValue)
            tsEditBU.setValue(role.business_unit || []);
            tsEditComp.setValue(role.company || []);
            tsEditLoc.setValue(role.location || []);

            // Fill Users
            let userIds = role.users.map(u => u.employee_id);
            tsEditEmp.setValue(userIds);

            // Disable delete if System Role
            if(role.name === 'Manager' || role.name === 'Superior') {
                $('#btnDeleteRole').hide();
                $('#edit_role_name').prop('readonly', true).addClass('bg-light');
            } else {
                $('#btnDeleteRole').show();
                $('#edit_role_name').prop('readonly', false).removeClass('bg-light');
            }

            $('#editFormContainer').slideDown();
        });

        // Delete Confirmation
        $('#btnDeleteRole').click(function() {
            Swal.fire({
                title: 'Delete Role?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#AB2F2B',
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) $('#deleteForm').submit();
            });
        });
    });
</script>
@endpush