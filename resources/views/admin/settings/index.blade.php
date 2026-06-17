@extends('layouts.app')
@section('title', 'School Settings - StudentFlow')
@section('content')
    <h2 class="mb-3"><i class="bi bi-gear"></i> School Settings</h2>
    @php
        $defaults = [
            'school_name' => 'StudentFlow Demo School',
            'school_year' => '2026-2027',
            'semester' => 'First Semester',
            'principal_name' => '',
            'contact_email' => 'admin@studentflow.local',
        ];
        $byKey = $settings->keyBy('setting_key');
    @endphp
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card stat-card"><div class="card-body">
                <form method="POST" action="/admin/settings">
                    @csrf @method('PUT')
                    @foreach ($defaults as $key => $fallback)
                        <div class="mb-3">
                            <label class="form-label">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                            <input name="settings[{{ $key }}]" class="form-control" value="{{ old("settings.$key", $byKey[$key]->setting_value ?? $fallback) }}">
                        </div>
                    @endforeach
                    <button class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Settings</button>
                </form>
            </div></div>
        </div>
        <div class="col-lg-6">
            <div class="card stat-card">
                <div class="card-header bg-white"><h5 class="mb-0">Recent Setting Changes</h5></div>
                <ul class="list-group list-group-flush">
                    @forelse ($settings->flatMap->histories->sortByDesc('created_at')->take(15) as $history)
                        <li class="list-group-item">
                            <strong>{{ $history->setting->label ?? $history->setting->setting_key }}</strong>
                            <small class="text-muted d-block">{{ $history->user->username ?? 'system' }} - {{ $history->created_at->format('Y-m-d H:i') }}</small>
                            <small>{{ $history->old_value ?? 'blank' }} -> {{ $history->new_value ?? 'blank' }}</small>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">No setting changes yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
