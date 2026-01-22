@extends('layouts.app')
@section('title', $pageTitle ?? 'Employee Synchronization')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
<style>
    #loadingContainer { transition: opacity 0.3s ease; }
    .disabled-overlay { pointer-events: none; opacity: 0.5; filter: grayscale(100%); }
    .dataTables_wrapper .dataTables_paginate { display: flex; justify-content: flex-end; margin-top: 10px; }
    .ts-control { border-radius: 0.25rem; padding: 0.375rem 0.75rem; font-size: 0.875rem; }
    .badge-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; border: 1px solid rgba(25, 135, 84, 0.2); }
    .badge-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.2); }
    .text-primary { color: #AB2F2B !important; }
    .btn-primary { background-color: #AB2F2B; border-color: #AB2F2B; }
    .btn-outline-primary { color: #AB2F2B; border-color: #AB2F2B; }
    .btn-outline-primary:hover { background-color: #AB2F2B; color: white; }
    .page-item.active .page-link { background-color: #AB2F2B; border-color: #AB2F2B; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        {{-- HEADER CARD --}}
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h1 class="h4 mb-0 fw-bold text-primary">
                {{ $pageTitle ?? 'Employee Synchronization' }}
            </h1>
            
            <button type="button" id="btnExport" class="btn btn-success btn-sm fw-bold shadow-sm">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Export to Excel
            </button>
        </div>

        <div class="card-body">
            {{-- FILTER SECTION --}}
            <div id="filterWrapper">
                <form id="filterForm" class="mb-2">
                    <div class="row g-2 align-items-end">
                        {{-- 1. Business Unit --}}
                        <div class="col-md-3">
                            <label class="form-label mb-1 small text-muted">Business Unit</label>
                            <select name="business_unit" id="business_unit" class="form-select form-select-sm filter-input">
                                <option value="">All Business Units</option>
                                @foreach($filterOptions['businessUnits'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- 2. Job Level --}}
                        <div class="col-md-2">
                            <label class="form-label mb-1 small text-muted">Job Level</label>
                            <select name="job_level" id="job_level" class="form-select form-select-sm filter-input">
                                <option value="">All Job Levels</option>
                                 @foreach($filterOptions['jobLevels'] as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- 3. Data Status (Sync/Unsync) --}}
                        <div class="col-md-2">
                            <label class="form-label mb-1 small text-muted">Data Status</label>
                            <select name="data_status" id="data_status" class="form-select form-select-sm filter-input">
                                <option value="">All Statuses</option>
                                <option value="sync">Synchronized (Match)</option>
                                <option value="unsync">Unsynchronized (Mismatch)</option>
                            </select>
                        </div>
                        {{-- 4. Check Status (Verified/Pending) --}}
                        <div class="col-md-2">
                            <label class="form-label mb-1 small text-muted">Verification</label>
                            <select name="check_status" id="check_status" class="form-select form-select-sm filter-input">
                                <option value="">All Verifications</option>
                                <option value="checked">Verified & Checked</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        {{-- Reset Button --}}
                        <div class="col-md-3 d-flex justify-content-end gap-2">
                            <button type="button" id="resetBtn" class="btn btn-outline-secondary btn-sm w-100">Reset Filters</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mb-3">
                <small class="text-muted fst-italic">
                    Note: The list shows comparison status between HCIS and Andal data.
                </small>
            </div>

            {{-- PROGRESS BAR --}}
            <div id="loadingContainer" class="d-none mb-3">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-bold text-primary" id="loadingText">Preparing data...</span>
                    <span class="small fw-bold text-primary" id="loadingPercent">0%</span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-danger" role="progressbar" style="width: 0%"></div>
                </div>
            </div>

            {{-- TABLE SECTION --}}
            <div class="table-responsive">
                <table id="employeesTable" class="table table-hover small align-middle w-100">
                    <thead class="table-secondary">
                        <tr>
                            <th class="text-center align-middle" style="width: 5%;">No</th>
                            <th class="align-middle">Employee ID</th>
                            <th class="align-middle">Employee Name</th>
                            <th class="align-middle">Business Unit</th>
                            <th class="align-middle">Data Status</th>
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
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    let table;
    let isLoading = false;
    let tomSelects = [];

    // 1. Init DataTable
    table = $('#employeesTable').DataTable({
        dom: "<'row mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" +
             "<'row'<'col-sm-12'tr>>" +
             "<'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-end'p>>",
        autoWidth: false,
        responsive: true,
        deferRender: true,
        pageLength: 10,
        lengthMenu: [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
        columnDefs: [
            { targets: '_all', className: 'dt-head-center' },
            { orderable: false, targets: 6 },
            { targets: 0, data: 'no', className: 'text-center' },
            { targets: 1, data: 'employee_id', className: 'fw-bold' },
            { targets: 2, data: 'fullname' },
            { targets: 3, data: 'group_company' },
            { 
                targets: 4, 
                data: 'sync_status',
                render: function(data) {
                    if(data) return `<span class="badge badge-soft-success rounded-pill px-3"><i class="bi bi-check-circle-fill me-1"></i> Match</span>`;
                    return `<span class="badge badge-soft-danger rounded-pill px-3"><i class="bi bi-x-circle-fill me-1"></i> Mismatch</span>`;
                }
            },
            {
                targets: 5,
                data: null,
                className: 'text-center',
                render: function(data, type, row) {
                    if (row.is_checked) return `<span class="badge bg-primary rounded-pill"><i class="bi bi-check2-all me-1"></i> Checked</span>`;
                    return `<span class="badge bg-warning text-dark">Pending</span>`;
                }
            },
            {
                targets: 6,
                data: 'action_url',
                className: 'text-center',
                render: function(data) {
                    return `<a href="${data}" class="btn btn-sm btn-outline-primary" title="View Detail"><i class="bi bi-layout-split me-1"></i> Detail</a>`;
                }
            }
        ]
    });

    // 2. Logic Export
    $('#btnExport').click(function() {
        const params = new URLSearchParams({
            business_unit: $('#business_unit').val() || '',
            job_level: $('#job_level').val() || '',
            data_status: $('#data_status').val() || '',
            check_status: $('#check_status').val() || ''
        });
        window.location.href = "{{ route('employees.export') }}?" + params.toString();
    });

    // 3. Load Data
    async function loadData() {
        if (isLoading) return;
        isLoading = true;
        
        $('#loadingContainer').removeClass('d-none');
        $('#filterWrapper').addClass('disabled-overlay');
        tomSelects.forEach(ts => ts.lock());
        $('#resetBtn').prop('disabled', true);
        table.clear().draw(); 

        let page = 1;
        let hasMore = true;
        let totalLoaded = 0;
        let totalRecords = 0;

        // Ambil filter values
        const filters = {
            business_unit: $('#business_unit').val(),
            job_level: $('#job_level').val(),
            data_status: $('#data_status').val(),
            check_status: $('#check_status').val(),
            _token: '{{ csrf_token() }}'
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
            console.error(error);
            Swal.fire('Error', 'Failed to load data.', 'error');
        } finally {
            isLoading = false;
            setTimeout(() => {
                $('#loadingContainer').addClass('d-none');
                $('#progressBar').css('width', '0%');
            }, 500);
            $('#filterWrapper').removeClass('disabled-overlay');
            tomSelects.forEach(ts => ts.unlock());
            $('#resetBtn').prop('disabled', false);
        }
    }

    // 4. Init TomSelect
    const selectSettings = {
        plugins: ['dropdown_input'],
        allowEmptyOption: true,
        onChange: function() { if(!isLoading) loadData(); }
    };

    $('.filter-input').each(function() {
        let ts = new TomSelect(this, selectSettings);
        tomSelects.push(ts);
    });
    
    $('#resetBtn').click(function(){
        if(isLoading) return;
        window.location.reload(); 
    });

    loadData();
});
</script>
@endpush