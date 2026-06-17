@extends('layouts.app')
@section('title', 'Grades — ' . $class->class_name)
@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h2 class="mb-1"><i class="bi bi-clipboard-data"></i> Grade Entry: {{ $class->class_name }}</h2>
            <p class="text-muted mb-0">{{ $class->subject }} · {{ $class->schedule ?? '—' }}</p>
        </div>
        <a href="/grades" class="btn btn-outline-secondary">Back</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="row g-3 mb-3">
        <div class="col-lg-5">
            <div class="card stat-card">
                <div class="card-header bg-white"><h5 class="mb-0">Add Grade Category</h5></div>
                <div class="card-body">
                    <form method="POST" action="/grades/{{ $class->id }}/categories" class="row g-2">
                        @csrf
                        <div class="col-md-7"><input name="category_name" class="form-control" placeholder="Category name" required></div>
                        <div class="col-md-3"><input type="number" step="0.01" name="percentage_weight" class="form-control" placeholder="%" required></div>
                        <div class="col-md-2"><button class="btn btn-primary w-100"><i class="bi bi-plus"></i></button></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card stat-card">
                <div class="card-header bg-white"><h5 class="mb-0">Add Grade Item</h5></div>
                <div class="card-body">
                    <form method="POST" action="/grades/{{ $class->id }}/items" class="row g-2">
                        @csrf
                        <div class="col-md-3">
                            <select name="category_id" class="form-select" required>
                                @foreach ($class->gradeCategories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->category_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4"><input name="title" class="form-control" placeholder="Item title" required></div>
                        <div class="col-md-2"><input type="number" step="0.01" name="maximum_score" class="form-control" placeholder="Max" required></div>
                        <div class="col-md-2"><input type="date" name="date_given" class="form-control"></div>
                        <div class="col-md-1"><button class="btn btn-primary w-100"><i class="bi bi-plus"></i></button></div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card stat-card mb-3">
        <div class="card-header bg-white"><h5 class="mb-0">Grade Structure</h5></div>
        <div class="card-body">
            @foreach ($class->gradeCategories as $cat)
                <div class="border rounded p-3 mb-3">
                    <form method="POST" action="/grades/{{ $class->id }}/categories/{{ $cat->id }}" class="row g-2 align-items-end mb-2">
                        @csrf @method('PUT')
                        <div class="col-md-5"><label class="form-label small">Category</label><input name="category_name" class="form-control form-control-sm" value="{{ $cat->category_name }}" required></div>
                        <div class="col-md-3"><label class="form-label small">Weight %</label><input type="number" step="0.01" name="percentage_weight" class="form-control form-control-sm" value="{{ $cat->percentage_weight }}" required></div>
                        <div class="col-md-2"><button class="btn btn-sm btn-outline-primary w-100">Update</button></div>
                    </form>
                    <form method="POST" action="/grades/{{ $class->id }}/categories/{{ $cat->id }}" class="mb-2">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category and its items?')"><i class="bi bi-trash"></i> Delete Category</button>
                    </form>
                    @foreach ($cat->items as $item)
                        <div class="border-top pt-2 mt-2">
                            <form method="POST" action="/grades/{{ $class->id }}/items/{{ $item->id }}" class="row g-2 align-items-end">
                                @csrf @method('PUT')
                                <div class="col-md-3"><label class="form-label small">Item</label><input name="title" class="form-control form-control-sm" value="{{ $item->title }}" required></div>
                                <div class="col-md-3"><label class="form-label small">Category</label><select name="category_id" class="form-select form-select-sm">@foreach ($class->gradeCategories as $option)<option value="{{ $option->id }}" @selected($option->id === $item->category_id)>{{ $option->category_name }}</option>@endforeach</select></div>
                                <div class="col-md-2"><label class="form-label small">Max</label><input type="number" step="0.01" name="maximum_score" class="form-control form-control-sm" value="{{ $item->maximum_score }}" required></div>
                                <div class="col-md-2"><label class="form-label small">Date</label><input type="date" name="date_given" class="form-control form-control-sm" value="{{ $item->date_given?->format('Y-m-d') }}"></div>
                                <div class="col-md-1"><button class="btn btn-sm btn-outline-primary w-100">Save</button></div>
                            </form>
                            <form method="POST" action="/grades/{{ $class->id }}/items/{{ $item->id }}" class="mt-1">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this grade item?')"><i class="bi bi-trash"></i> Delete Item</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    <form method="POST" action="/grades/{{ $class->id }}">
        @csrf
        <div class="accordion" id="gradeAccordion">
            @foreach ($class->gradeCategories as $idx => $cat)
                <div class="accordion-item">
                    <h2 class="accordion-header" id="heading{{ $idx }}">
                        <button class="accordion-button {{ $idx === 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $idx }}" aria-expanded="{{ $idx === 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $idx }}">
                            <span class="me-3 fw-bold">{{ $cat->category_name }}</span>
                            <span class="badge bg-primary">{{ $cat->percentage_weight }}%</span>
                            <span class="ms-3 text-muted">{{ $cat->items->count() }} item(s)</span>
                        </button>
                    </h2>
                    <div id="collapse{{ $idx }}" class="accordion-collapse collapse {{ $idx === 0 ? 'show' : '' }}" aria-labelledby="heading{{ $idx }}" data-bs-parent="#gradeAccordion">
                        <div class="accordion-body p-0">
                            @foreach ($cat->items as $item)
                                <table class="table table-bordered mb-0 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 25%">{{ $item->title }}</th>
                                            <th style="width: 10%" class="text-center">Max: {{ $item->maximum_score }}</th>
                                            @foreach ($class->students as $s)
                                                <th class="text-center" style="width: {{ 65 / max(count($class->students), 1) }}%">
                                                    <small>{{ $s->last_name }}</small>
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><small class="text-muted">{{ $item->date_given ? $item->date_given->format('M d') : '' }}</small></td>
                                            <td></td>
                                            @foreach ($class->students as $s)
                                                @php $sg = $item->studentGrades->firstWhere('student_id', $s->id); @endphp
                                                <td>
                                                    <input type="number" step="0.01" min="0" max="{{ $item->maximum_score }}"
                                                        name="scores[{{ $item->id }}][{{ $s->id }}]"
                                                        class="form-control form-control-sm text-center"
                                                        value="{{ $sg->score ?? '' }}">
                                                </td>
                                            @endforeach
                                        </tr>
                                    </tbody>
                                </table>
                            @endforeach
                            @if ($cat->items->isEmpty())
                                <div class="p-3 text-muted">No grade items in this category yet.</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-3 d-flex justify-content-between align-items-center">
            <a href="/grades" class="btn btn-outline-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save All Grades</button>
        </div>
    </form>

    <div class="card stat-card mt-4">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="bi bi-bar-chart"></i> Computed Final Grades</h5></div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Student Number</th>
                        <th class="text-center">Final Grade</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($class->students as $i => $s)
                        @php
                            $g = $finals[$s->id] ?? null;
                            $status = $g === null ? 'No data' : ($g >= 75 ? 'Pass' : 'Fail');
                            $color = $g === null ? 'secondary' : ($g >= 75 ? 'success' : 'danger');
                        @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td><a href="/students/{{ $s->id }}">{{ $s->full_name }}</a></td>
                            <td>{{ $s->student_number }}</td>
                            <td class="text-center"><strong class="text-{{ $color }}">{{ $g ?? '—' }}</strong></td>
                            <td class="text-center"><span class="badge bg-{{ $color }}">{{ $status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No students enrolled.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
