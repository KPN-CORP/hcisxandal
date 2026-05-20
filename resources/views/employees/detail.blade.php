@extends('layouts.app')
@section('title', 'Employee Detail')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .text-primary { color: #AB2F2B !important; }
    .bg-primary { background-color: #AB2F2B !important; }
    .btn-primary { background-color: #AB2F2B; border-color: #AB2F2B; }
    .btn-primary:hover, .btn-primary:active, .btn-primary:focus { background-color: #8f2623 !important; border-color: #8f2623 !important; }
    .btn-outline-primary { color: #AB2F2B; border-color: #AB2F2B; }
    .btn-outline-primary:hover { background-color: #AB2F2B; color: white; }
    
    .info-box { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 10px 15px; height: 100%; }
    .info-label { display: block; font-size: 0.7rem; text-transform: uppercase; color: #6c757d; font-weight: 600; letter-spacing: 0.5px; margin-bottom: 4px; }
    .info-value { display: block; font-size: 0.95rem; font-weight: 700; color: #212529; }
    
    .form-switch .form-check-input {
        width: 3em; 
        height: 1.5em;
        cursor: pointer;
    }

    /* Sticky Column Feature untuk Mobile */
    .table-responsive {
        position: relative;
    }
    .table-sticky-column th:first-child,
    .table-sticky-column td:first-child {
        position: sticky;
        left: 0;
        z-index: 1;
        background-color: inherit;
        box-shadow: inset -1px 0 0 #dee2e6;
    }
    .table-sticky-column thead tr th:first-child {
        z-index: 2;
        background-color: #212529; /* Sesuai table-dark */
        box-shadow: inset -1px 0 0 #373b3e;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-3">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h5 mb-0 fw-bold text-primary">Comparison Detail</h1>
            <p class="text-muted small mb-0">Employee Data Verification</p>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <div class="row g-4 align-items-center">
                <div class="col-md-8 border-lg-end">
                    <span class="text-secondary small fw-bold">
                        {{ $employee->employee_id }}
                    </span>
                    <h5 class="fw-bold text-dark mb-2">{{ $employee->fullname }}</h5>
                    <p class="text-dark">{{ $employee->email }}</p>
                    <small>
                        Last Updated: {{ (isset($andalEmployee) && $andalEmployee->data_pull) ? \Carbon\Carbon::parse($andalEmployee->data_pull)->format('d M Y') : '-' }}
                    </small>
                </div>

                <div class="col-md-4">
                    <div class="d-flex flex-column align-items-start align-items-lg-end justify-content-center h-100">
                        
                        <div class="form-check form-switch d-flex align-items-center gap-3">
                            <div>
                                <label class="form-check-label fw-bold {{ $isChecked ? 'text-success' : 'text-muted' }}" for="verificationSwitch">
                                    {{ $isChecked ? 'Verified & Checked' : 'Mark as Checked' }}
                                </label>
                                @if($isChecked)
                                    <small class="d-block text-muted text-start text-lg-end" style="font-size: 0.75rem;">
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
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 h6 fw-bold text-primary"><i class="bi bi-table me-2"></i>Comparison Data</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped table-sticky-column mb-0 small align-middle" style="min-width: 600px;">
                    <thead class="table-dark">
                        <tr>
                            <th style="width: 20%;">Attribute</th>
                            <th>Andal Data</th>
                            <th>HC System Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($comparison as $data)
                            <tr>
                                <td class="fw-bold bg-white text-secondary">
                                    {{ $data['description'] }}
                                </td>
                                
                                <td class="{{ $data['is_match'] ? 'text-dark' : 'text-primary fw-bold' }}">
                                    @if($data['is_match'])
                                        {{ $data['andal_value'] }}
                                    @else
                                        {{ $data['andal_value'] }}
                                        <i class="bi bi-exclamation-triangle-fill ms-2 text-primary" data-bs-toggle="tooltip" title="Data Mismatch"></i>
                                    @endif
                                </td>
                                
                                <td class="{{ $data['is_match'] ? 'text-dark' : 'text-primary fw-bold' }}">
                                    @if($data['is_match'])
                                         {{ $data['hcis_value'] }}
                                    @else
                                        {{ $data['hcis_value'] }}
                                        <i class="bi bi-exclamation-triangle-fill ms-2 text-primary" data-bs-toggle="tooltip" title="Data Mismatch"></i>
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
                        form.submit();
                    } else {
                        this.checked = !isChecking;
                    }
                });
            });
        }

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