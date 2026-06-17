<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $attempt->exam->title }} - StudentFlow Exam</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<main class="container py-4" style="max-width: 820px">
    <div class="card shadow-sm">
        <div class="card-body">
            <h1 class="h3 mb-1">{{ $attempt->exam->title }}</h1>
            <p class="text-muted mb-3">
                {{ $attempt->exam->schoolClass->class_name }} - {{ $attempt->student->full_name }}
            </p>

            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif

            @if ($attempt->status === 'submitted')
                <div class="alert alert-info">
                    Submitted on {{ $attempt->submitted_at?->format('Y-m-d H:i') }}.
                    Score: <strong>{{ $attempt->score }}</strong> / {{ $attempt->exam->maximum_score }}
                </div>
            @else
                <p>{{ $attempt->exam->instructions }}</p>
                <form method="POST" action="/exam/magic/{{ $attempt->magic_token }}">
                    @csrf
                    @foreach ($attempt->exam->questions as $question)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ $loop->iteration }}. {{ $question->prompt }}</label>
                            <input class="form-control" name="answers[{{ $question->id }}]" required>
                            <div class="form-text">{{ $question->points }} point(s)</div>
                        </div>
                    @endforeach
                    <button class="btn btn-primary">Submit Exam</button>
                </form>
            @endif
        </div>
    </div>
</main>
</body>
</html>
