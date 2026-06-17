@extends('layouts.app')
@section('title', 'Edit Student - StudentFlow')
@section('content')
    <h2 class="mb-4"><i class="bi bi-pencil"></i> Edit Student: {{ $student->full_name }}</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card stat-card">
        <div class="card-body">
            <form method="POST" action="/students/{{ $student->id }}">
                @csrf @method('PUT')
                @include('students._form')
            </form>
        </div>
    </div>
@endsection
