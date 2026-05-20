@extends('layouts.app')
@section('title', $pageTitle ?? 'Employee Data  ')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
<style>
    #loadingContainer { transition: opacity 0.3s ease; }
    .disabled-overlay { pointer-events: none; opacity: 0.5; filter: grayscale(100%); }
    .dataTables_wrapper .dataTables_paginate { display: flex; justify-content: flex-end; margin-top: 15px; }
    .badge-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; border: 1px solid rgba(25, 135, 84, 0.2); }
    .badge-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.2); }
    .text-primary { color: #AB2F2B !important; }
    .btn-primary { background-color: #AB2F2B; border-color: #AB2F2B; }
    .btn-outline-primary { color: #AB2F2B; border-color: #AB2F2B; }
    .btn-outline-primary:hover { background-color: #AB2F2B; color: white; }
    .page-item.active .page-link { background-color: #AB2F2B; border-color: #AB2F2B; }
    .select2-container--bootstrap-5 .select2-selection { font-size: 0.875rem; min-height: calc(1.5em + 0.5rem + 2px); }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
            <h1 class="h5 mb-0 fw-bold text-primary">
                {{ $pageTitle ?? 'Employee Synchronization' }}
            </h1>
            
            <button type="button" id="btnExport" class="btn btn-success btn-sm fw-bold shadow-sm">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Export to Excel
            </button>
        </div>

        <div class="card-body">
            <div id="filterWrapper">
                <form id="filterForm" class="mb-4 bg-light p-3 rounded">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label class="form-label mb-1 small text-muted fw-bold">Business Unit</label>
                            <select name="business_unit" id="business_unit" class="form-select filter-input">
                                <option value="">All Business Units</option>
                                @foreach($filterOptions['businessUnits'] ?? [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label mb-1 small text-muted fw-bold">Job Level</label>
                            <select name="job_level" id="job_level" class="form-select filter-input">
                                <option value="">All Job Levels</option>
                                 @foreach($filterOptions['jobLevels'] ?? [] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <label class="form-label mb-1 small text-muted fw-bold">Data Status</label>
                            <select name="data_status" id="data_status" class="form-select filter-input">
                                <option value="">All Status</option>
                                <option value="sync">Match</option>
                                <option value="unsync">Mismatch</option>
                            </select>
                        </div>
                        
                        <!-- Perubahan: Filter Verification diganti ke Employee Status (Default: Active) -->
                        <div class="col-md-2">
                            <label class="form-label mb-1 small text-muted fw-bold">Employee Status</label>
                            <select name="employee_status" id="employee_status" class="form-select filter-input">
                                <option value="">All Status</option>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                        
                        <div class="col-md-2">
                            <button type="button" id="resetBtn" class="btn btn-outline-secondary w-100 btn-sm" style="height: 35px;">
                                <i class="bi bi-arrow-clockwise me-1"></i> Reset Filters
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="alert alert-info bg-opacity-10 border-info border-opacity-25 small mb-3 py-2 text-dark">
                <i class="bi bi-info-circle-fill text-info me-2"></i> The list shows comparison status between HCIS and Andal data.
            </div>

            <div id="loadingContainer" class="d-none mb-4 px-2">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-bold text-primary" id="loadingText">Preparing data...</span>
                    <span class="small fw-bold text-primary" id="loadingPercent">0%</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 0%"></div>
                </div>
            </div>

            <div class="table-responsive">
                <table id="employeesTable" class="table table-hover table-striped small align-middle w-100">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center align-middle" style="width: 5%;">No</th>
                            <th class="align-middle">Employee ID</th>
                            <th class="align-middle">Employee Name</th>
                            <th class="align-middle">Business Unit</th>
                            <th class="align-middle text-center">Data Status</th>
                            <th class="align-middle text-center">Check Status</th>
                            <th class="text-center align-middle" style="width: 10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let isLoading = false;

    $('.filter-input').select2({
        theme: 'bootstrap-5',
        width: '100%',
        minimumResultsForSearch: 5
    });

    let table = $('#employeesTable').DataTable({
        dom: "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
        autoWidth: false,
        responsive: true,
        deferRender: true,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        columns: [
            { data: 'no', className: 'text-center' },
            { data: 'employee_id', className: 'fw-bold text-center' },
            { data: 'fullname' },
            { data: 'group_company' },
            { 
                data: 'sync_status',
                className: 'text-center',
                render: function(data) {
                    if(data) return `<span class="badge badge-soft-success rounded-pill px-3"><i class="bi bi-check-circle-fill me-1"></i> Match</span>`;
                    return `<span class="badge badge-soft-danger rounded-pill px-3"><i class="bi bi-x-circle-fill me-1"></i> Mismatch</span>`;
                }
            },
            {
                data: 'is_checked',
                className: 'text-center',
                render: function(data) {
                    if (data) return `<span class="badge bg-primary rounded-pill"><i class="bi bi-check2-all me-1"></i> Checked</span>`;
                    return `<span class="badge bg-warning text-dark rounded-pill">Pending</span>`;
                }
            },
            {
                data: 'action_url',
                className: 'text-center',
                orderable: false,
                render: function(data) {
                    return `<a href="${data}" target="_blank" class="btn btn-sm btn-outline-primary" title="View Detail"><i class="bi bi-layout-split me-1"></i> Detail</a>`;
                }
            }
        ]
    });

    async function loadData() {
        if (isLoading) return;
        isLoading = true;
        
        $('#loadingContainer').removeClass('d-none');
        $('#filterWrapper').addClass('disabled-overlay');
        $('.filter-input').prop('disabled', true);
        $('#resetBtn').prop('disabled', true);
        table.clear().draw(); 

        let page = 1;
        let hasMore = true;
        let totalLoaded = 0;
        let totalRecords = 0;

        const filters = {
            business_unit: $('#business_unit').val(),
            job_level: $('#job_level').val(),
            data_status: $('#data_status').val(),
            employee_status: $('#employee_status').val() // Mengirim parameter status karyawan baru
        };

        try {
            while (hasMore) {
                const response = await $.ajax({
                    url: "{{ route('employees.chunk') }}",
                    method: "GET",
                    data: { ...filters, page: page }
                });

                if (page === 1) totalRecords = response.total;

                table.rows.add(response.data);
                
                totalLoaded += response.data.length;
                let percent = totalRecords > 0 ? Math.round((totalLoaded / totalRecords) * 100) : 100;
                $('#progressBar').css('width', percent + '%');
                $('#loadingPercent').text(percent + '%');

                hasMore = response.has_more;
                page++;
                await new Promise(r => setTimeout(r, 20)); 
            }
            table.draw();
        } catch (error) {
            Swal.fire('Error', 'Failed to load data.', 'error');
        } finally {
            isLoading = false;
            setTimeout(() => {
                $('#loadingContainer').addClass('d-none');
                $('#progressBar').css('width', '0%');
                $('#filterWrapper').removeClass('disabled-overlay');
                $('.filter-input').prop('disabled', false);
                $('#resetBtn').prop('disabled', false);
            }, 500);
        }
    }

    $('.filter-input').on('change', function() {
        if (!isLoading) loadData();
    });

    $('#resetBtn').click(function(){
        if(isLoading) return;
        // Mengembalikan filter lain ke null, tetapi 'employee_status' kembali ke default 'active'
        $('#business_unit').val(null).trigger('change.select2');
        $('#job_level').val(null).trigger('change.select2');
        $('#data_status').val(null).trigger('change.select2');
        $('#employee_status').val('active').trigger('change.select2');
        loadData();
    });

    $('#btnExport').click(function() {
        const params = new URLSearchParams({
            business_unit: $('#business_unit').val() || '',
            job_level: $('#job_level').val() || '',
            data_status: $('#data_status').val() || '',
            employee_status: $('#employee_status').val() || ''
        });
        window.location.href = "{{ route('employees.export') }}?" + params.toString();
    });

    loadData();
});
</script>
@endpush