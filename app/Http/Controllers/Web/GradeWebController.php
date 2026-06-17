<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\GradeCategory;
use App\Models\GradeItem;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeWebController extends Controller
{
    /**
     * Class chooser - landing page for grades module.
     */
    public function index(Request $request)
    {
        $query = SchoolClass::query();
        if ($request->user()->isTeacher()) {
            $teacher = $request->user()->teacher;
            if ($teacher) $query->where('teacher_id', $teacher->id);
        }
        $classes = $query->orderBy('class_name')->get();
        return view('grades.index', compact('classes'));
    }

    /**
     * Per-class grade entry with category accordion + computed finals per student.
     */
    public function show(Request $request, SchoolClass $class)
    {
        $this->authorizeClass($request, $class);
        $class->load(['gradeCategories.items.studentGrades', 'students']);

        // Compute final grade per enrolled student
        $finals = [];
        foreach ($class->students as $student) {
            $total = 0.0;
            foreach ($class->gradeCategories as $cat) {
                $ratios = [];
                foreach ($cat->items as $item) {
                    if ((float) $item->maximum_score <= 0) continue;
                    $sg = $item->studentGrades->firstWhere('student_id', $student->id);
                    if (! $sg) continue;
                    $ratios[] = ((float) $sg->score) / ((float) $item->maximum_score);
                }
                $catAvg = count($ratios) > 0 ? array_sum($ratios) / count($ratios) : 0.0;
                $total += $catAvg * ((float) $cat->percentage_weight);
            }
            $finals[$student->id] = round($total, 2);
        }

        return view('grades.show', compact('class', 'finals'));
    }

    /**
     * Save all scores for a class (POST).
     * Body: scores[<grade_item_id>][<student_id>] = score (optional)
     */
    public function save(Request $request, SchoolClass $class)
    {
        $this->authorizeClass($request, $class);
        $payload = $request->validate([
            'scores' => 'nullable|array',
            'scores.*' => 'nullable|array',
            'scores.*.*' => 'nullable|numeric|min:0',
        ]);

        $scores = $payload['scores'] ?? [];
        $items = $class->gradeItems()->get()->keyBy('id');
        $rows = [];
        $now = now();
        foreach ($scores as $itemId => $studentScores) {
            $item = $items->get((int) $itemId);
            if (! $item) {
                return back()->withErrors(['scores' => 'Grade item does not belong to this class.']);
            }
            foreach ($studentScores as $studentId => $score) {
                if ($score === null || $score === '') continue;
                if ((float) $score > (float) $item->maximum_score) {
                    return back()->withErrors(['scores' => "Score cannot exceed maximum score for {$item->title}."]);
                }
                $rows[] = [
                    'student_id' => (int) $studentId,
                    'grade_item_id' => (int) $itemId,
                    'score' => (float) $score,
                    'remarks' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if ($rows) {
            DB::table('student_grades')->upsert(
                $rows,
                ['student_id', 'grade_item_id'],
                ['score', 'updated_at']
            );
        }

        return redirect("/grades/{$class->id}")->with('status', 'Grades saved.');
    }

    public function storeCategory(Request $request, SchoolClass $class)
    {
        $this->authorizeClass($request, $class);
        $payload = $request->validate([
            'category_name' => 'required|string|max:128',
            'percentage_weight' => 'required|numeric|min:0|max:100',
        ]);
        $category = $class->gradeCategories()->create($payload);
        ActivityLogger::log($request, 'grade_category.created', $category);
        return back()->with('status', 'Grade category created.');
    }

    public function updateCategory(Request $request, SchoolClass $class, GradeCategory $category)
    {
        $this->authorizeClass($request, $class);
        if ($category->class_id !== $class->id) abort(404);
        $payload = $request->validate([
            'category_name' => 'required|string|max:128',
            'percentage_weight' => 'required|numeric|min:0|max:100',
        ]);
        $category->update($payload);
        ActivityLogger::log($request, 'grade_category.updated', $category);
        return back()->with('status', 'Grade category updated.');
    }

    public function destroyCategory(Request $request, SchoolClass $class, GradeCategory $category)
    {
        $this->authorizeClass($request, $class);
        if ($category->class_id !== $class->id) abort(404);
        $category->delete();
        ActivityLogger::log($request, 'grade_category.deleted', $category);
        return back()->with('status', 'Grade category deleted.');
    }

    public function storeItem(Request $request, SchoolClass $class)
    {
        $this->authorizeClass($request, $class);
        $payload = $request->validate([
            'category_id' => 'required|integer|exists:grade_categories,id',
            'title' => 'required|string|max:191',
            'maximum_score' => 'required|numeric|min:0',
            'date_given' => 'nullable|date',
        ]);
        if (! $class->gradeCategories()->where('id', $payload['category_id'])->exists()) abort(422);
        $item = $class->gradeItems()->create($payload);
        ActivityLogger::log($request, 'grade_item.created', $item);
        return back()->with('status', 'Grade item created.');
    }

    public function updateItem(Request $request, SchoolClass $class, GradeItem $item)
    {
        $this->authorizeClass($request, $class);
        if ($item->class_id !== $class->id) abort(404);
        $payload = $request->validate([
            'category_id' => 'required|integer|exists:grade_categories,id',
            'title' => 'required|string|max:191',
            'maximum_score' => 'required|numeric|min:0',
            'date_given' => 'nullable|date',
        ]);
        if (! $class->gradeCategories()->where('id', $payload['category_id'])->exists()) abort(422);
        $item->update($payload);
        ActivityLogger::log($request, 'grade_item.updated', $item);
        return back()->with('status', 'Grade item updated.');
    }

    public function destroyItem(Request $request, SchoolClass $class, GradeItem $item)
    {
        $this->authorizeClass($request, $class);
        if ($item->class_id !== $class->id) abort(404);
        $item->delete();
        ActivityLogger::log($request, 'grade_item.deleted', $item);
        return back()->with('status', 'Grade item deleted.');
    }

    private function authorizeClass(Request $request, SchoolClass $class): void
    {
        $user = $request->user();
        if ($user->isAdmin()) return;
        $teacher = $user->teacher;
        if (! $teacher || $class->teacher_id !== $teacher->id) abort(403);
    }
}
