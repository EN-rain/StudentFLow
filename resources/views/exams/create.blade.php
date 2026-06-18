@extends('layouts.app')

@section('title', 'Create Exam')

@section('content')
<div class="page-header mb-4">
    <div>
        <h1 class="h3 mb-1">Create Exam</h1>
        <p class="text-muted mb-0">Add the exam details and questions.</p>
    </div>
    <a href="/exams" class="btn btn-outline-secondary">Back</a>
</div>

@if ($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="/exams" class="card border-0 shadow-sm p-4">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Class</label>
            <select name="class_id" class="form-select" required>
                <option value="">Select class</option>
                @foreach ($classes as $class)
                    <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>{{ $class->class_name }} — {{ $class->subject }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Grade item ID <span class="text-muted">(optional)</span></label>
            <input type="number" name="grade_item_id" class="form-control" value="{{ old('grade_item_id') }}">
            <div class="form-text">Use an existing grade item from the selected class to sync scores.</div>
        </div>
        <div class="col-md-8">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Maximum score</label>
            <input type="number" step="0.01" min="1" name="maximum_score" class="form-control" value="{{ old('maximum_score', 100) }}" required>
        </div>
        <div class="col-12">
            <label class="form-label">Instructions</label>
            <textarea name="instructions" class="form-control" rows="3">{{ old('instructions') }}</textarea>
        </div>
        <div class="col-md-4">
            <label class="form-label">Available from</label>
            <input type="datetime-local" name="available_from" class="form-control" value="{{ old('available_from') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label">Due at</label>
            <input type="datetime-local" name="due_at" class="form-control" value="{{ old('due_at') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Duration</label>
            <input type="number" min="1" max="600" name="duration_minutes" class="form-control" value="{{ old('duration_minutes', 60) }}">
        </div>
        <div class="col-md-2">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
                <option value="draft" @selected(old('status') === 'draft')>Draft</option>
                <option value="published" @selected(old('status', 'published') === 'published')>Published</option>
            </select>
        </div>
    </div>

    <hr class="my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0">Questions</h2>
        <button type="button" id="add-question" class="btn btn-sm btn-outline-primary"><i class="bi bi-plus-lg"></i> Add Question</button>
    </div>

    <div id="questions"></div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="/exams" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary">Create Exam</button>
    </div>
</form>
@endsection

@push('scripts')
<script>
    let questionIndex = 0;

    function addQuestion(values = {}) {
        const index = questionIndex++;
        const wrapper = document.createElement('div');
        wrapper.className = 'card border mb-3 p-3 question-card';
        wrapper.innerHTML = `
            <div class="d-flex justify-content-between align-items-center mb-3">
                <strong>Question ${index + 1}</strong>
                <button type="button" class="btn btn-sm btn-outline-danger remove-question">Remove</button>
            </div>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Prompt</label>
                    <textarea name="questions[${index}][prompt]" class="form-control" rows="2" required>${values.prompt || ''}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Type</label>
                    <select name="questions[${index}][type]" class="form-select question-type" required>
                        <option value="multiple_choice">Multiple choice</option>
                        <option value="text">Text answer</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Points</label>
                    <input type="number" step="0.01" min="0.01" name="questions[${index}][points]" class="form-control" value="${values.points || 1}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Correct answer</label>
                    <input type="text" name="questions[${index}][correct_answer]" class="form-control" value="${values.correct_answer || ''}">
                </div>
                <div class="col-12 choices-wrap">
                    <label class="form-label">Choices <span class="text-muted">(one per line)</span></label>
                    <textarea name="questions[${index}][choices_text]" class="form-control" rows="4">${values.choices_text || ''}</textarea>
                </div>
            </div>`;

        const type = wrapper.querySelector('.question-type');
        const choices = wrapper.querySelector('.choices-wrap');
        const toggleChoices = () => choices.style.display = type.value === 'multiple_choice' ? '' : 'none';
        type.addEventListener('change', toggleChoices);
        wrapper.querySelector('.remove-question').addEventListener('click', () => {
            if (document.querySelectorAll('.question-card').length > 1) wrapper.remove();
        });
        document.getElementById('questions').appendChild(wrapper);
        toggleChoices();
    }

    document.getElementById('add-question').addEventListener('click', () => addQuestion());
    addQuestion();
</script>
@endpush
