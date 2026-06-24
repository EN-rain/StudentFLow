@extends('layouts.app')
@section('title', 'Add Teacher - StudentFlow')
@section('content')
    <h2 class="mb-3"><i class="bi bi-plus-lg"></i> Add Teacher</h2>
    <div class="card stat-card"><div class="card-body">
        <form method="POST" action="/admin/teachers">
            @csrf
            @include('admin.teachers._form')
        </form>
    </div></div>
@endsection
