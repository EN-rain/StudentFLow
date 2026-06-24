@extends('layouts.app')
@section('title', 'Edit Teacher - StudentFlow')
@section('content')
    <h2 class="mb-3"><i class="bi bi-pencil"></i> Edit Teacher</h2>
    <div class="card stat-card"><div class="card-body">
        <form method="POST" action="/admin/teachers/{{ $teacher->id }}">
            @csrf
            @method('PUT')
            @include('admin.teachers._form')
        </form>
    </div></div>
@endsection
