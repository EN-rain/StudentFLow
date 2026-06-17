@extends('layouts.app')
@section('title', 'Activity Logs - StudentFlow')
@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="mb-0"><i class="bi bi-clock-history"></i> Activity Logs</h2>
        <a class="btn btn-outline-secondary" href="/admin/activity-logs/csv?{{ http_build_query(request()->query()) }}"><i class="bi bi-filetype-csv"></i> CSV</a>
    </div>
    <form class="row g-2 mb-3">
        <div class="col-md-4"><input name="q" class="form-control" value="{{ request('q') }}" placeholder="Action, user, entity, IP"></div>
        <div class="col-md-2"><input type="date" name="from" class="form-control" value="{{ request('from') }}"></div>
        <div class="col-md-2"><input type="date" name="to" class="form-control" value="{{ request('to') }}"></div>
        <div class="col-auto"><button class="btn btn-outline-secondary"><i class="bi bi-search"></i></button></div>
    </form>
    <div class="card stat-card"><div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="table-light"><tr><th>Date</th><th>User</th><th>Action</th><th>Entity</th><th>IP</th><th>Metadata</th></tr></thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('Y-m-d H:i') }}</td>
                        <td>{{ $log->user->username ?? 'system' }}</td>
                        <td><code>{{ $log->action }}</code></td>
                        <td>{{ class_basename($log->entity_type) }} #{{ $log->entity_id }}</td>
                        <td>{{ $log->ip_address }}</td>
                        <td><small>{{ json_encode($log->metadata) }}</small></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No activity logs.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div></div>
@endsection
