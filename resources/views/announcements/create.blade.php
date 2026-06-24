@extends('layouts.app')
@section('title', 'New Announcement - StudentFlow')
@section('content')
    <h2 class="mb-4"><i class="bi bi-megaphone"></i> New Announcement</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card stat-card">
        <div class="card-body">
            <form method="POST" action="/announcements">
                @csrf
                @include('announcements._form')
            </form>
        </div>
    </div>
@endsection
