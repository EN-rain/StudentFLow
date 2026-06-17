@php
    $class = $announcement->schoolClass;
    $teacher = $announcement->teacher?->full_name ?? $announcement->teacher?->user?->name ?? 'Your teacher';
@endphp

<p>Hello {{ $student->first_name }},</p>

<p><strong>{{ $announcement->title }}</strong></p>

<p>{{ $announcement->message }}</p>

<p>
    <strong>Class:</strong> {{ $class?->class_name ?? 'Class announcement' }}<br>
    <strong>Subject:</strong> {{ $class?->subject ?? '-' }}<br>
    <strong>Priority:</strong> {{ $announcement->priority ?? 'Normal' }}<br>
    <strong>Published:</strong> {{ $announcement->publish_date?->format('Y-m-d') ?? '-' }}<br>
    <strong>Teacher:</strong> {{ $teacher }}
</p>

@if ($announcement->expiration_date)
    <p>This announcement is active until {{ $announcement->expiration_date->format('Y-m-d') }}.</p>
@endif

<p>StudentFlow</p>
