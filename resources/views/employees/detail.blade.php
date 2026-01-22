@extends('layouts.app')
@section('title', 'Comparison Detail')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .text-primary { color: #AB2F2B !important; }
    .bg-primary { background-color: #AB2F2B !important; }
    .btn-primary { background-color: #AB2F2B; border-color: #AB2F2B; }
    .btn-primary:hover, .btn-primary:active, .btn-primary:focus { background-color: #8f2623 !important; border-color: #8f2623 !important; }
    .btn-outline-primary { color: #AB2F2B; border-color: #AB2F2B; }
    .btn-outline-primary:hover { background-color: #AB2F2B; color: white; }
    
    /* Soft Badges */
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); color: #198754; border: 1px solid rgba(25, 135, 84, 0.2); }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.2); }
    
    /* Layout Details */
    .info-box { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 10px 15px; height: 100%; }
    .info-label { display: block; font-size: 0.7rem; text-transform: uppercase; color: #6c757d; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px; }
    .info-value { display: block; font-size: 0.95rem; font-weight: 700; color: #212529; }
    
    .table-custom th { background-color: #f1f3f5; color: #495057; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; border-bottom: 2px solid #dee2e6; }
    .table-custom td { vertical-align: middle; padding: 12px 15px; }

    /* Custom Switch Size */
    .form-switch .form-check-input {
        width: 3em; 
        height: 1.5em;
        cursor: pointer;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-primary">Comparison Detail</h1>
            <p class="text-muted small mb-0">Employee Data Verification</p>
        </div>
        <a href="{{ route('employees.list') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-4">
            <div class="row g-4 align-items-center">
                <div class="col-lg-4 border-end">
                    <h2 class="h4 fw-bold text-dark mb-1">{{ $employee->fullname }}</h2>
                    <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 rounded-pill">
                        <i class="bi bi-person-badge me-1"></i> {{ $employee->employee_id }}
                    </span>
                </div>

                {{-- Data Pull Info --}}
                <div class="col-lg-4">
                    <div class="row g-2">
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-label"><i class="bi bi-database me-1"></i> HCIS Data Pull</span>
                                <span class="info-value">
                                    {{ optional($employee)->data_pull_date ? \Carbon\Carbon::parse($employee->data_pull_date)->format('d M Y') : '-' }}
                                    <small class="text-muted fw-normal ms-1">{{ optional($employee)->data_pull_date ? \Carbon\Carbon::parse($employee->data_pull_date)->format('H:i') : '' }}</small>
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="info-box">
                                <span class="info-label"><i class="bi bi-database me-1"></i> ANDAL Data Pull</span>
                                <span class="info-value">
                                    {{ (isset($andalEmployee) && $andalEmployee->data_pull_date) ? \Carbon\Carbon::parse($andalEmployee->data_pull_date)->format('d M Y') : '-' }}
                                    <small class="text-muted fw-normal ms-1">{{ (isset($andalEmployee) && $andalEmployee->data_pull_date) ? \Carbon\Carbon::parse($andalEmployee->data_pull_date)->format('H:i') : '' }}</small>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Verification Action --}}
                <div class="col-lg-4">
                    <div class="d-flex flex-column align-items-end justify-content-center h-100">
                        
                        <div class="form-check form-switch d-flex align-items-center gap-3">
                            <div>
                                <label class="form-check-label fw-bold {{ $isChecked ? 'text-success' : 'text-muted' }}" for="verificationSwitch">
                                    {{ $isChecked ? 'Verified & Checked' : 'Mark as Checked' }}
                                </label>
                                @if($isChecked)
                                    <small class="d-block text-muted text-end" style="font-size: 0.75rem;">
                                        By {{ $checkerName }} <br>
                                        {{ \Carbon\Carbon::parse($checkedAt)->format('d M Y, H:i') }}
                                    </small>
                                @endif
                            </div>
                            <input class="form-check-input m-0" type="checkbox" role="switch" id="verificationSwitch" {{ $isChecked ? 'checked' : '' }}>
                        </div>
                        <form id="verifyForm" action="{{ route('employees.confirm', $employee->employee_id) }}" method="POST">
                            @csrf
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-table me-2 text-primary"></i>Comparison Data</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-custom mb-0">
                    <thead>
                        <tr>
                            <th style="width: 30%; padding-left: 1.5rem;">Field Description</th>
                            <th style="width: 35%;">Data ANDAL (Payroll)</th>
                            <th style="width: 35%;">Data HCIS (Source)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparison as $data)
                            <tr class="{{ $data['is_match'] ? '' : 'bg-soft-danger' }}">
                                <td class="fw-bold text-secondary" style="padding-left: 1.5rem;">
                                    {{ $data['description'] }}
                                </td>
                                
                                {{-- Data Andal --}}
                                <td class="{{ $data['is_match'] ? 'text-success' : 'text-danger fw-bold' }}">
                                    {{ $data['andal_value'] }}
                                </td>
                                
                                {{-- Data HCIS --}}
                                <td class="{{ $data['is_match'] ? 'text-success' : 'text-danger fw-bold' }}">
                                    {{ $data['hcis_value'] }}
                                    @if(!$data['is_match'])
                                        <i class="bi bi-exclamation-triangle-fill ms-2" data-bs-toggle="tooltip" title="Data Mismatch"></i>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })

        const switchBtn = document.getElementById('verificationSwitch');
        const form = document.getElementById('verifyForm');

        if (switchBtn) {
            switchBtn.addEventListener('change', function(e) {
                const isChecking = this.checked;
                
                const title = isChecking ? 'Confirm Verification?' : 'Undo Verification?';
                const text = isChecking 
                    ? "You are validating this data as correct. This will be logged."
                    : "This will revert the status to 'Pending' (Unchecked).";
                const icon = isChecking ? 'question' : 'warning';
                const confirmBtnColor = isChecking ? '#AB2F2B' : '#dc3545'; 
                const confirmBtnText = isChecking ? 'Yes, Check it!' : 'Yes, Undo it!';

                Swal.fire({
                    title: title,
                    text: text,
                    icon: icon,
                    showCancelButton: true,
                    confirmButtonColor: confirmBtnColor,
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: confirmBtnText,
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Submit form jika user setuju
                        form.submit();
                    } else {
                        // Kembalikan posisi switch jika cancel
                        this.checked = !isChecking;
                    }
                });
            });
        }

        // Logic Tombol Verify (Mark as Checked)
        const btnVerify = document.getElementById('btnVerify');
        if (btnVerify) {
            btnVerify.addEventListener('click', function() {
                Swal.fire({
                    title: 'Mark as Checked?',
                    text: "You are confirming that you have reviewed this data comparison.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#AB2F2B',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Verify It!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('verifyForm').submit();
                    }
                });
            });
        }

        const btnUndo = document.getElementById('btnUndo');
        if (btnUndo) {
            btnUndo.addEventListener('click', function() {
                Swal.fire({
                    title: 'Undo Verification?',
                    text: "This will revert the status to 'Pending'.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545', 
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Undo It!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('verifyForm').submit();
                    }
                });
            });
        }
    });
</script>
@endpush