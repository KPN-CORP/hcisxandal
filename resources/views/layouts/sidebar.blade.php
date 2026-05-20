@push('navbar-right')
    @php $employeeData = Auth::user()->employee; @endphp
    <div class="d-flex align-items-center">
        <div class="flex-shrink-0">
            @if($employeeData && $employeeData->user && $employeeData->user->img_path)
                <img src="{{ Storage::url('app/'.$employeeData->user->img_path) }}" alt="User" class="rounded-circle border" style="width: 40px; height: 40px; object-fit: cover;">
            @else
                <div class="rounded-circle text-white d-flex align-items-center justify-content-center bg-kpn fw-bold" style="width: 40px; height: 40px; font-size: 1.2rem;">
                    {{ substr($employeeData->fullname ?? Auth::user()->name, 0, 1) }}
                </div>
            @endif
        </div>
        <div class="flex-grow-1 ms-3 text-end d-none d-sm-block">
            <span class="fw-bold d-block text-dark">{{ $employeeData->fullname ?? Auth::user()->name }}</span>
            @if($employeeData && $employeeData->employee_id)
                <span class="text-muted d-block small">{{ $employeeData->employee_id }}</span>
            @endif
        </div>
    </div>
@endpush

<aside class="sidebar py-4 px-3 shadow-sm">
    <div class="d-flex flex-column align-items-center mb-4 pb-3 border-bottom">
        <img src="{{ asset('images/kpn-logo.png') }}" alt="KPN Corp Logo" style="height: 50px;">
        <h2 class="h6 mb-0 mt-3 text-kpn fw-bold">HC SYSTEM</h2>
    </div>

    <p class="text-muted small text-uppercase fw-bold mb-2 ps-3">Main Menu</p>
    <nav class="nav flex-column gap-2">
        <a href="{{ route('employees.list') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active bg-danger-subtle text-kpn fw-bold' : 'text-dark' }} rounded d-flex align-items-center px-3 py-2 transition-all">
            <i class="bi bi-people me-3 fs-5"></i> <span>Employee Data</span>
        </a>

        <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') ? 'active bg-danger-subtle text-kpn fw-bold' : 'text-dark' }} rounded d-flex align-items-center px-3 py-2 transition-all">
            <i class="bi bi-gear me-3 fs-5"></i> <span>Role Setting</span>
        </a>
    </nav>

    <div class="mt-auto pt-3 border-top">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-kpn w-100 fw-bold d-flex align-items-center justify-content-center py-2">
                <i class="bi bi-box-arrow-right me-2"></i> Logout
            </button>
        </form>
    </div>
</aside>