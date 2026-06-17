<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'StudentFlow')</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="apple-touch-icon" href="/images/studentflow-logo.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root { --sf-primary: #0d6efd; }
        body { background: #f5f7fb; }
        .navbar-brand { font-weight: 700; letter-spacing: 0.3px; }
        .stat-card { border: none; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
        .stat-card .stat-label { color: #6c757d; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card .stat-value { font-size: 2rem; font-weight: 700; }
        .login-card { max-width: 420px; margin: 4rem auto; }
        .sidebar { background: #fff; min-height: calc(100vh - 56px); border-right: 1px solid #e9ecef; padding-top: 1rem; }
        .sidebar .nav-link { color: #495057; padding: 0.5rem 1rem; border-radius: 0.375rem; margin: 0 0.5rem; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background: #f1f3f5; color: var(--sf-primary); }
        .content { padding: 1.5rem; }
        .brand-logo { width: 30px; height: 30px; object-fit: contain; border-radius: 7px; margin-right: 0.4rem; background: #fff; }
    </style>
    @stack('head')
</head>
<body>
    @auth
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <a class="navbar-brand" href="/dashboard">
                    <img src="/images/studentflow-logo.png" alt="" class="brand-logo"> StudentFlow
                </a>
                <div class="d-flex align-items-center text-white">
                    <span class="me-3">
                        <i class="bi bi-person-circle"></i>
                        {{ auth()->user()->name }}
                        <span class="badge bg-light text-primary ms-1">{{ ucfirst(auth()->user()->role) }}</span>
                    </span>
                    <form method="POST" action="/logout" class="d-inline">
                        @csrf
                        <button class="btn btn-sm btn-outline-light" type="submit">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </nav>
        <div class="container-fluid">
            <div class="row">
                <aside class="col-md-3 col-lg-2 sidebar">
                    <ul class="nav flex-column">
                        <li class="nav-item"><a class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}" href="/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->is('classes*') ? 'active' : '' }}" href="/classes"><i class="bi bi-collection"></i> Classes</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->is('students*') ? 'active' : '' }}" href="/students"><i class="bi bi-people"></i> Students</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->is('attendance*') ? 'active' : '' }}" href="/attendance"><i class="bi bi-check2-square"></i> Attendance</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->is('grades*') ? 'active' : '' }}" href="/grades"><i class="bi bi-clipboard-data"></i> Grades</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->is('assignments*') ? 'active' : '' }}" href="/assignments"><i class="bi bi-journal-text"></i> Assignments</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->is('announcements*') ? 'active' : '' }}" href="/announcements"><i class="bi bi-megaphone"></i> Announcements</a></li>
                        <li class="nav-item"><a class="nav-link {{ request()->is('reports*') ? 'active' : '' }}" href="/reports"><i class="bi bi-file-earmark-bar-graph"></i> Reports</a></li>
                        @if (auth()->user()->isAdmin())
                            <li class="nav-item mt-2"><small class="text-muted px-3">Administration</small></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/teachers*') ? 'active' : '' }}" href="/admin/teachers"><i class="bi bi-person-workspace"></i> Teachers</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/settings*') ? 'active' : '' }}" href="/admin/settings"><i class="bi bi-gear"></i> Settings</a></li>
                            <li class="nav-item"><a class="nav-link {{ request()->is('admin/activity-logs*') ? 'active' : '' }}" href="/admin/activity-logs"><i class="bi bi-clock-history"></i> Activity Logs</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link {{ request()->is('change-password') ? 'active' : '' }}" href="/change-password"><i class="bi bi-key"></i> Change Password</a></li>
                    </ul>
                </aside>
                <main class="col-md-9 col-lg-10 content">
                    @yield('content')
                </main>
            </div>
        </div>
    @else
        <main class="container">
            @yield('content')
        </main>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
