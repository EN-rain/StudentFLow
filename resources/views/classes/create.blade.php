@extends('layouts.app')
@section('title', 'New Class - StudentFlow')
@section('content')
    <h2 class="mb-4"><i class="bi bi-plus-circle"></i> New Class</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="card stat-card">
        <div class="card-body">
            <form method="POST" action="/classes">
                @csrf
                @include('classes._form')
            </form>
        </div>
    </div>
@endsection
