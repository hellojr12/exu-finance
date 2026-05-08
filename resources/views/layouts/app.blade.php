<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — EXU Finance</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #0f172a;
            --sidebar-active: #1e293b;
            --sidebar-text: #94a3b8;
            --sidebar-text-active: #f1f5f9;
            --sidebar-accent: #3b82f6;
            --topbar-height: 60px;
            --body-bg: #f1f5f9;
        }

        body { background: var(--body-bg); font-family: 'Inter', system-ui, sans-serif; }

        /* ── Sidebar ───────────────────────────────── */
        #sidebar {
            position: fixed; top: 0; left: 0; bottom: 0;
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            z-index: 1030;
            transition: transform .25s ease;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .sidebar-brand {
            display: flex; align-items: center; gap: .75rem;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #1e293b;
        }
        .sidebar-brand .brand-icon {
            width: 38px; height: 38px; border-radius: 10px;
            background: var(--sidebar-accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem; color: #fff;
        }
        .sidebar-brand .brand-text { color: #f1f5f9; font-size: .95rem; font-weight: 700; line-height: 1.2; }
        .sidebar-brand .brand-sub  { color: var(--sidebar-text); font-size: .72rem; }

        .sidebar-section { padding: .5rem 1rem .25rem; font-size: .68rem; font-weight: 600;
            letter-spacing: .08em; color: #475569; text-transform: uppercase; margin-top: .5rem; }

        .sidebar-nav { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav .nav-item { margin: 1px 0.5rem; }
        .sidebar-nav .nav-link {
            display: flex; align-items: center; gap: .75rem;
            padding: .55rem .9rem; border-radius: 8px;
            color: var(--sidebar-text); text-decoration: none;
            font-size: .875rem; transition: all .15s;
        }
        .sidebar-nav .nav-link:hover { background: var(--sidebar-active); color: var(--sidebar-text-active); }
        .sidebar-nav .nav-link.active { background: var(--sidebar-accent); color: #fff; }
        .sidebar-nav .nav-link i { font-size: 1.05rem; width: 20px; text-align: center; }

        /* ── Top Bar ───────────────────────────────── */
        #topbar {
            position: fixed; top: 0; left: var(--sidebar-width); right: 0;
            height: var(--topbar-height); z-index: 1020;
            background: #fff; border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; padding: 0 1.5rem;
        }

        /* ── Main Content ──────────────────────────── */
        #main-content {
            margin-left: var(--sidebar-width);
            padding-top: calc(var(--topbar-height) + 1.5rem);
            padding-bottom: 2rem;
            min-height: 100vh;
        }

        /* ── Cards ─────────────────────────────────── */
        .kpi-card {
            background: #fff; border-radius: 12px; padding: 1.25rem 1.5rem;
            border: 1px solid #e2e8f0; transition: transform .15s, box-shadow .15s;
        }
        .kpi-card:hover { transform: translateY(-2px); box-shadow: 0 4px 20px rgba(0,0,0,.07); }
        .kpi-card .kpi-icon {
            width: 48px; height: 48px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center; font-size: 1.4rem;
        }
        .kpi-card .kpi-value { font-size: 1.6rem; font-weight: 700; color: #0f172a; line-height: 1; }
        .kpi-card .kpi-label { font-size: .78rem; color: #64748b; margin-top: .2rem; }
        .kpi-card .kpi-change { font-size: .78rem; font-weight: 600; }
        .kpi-card .kpi-change.up   { color: #10b981; }
        .kpi-card .kpi-change.down { color: #ef4444; }

        /* Chart cards */
        .chart-card {
            background: #fff; border-radius: 12px; padding: 1.25rem 1.5rem;
            border: 1px solid #e2e8f0;
        }
        .chart-card .chart-title { font-size: .9rem; font-weight: 600; color: #0f172a; }

        /* Table cards */
        .table-card {
            background: #fff; border-radius: 12px; padding: 1.25rem 1.5rem;
            border: 1px solid #e2e8f0;
        }
        .table-card .table-card-title { font-size: .9rem; font-weight: 600; color: #0f172a; }

        /* Status badges */
        .badge-paid     { background: #dcfce7; color: #166534; }
        .badge-unpaid   { background: #fef9c3; color: #854d0e; }
        .badge-partial  { background: #dbeafe; color: #1e40af; }
        .badge-overdue  { background: #fee2e2; color: #991b1b; }
        .badge-active   { background: #dcfce7; color: #166534; }
        .badge-cancelled{ background: #f1f5f9; color: #475569; }

        /* Alert strip */
        .alert-strip {
            border-left: 4px solid; border-radius: 8px;
            padding: .65rem 1rem; font-size: .85rem;
        }
        .alert-strip.warning { border-color: #f59e0b; background: #fffbeb; color: #92400e; }
        .alert-strip.danger  { border-color: #ef4444; background: #fef2f2; color: #991b1b; }
        .alert-strip.info    { border-color: #3b82f6; background: #eff6ff; color: #1e40af; }

        /* Responsive */
        @media (max-width: 991px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.show { transform: translateX(0); }
            #topbar { left: 0; }
            #main-content { margin-left: 0; }
        }

        /* Page header */
        .page-header { margin-bottom: 1.5rem; }
        .page-header h4 { font-weight: 700; color: #0f172a; margin: 0; }
        .page-header .breadcrumb { font-size: .8rem; margin: 0; }

        /* Tables */
        .table th { font-size: .78rem; font-weight: 600; color: #64748b;
            text-transform: uppercase; letter-spacing: .04em; border-top: none; }
        .table td { font-size: .875rem; vertical-align: middle; }

        /* Form controls */
        .form-label { font-size: .82rem; font-weight: 600; color: #374151; }
        .form-control, .form-select {
            border-radius: 8px; border-color: #d1d5db; font-size: .875rem;
        }
        .form-control:focus, .form-select:focus { border-color: var(--sidebar-accent); box-shadow: 0 0 0 3px rgba(59,130,246,.15); }

        /* Buttons */
        .btn { border-radius: 8px; font-size: .875rem; font-weight: 500; }
        .btn-primary { background: var(--sidebar-accent); border-color: var(--sidebar-accent); }
        .btn-primary:hover { background: #2563eb; border-color: #2563eb; }

        /* Number formatting */
        .num { font-variant-numeric: tabular-nums; font-weight: 500; }
        .num-green { color: #10b981; }
        .num-red   { color: #ef4444; }
    </style>
    @stack('styles')
</head>
<body>

<!-- ── Sidebar ─────────────────────────────────────────────────────────────── -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-bar-chart-line-fill"></i></div>
        <div>
            <div class="brand-text">EXU Finance</div>
            <div class="brand-sub">Exponential University</div>
        </div>
    </div>

    <ul class="sidebar-nav mt-2">
        <li class="sidebar-section">Overview</li>
        <li class="nav-item">
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        <li class="sidebar-section">Transactions</li>
        <li class="nav-item">
            <a href="{{ route('revenue.index') }}"
               class="nav-link {{ request()->routeIs('revenue*') ? 'active' : '' }}">
                <i class="bi bi-graph-up-arrow"></i> Revenue
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('expenses.index') }}"
               class="nav-link {{ request()->routeIs('expenses*') ? 'active' : '' }}">
                <i class="bi bi-receipt-cutoff"></i> Expenses
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('bank-transactions.index') }}"
               class="nav-link {{ request()->routeIs('bank-transactions*') ? 'active' : '' }}">
                <i class="bi bi-arrow-left-right"></i> Bank Transactions
            </a>
        </li>

        <li class="sidebar-section">Banking</li>
        <li class="nav-item">
            <a href="{{ route('bank-accounts.index') }}"
               class="nav-link {{ request()->routeIs('bank-accounts*') ? 'active' : '' }}">
                <i class="bi bi-bank"></i> Bank Accounts
            </a>
        </li>

        <li class="sidebar-section">Receivables & Payables</li>
        <li class="nav-item">
            <a href="{{ route('invoices.index') }}"
               class="nav-link {{ request()->routeIs('invoices*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> Accounts Receivable
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('bills.index') }}"
               class="nav-link {{ request()->routeIs('bills*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-minus"></i> Accounts Payable
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('staff-loans.index') }}"
               class="nav-link {{ request()->routeIs('staff-loans*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i> Staff Loans
            </a>
        </li>

        <li class="sidebar-section">Reports</li>
        <li class="nav-item">
            <a href="{{ route('financial-statements.income-statement') }}"
               class="nav-link {{ request()->routeIs('financial-statements.income*') ? 'active' : '' }}">
                <i class="bi bi-journal-richtext"></i> Income Statement
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('financial-statements.balance-sheet') }}"
               class="nav-link {{ request()->routeIs('financial-statements.balance*') ? 'active' : '' }}">
                <i class="bi bi-clipboard-data"></i> Balance Sheet
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('financial-statements.cash-flow') }}"
               class="nav-link {{ request()->routeIs('financial-statements.cash*') ? 'active' : '' }}">
                <i class="bi bi-water"></i> Cash Flow
            </a>
        </li>

        @role('admin')
        <li class="sidebar-section">Administration</li>
        <li class="nav-item">
            <a href="{{ route('settings.index') }}"
               class="nav-link {{ request()->routeIs('settings*') ? 'active' : '' }}">
                <i class="bi bi-gear"></i> Settings
            </a>
        </li>
        @endrole
    </ul>

    <div style="padding:1.5rem 1rem; border-top:1px solid #1e293b; margin-top:auto;">
        <div style="font-size:.78rem; color:#64748b;">Logged in as</div>
        <div style="font-size:.85rem; color:#f1f5f9; font-weight:600;">{{ auth()->user()->name }}</div>
        <div style="font-size:.72rem; color:#475569; text-transform:uppercase;">
            {{ auth()->user()->getRoleNames()->first() ?? 'User' }}
        </div>
    </div>
</nav>

<!-- ── Top Bar ─────────────────────────────────────────────────────────────── -->
<div id="topbar">
    <button class="btn btn-link text-secondary d-lg-none me-2 p-0" id="sidebarToggle">
        <i class="bi bi-list fs-4"></i>
    </button>
    <div class="me-auto">
        <span class="fw-600 text-dark" style="font-weight:600;">@yield('title', 'Dashboard')</span>
    </div>
    <div class="d-flex align-items-center gap-3">
        <span class="text-muted" style="font-size:.8rem;">
            <i class="bi bi-calendar3 me-1"></i>{{ now()->format('F d, Y') }}
        </span>
        <div class="dropdown">
            <button class="btn btn-link text-secondary p-0 dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-5"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><h6 class="dropdown-header">{{ auth()->user()->name }}</h6></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item text-danger" type="submit">
                            <i class="bi bi-box-arrow-right me-2"></i>Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- ── Main Content ────────────────────────────────────────────────────────── -->
<div id="main-content">
    <div class="container-fluid px-4">

        {{-- Alerts --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:10px;">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:10px;">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" style="border-radius:10px;">
            <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @yield('content')
    </div>
</div>

<!-- ── Scripts ─────────────────────────────────────────────────────────────── -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Sidebar toggle
    document.getElementById('sidebarToggle')?.addEventListener('click', function () {
        document.getElementById('sidebar').classList.toggle('show');
    });

    // Flatpickr date pickers
    document.querySelectorAll('.datepicker').forEach(el => {
        flatpickr(el, { dateFormat: 'Y-m-d', allowInput: true });
    });

    // DataTables init
    document.querySelectorAll('.datatable').forEach(el => {
        new DataTable(el, {
            pageLength: 25,
            responsive: true,
            language: { search: '', searchPlaceholder: 'Search...' },
        });
    });

    // Confirm delete
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const form = document.querySelector(this.dataset.form);
            Swal.fire({
                title: 'Are you sure?',
                text: this.dataset.confirm || 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, delete it',
            }).then(r => { if (r.isConfirmed && form) form.submit(); });
        });
    });

    // Format numbers
    function phpNum(n) {
        return '₱' + parseFloat(n).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
    }
</script>
@stack('scripts')
</body>
</html>
