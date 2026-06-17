<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAssignmentRequest;
use App\Models\Assignment;
use App\Models\SchoolClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Assignment::with('schoolClass');

        if ($classId = $request->query('class_id')) {
            $query->where('class_id', $classId);
        }

        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if (! $teacher) return response()->json(['data' => []]);
            $classIds = SchoolClass::where('teacher_id', $teacher->id)->pluck('id');
            $query->whereIn('class_id', $classIds);
        }

        return response()->json(['data' => $query->orderBy('deadline')->get()]);
    }

    public function show(Request $request, Assignment $assignment): JsonResponse
    {
        $this->authorizeAccess($request, $assignment);
        return response()->json(['data' => $assignment->load('schoolClass')]);
    }

    public function store(StoreAssignmentRequest $request): JsonResponse
    {
        $this->authorizeClassId($request, $request->class_id);
        $assignment = Assignment::create($request->validated());
        return response()->json(['data' => $assignment], 201);
    }

    public function update(StoreAssignmentRequest $request, Assignment $assignment): JsonResponse
    {
        $this->authorizeAccess($request, $assignment);
        $assignment->update($request->validated());
        return response()->json(['data' => $assignment]);
    }

    public function destroy(Request $request, Assignment $assignment): JsonResponse
    {
        $this->authorizeAccess($request, $assignment);
        $assignment->delete();
        return response()->json(['message' => 'Assignment deleted.']);
    }

    /**
     * Auto-derive status from deadline + completion for a single assignment.
     */
    public function refreshStatus(Assignment $assignment): string
    {
        if ($assignment->status === 'Cancelled' || $assignment->status === 'Completed') {
            return $assignment->status;
        }
        $today = date('Y-m-d');
        if ($assignment->deadline && $assignment->deadline->lt($today)) {
            $assignment->status = 'Overdue';
        } elseif ($assignment->date_assigned && $assignment->date_assigned->gt($today)) {
            $assignment->status = 'Upcoming';
        } else {
            $assignment->status = 'Active';
        }
        $assignment->save();
        return $assignment->status;
    }

    private function authorizeAccess(Request $request, Assignment $assignment): void
    {
        $user = $request->user();
        if ($user->isAdmin()) return;
        $teacher = $user->teacher;
        if (! $teacher) abort(403);
        $class = SchoolClass::find($assignment->class_id);
        if (! $class || $class->teacher_id !== $teacher->id) abort(403);
    }

    private function authorizeClassId(Request $request, int $classId): void
    {
        $user = $request->user();
        if ($user->isAdmin()) return;
        $teacher = $user->teacher;
        if (! $teacher) abort(403);
        $class = SchoolClass::find($classId);
        if (! $class || $class->teacher_id !== $teacher->id) abort(403, 'You can only create assignments for your own classes.');
    }
}
