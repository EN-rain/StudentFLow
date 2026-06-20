<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'StudentFlow')</title>
    <link rel="icon" type="image/png" href="/images/studentflow-logo-96.png">
    <link rel="apple-touch-icon" href="/images/studentflow-logo-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sf-primary: #0f172a;
            --sf-primary-dark: #020617;
            --sf-accent: #0f766e;
            --sf-accent-soft: #ccfbf1;
            --sf-surface: #f8fafc;
            --sf-surface-alt: #eff6ff;
            --sf-panel: #ffffff;
            --sf-border: #dbe4ee;
            --sf-border-strong: #cbd5e1;
            --sf-text: #0f172a;
            --sf-text-muted: #64748b;
            --sf-shadow: 0 24px 60px rgba(15, 23, 42, 0.14);
            --sf-shadow-soft: 0 12px 30px rgba(15, 23, 42, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            overflow-y: auto;
            color: var(--sf-text);
            background:
                linear-gradient(180deg, rgba(2, 6, 23, 0.86) 0, rgba(15, 23, 42, 0.72) 280px, rgba(248, 250, 252, 0.94) 280px, rgba(248, 250, 252, 0.98) 100%),
                url('/images/studentflow-background.jpg') center top / cover fixed,
                radial-gradient(circle at top left, rgba(15, 118, 110, 0.26), transparent 28%),
                linear-gradient(180deg, var(--sf-primary-dark) 0, var(--sf-primary) 280px, var(--sf-surface) 280px, var(--sf-surface) 100%);
        }

        a {
            color: var(--sf-accent);
            text-decoration: none;
        }

        a:hover {
            color: #115e59;
        }

        .topbar {
            padding: 1.1rem 1.25rem 0;
            background: transparent;
        }

        .topbar .container-fluid {
            min-height: 72px;
            padding: 0.85rem 1rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            background: rgba(15, 23, 42, 0.72);
            backdrop-filter: blur(16px);
            box-shadow: 0 20px 45px rgba(2, 6, 23, 0.28);
        }

        .navbar-brand {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0;
            color: #fff;
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            padding: 0.3rem;
            object-fit: contain;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.18);
        }

        .guest-shell {
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(1rem, 4vw, 2rem);
            overflow-y: auto;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #fff;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .topbar-user-name {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            font-weight: 600;
        }

        .role-pill {
            display: inline-flex;
            align-items: center;
            min-height: 30px;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: #ecfeff;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .app-shell {
            padding: 1rem 1.25rem 1.5rem;
        }

        .app-shell > .row {
            --bs-gutter-x: 1.25rem;
            --bs-gutter-y: 1.25rem;
            align-items: flex-start;
        }

        .sidebar {
            padding: 1rem;
        }

        .sidebar-panel {
            position: sticky;
            top: 1rem;
            padding: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 28px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(248, 250, 252, 0.96));
            box-shadow: var(--sf-shadow);
        }

        .sidebar-title {
            margin: 0.85rem 0 0.5rem;
            padding: 0 0.75rem;
            color: var(--sf-text-muted);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .sidebar .nav {
            gap: 0.25rem;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-height: 46px;
            padding: 0.75rem 0.9rem;
            border-radius: 18px;
            color: var(--sf-text);
            font-weight: 600;
            transition: background-color 0.18s ease, color 0.18s ease, transform 0.18s ease;
        }

        .sidebar .nav-link i {
            width: 1.15rem;
            color: var(--sf-accent);
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: linear-gradient(135deg, var(--sf-surface-alt), #e2e8f0);
            color: var(--sf-primary);
            transform: translateX(1px);
        }

        .sidebar .nav-link.active {
            box-shadow: inset 0 0 0 1px rgba(15, 118, 110, 0.12);
        }

        .content {
            padding: 0;
        }

        .content-panel {
            min-height: calc(100vh - 136px);
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 32px;
            background:
                linear-gradient(180deg, rgba(255, 255, 255, 0.99), rgba(248, 250, 252, 0.97));
            box-shadow: var(--sf-shadow);
        }

        .page-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .page-header h2,
        .page-header h1 {
            margin: 0;
            font-size: clamp(1.5rem, 2vw, 2rem);
            font-weight: 800;
            letter-spacing: 0;
        }

        .page-header p,
        .page-header .text-muted {
            margin: 0.4rem 0 0;
            color: var(--sf-text-muted) !important;
            font-size: 0.96rem;
        }

        .surface-card,
        .stat-card {
            border: 1px solid var(--sf-border);
            border-radius: 24px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: var(--sf-shadow-soft);
            overflow: hidden;
        }

        .stat-card .card-body {
            padding: 1.2rem 1.25rem;
        }

        .stat-card .stat-label {
            color: var(--sf-text-muted);
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .stat-card .stat-value {
            margin-top: 0.4rem;
            font-size: clamp(1.9rem, 2vw, 2.35rem);
            line-height: 1;
            font-weight: 800;
            color: var(--sf-primary);
        }

        .card-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--sf-border);
            background: transparent !important;
        }

        .card-header h5,
        .card-header h6 {
            margin: 0;
            font-weight: 700;
            color: var(--sf-primary);
        }

        .card-body {
            padding: 1.2rem 1.25rem;
        }

        .table {
            --bs-table-bg: transparent;
            --bs-table-border-color: var(--sf-border);
            color: var(--sf-text);
            margin-bottom: 0;
        }

        .table thead th {
            padding: 1rem 1.1rem;
            border-bottom-width: 1px;
            color: var(--sf-text-muted);
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            background: rgba(239, 246, 255, 0.72) !important;
        }

        .table tbody td {
            padding: 1rem 1.1rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            --bs-table-accent-bg: rgba(239, 246, 255, 0.68);
            color: inherit;
        }

        .list-group-item {
            padding: 1rem 0;
            border-color: var(--sf-border);
            background: transparent;
        }

        .btn {
            border-radius: 16px;
            font-weight: 700;
            padding: 0.7rem 1rem;
            box-shadow: none !important;
        }

        .btn-sm {
            min-height: 36px;
            padding: 0.48rem 0.72rem;
            border-radius: 12px;
        }

        .btn-primary {
            border-color: var(--sf-accent);
            background: var(--sf-accent);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            border-color: #115e59;
            background: #115e59;
        }

        .btn-outline-primary {
            border-color: rgba(15, 118, 110, 0.34);
            color: var(--sf-accent);
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            border-color: var(--sf-accent);
            background: var(--sf-accent-soft);
            color: #115e59;
        }

        .btn-outline-secondary,
        .btn-outline-light,
        .btn-outline-warning {
            border-color: var(--sf-border-strong);
            color: var(--sf-text);
        }

        .btn-outline-secondary:hover,
        .btn-outline-secondary:focus,
        .btn-outline-light:hover,
        .btn-outline-light:focus,
        .btn-outline-warning:hover,
        .btn-outline-warning:focus {
            background: var(--sf-surface-alt);
            color: var(--sf-primary);
            border-color: var(--sf-border-strong);
        }

        .btn-topbar {
            border-color: rgba(255, 255, 255, 0.22);
            color: #fff;
            background: transparent;
        }

        .btn-topbar:hover,
        .btn-topbar:focus {
            border-color: rgba(255, 255, 255, 0.32);
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        .form-control,
        .form-select {
            min-height: 48px;
            border: 1px solid var(--sf-border-strong);
            border-radius: 16px;
            color: var(--sf-text);
            background: rgba(255, 255, 255, 0.96);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: rgba(15, 118, 110, 0.5);
            box-shadow: 0 0 0 0.2rem rgba(15, 118, 110, 0.12);
        }

        .badge {
            padding: 0.5rem 0.7rem;
            border-radius: 999px;
            font-weight: 700;
            letter-spacing: 0;
        }

        .alert {
            border: 1px solid var(--sf-border);
            border-radius: 20px;
            box-shadow: var(--sf-shadow-soft);
        }

        code {
            padding: 0.2rem 0.45rem;
            border-radius: 10px;
            color: var(--sf-primary);
            background: var(--sf-surface-alt);
        }

        .login-card {
            width: min(100%, 440px);
            max-width: 440px;
            margin: auto;
            border: 1px solid var(--sf-border);
            border-radius: 28px;
            background: rgba(255, 255, 255, 0.96);
            box-shadow: var(--sf-shadow);
        }

        .login-card .card {
            border-radius: 28px;
        }

        .auth-logo {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        @media (max-width: 991.98px) {
            .app-shell {
                padding-top: 0.75rem;
            }

            .sidebar-panel,
            .content-panel {
                position: static;
                min-height: auto;
            }
        }

        @keyframes sf-page-enter {
            from { opacity: 0; transform: translateY(12px) scale(.995); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        @keyframes sf-item-enter {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        body {
            transition: opacity 160ms ease;
        }

        body.sf-page-leaving {
            opacity: 0;
        }

        .content-panel,
        body > main.container {
            animation: sf-page-enter 320ms cubic-bezier(.2,.8,.2,1) both;
        }

        .card,
        .alert,
        .table,
        .page-header,
        form,
        .list-group-item {
            animation: sf-item-enter 300ms cubic-bezier(.2,.8,.2,1) both;
        }

        .card,
        .btn,
        .nav-link,
        .list-group-item,
        .table tbody tr,
        input,
        select,
        textarea,
        .badge,
        .dropdown-menu,
        .modal-content {
            transition: transform 180ms ease, box-shadow 180ms ease, background-color 180ms ease,
                border-color 180ms ease, color 180ms ease, opacity 180ms ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(15, 23, 42, .10);
        }

        .btn:hover:not(:disabled),
        .nav-link:hover {
            transform: translateY(-1px);
        }

        .btn:active:not(:disabled),
        .nav-link:active {
            transform: scale(.97);
        }

        .table tbody tr:hover {
            transform: translateX(2px);
        }

        input:focus,
        select:focus,
        textarea:focus {
            transform: translateY(-1px);
        }

        .modal.fade .modal-dialog {
            transform: translateY(16px) scale(.98);
            transition: transform 220ms cubic-bezier(.2,.8,.2,1), opacity 220ms ease;
        }

        .modal.show .modal-dialog {
            transform: translateY(0) scale(1);
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
            }
        }

        @media (max-width: 767.98px) {
            .topbar {
                padding: 0.9rem 0.9rem 0;
            }

            .app-shell {
                padding: 0.9rem;
            }

            .guest-shell {
                align-items: flex-start;
                padding: 1rem;
            }

            .content-panel {
                padding: 1rem;
                border-radius: 24px;
            }

            .sidebar-panel {
                border-radius: 24px;
            }

            .page-header {
                flex-direction: column;
                align-items: stretch;
            }
        }
    </style>
    @stack('head')
</head>
<body>
    @auth
        <nav class="navbar navbar-expand-lg navbar-dark topbar">
            <div class="container-fluid">
                <a class="navbar-brand" href="/dashboard">
                    <img src="/images/studentflow-logo-96.png" alt="" class="brand-logo" width="44" height="44" decoding="async">
                    <span>StudentFlow</span>
                </a>
                <div class="topbar-user">
                    <span class="topbar-user-name">
                        <i class="bi bi-person-circle"></i>
                        {{ auth()->user()->name }}
                        <span class="role-pill">{{ ucfirst(auth()->user()->role) }}</span>
                    </span>
                    <form method="POST" action="/logout" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-topbar" type="submit">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </nav>
        <div class="container-fluid app-shell">
            <div class="row">
                <aside class="col-lg-3 col-xl-2 sidebar">
                    <div class="sidebar-panel">
                        <ul class="nav flex-column">
                            <li class="nav-item"><a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('classes*') ? 'active' : '' }}" href="/classes"><i class="bi bi-collection"></i> Classes</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('students*') ? 'active' : '' }}" href="/students"><i class="bi bi-people"></i> Students</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('attendance*') ? 'active' : '' }}" href="/attendance"><i class="bi bi-check2-square"></i> Attendance</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('grades*') ? 'active' : '' }}" href="/grades"><i class="bi bi-clipboard-data"></i> Grades</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('assignments*') ? 'active' : '' }}" href="/assignments"><i class="bi bi-journal-text"></i> Assignments</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('exams*') ? 'active' : '' }}" href="/exams"><i class="bi bi-ui-checks-grid"></i> Exams</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('announcements*') ? 'active' : '' }}" href="/announcements"><i class="bi bi-megaphone"></i> Announcements</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('reports*') ? 'active' : '' }}" href="/reports"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a></li>
                            @if (auth()->user()->isAdmin())
                                <li class="nav-item">
                                    <div class="sidebar-title">Administration</div>
                                </li>
                                <li class="nav-item"><a class="nav-link {{ request()->is('admin/teachers*') ? 'active' : '' }}" href="/admin/teachers"><i class="bi bi-person-workspace"></i> Teachers</a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}" href="/admin/settings"><i class="bi bi-gear"></i> Settings</a></li>
                                <li class="nav-item"><a class="nav-link {{ request()->is('admin/activity-logs*') ? 'active' : '' }}" href="/admin/activity-logs"><i class="bi bi-clock-history"></i> Activity Logs</a></li>
                            @endif
                            <li class="nav-item"><a class="nav-link {{ request()->is('change-password') ? 'active' : '' }}" href="/change-password"><i class="bi bi-key"></i> Change Password</a></li>
                        </ul>
                    </div>
                </aside>
                <main class="col-lg-9 col-xl-10 content">
                    <div class="content-panel">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    @else
        <main class="guest-shell">
            @yield('content')
        </main>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('click', function (event) {
            const link = event.target.closest('a[href]');
            if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin || link.target === '_blank' || link.hasAttribute('download') || url.hash) {
                return;
            }

            event.preventDefault();
            document.body.classList.add('sf-page-leaving');
            window.setTimeout(() => window.location.assign(url.href), 150);
        });

        window.addEventListener('pageshow', function () {
            document.body.classList.remove('sf-page-leaving');
        });
    </script>
    @stack('scripts')
</body>
</html>
