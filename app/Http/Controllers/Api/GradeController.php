<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GradeCategory;
use App\Models\GradeItem;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\StudentGrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GradeController extends Controller
{
    /**
     * Grade Categories CRUD (scoped to a class).
     */
    public function indexCategories(Request $request, SchoolClass $class): JsonResponse
    {
        $this->authorizeClass($request, $class);

        return response()->json(['data' => $class->gradeCategories()->orderBy('id')->get()]);
    }

    public function storeCategory(Request $request, SchoolClass $class): JsonResponse
    {
        $this->authorizeClass($request, $class);
        $payload = $request->validate([
            'category_name' => 'required|string|max:128',
            'percentage_weight' => 'required|numeric|min:0|max:100',
        ]);
        $newTotal = (float) $class->gradeCategories()->sum('percentage_weight') + (float) $payload['percentage_weight'];
        if ($newTotal > 100) {
            abort(422, 'Grade category weights cannot exceed 100%.');
        }
        $cat = $class->gradeCategories()->create($payload);

        return response()->json(['data' => $cat], 201);
    }

    public function updateCategory(Request $request, SchoolClass $class, GradeCategory $category): JsonResponse
    {
        $this->authorizeClass($request, $class);
        if ($category->class_id !== $class->id) {
            abort(404);
        }
        $payload = $request->validate([
            'category_name' => 'required|string|max:128',
            'percentage_weight' => 'required|numeric|min:0|max:100',
        ]);
        $newTotal = (float) $class->gradeCategories()->whereKeyNot($category->id)->sum('percentage_weight') + (float) $payload['percentage_weight'];
        if ($newTotal > 100) {
            abort(422, 'Grade category weights cannot exceed 100%.');
        }
        $category->update($payload);

        return response()->json(['data' => $category]);
    }

    public function destroyCategory(Request $request, SchoolClass $class, GradeCategory $category): JsonResponse
    {
        $this->authorizeClass($request, $class);
        if ($category->class_id !== $class->id) {
            abort(404);
        }
        $category->delete();

        return response()->json(['message' => 'Category deleted.']);
    }

    /**
     * Grade Items CRUD.
     */
    public function indexItems(Request $request, SchoolClass $class): JsonResponse
    {
        $this->authorizeClass($request, $class);

        return response()->json(['data' => $class->gradeItems()->with('category')->orderBy('id')->get()]);
    }

    public function storeItem(Request $request, SchoolClass $class): JsonResponse
    {
        $this->authorizeClass($request, $class);
        $payload = $request->validate([
            'category_id' => 'required|integer|exists:grade_categories,id',
            'title' => 'required|string|max:191',
            'maximum_score' => 'required|numeric|min:0',
            'date_given' => 'nullable|date',
        ]);
        if (! $class->gradeCategories()->where('id', $payload['category_id'])->exists()) {
            abort(422, 'Category does not belong to this class.');
        }
        $item = $class->gradeItems()->create($payload);

        return response()->json(['data' => $item->load('category')], 201);
    }

    public function updateItem(Request $request, SchoolClass $class, GradeItem $item): JsonResponse
    {
        $this->authorizeClass($request, $class);
        if ($item->class_id !== $class->id) {
            abort(404);
        }
        $payload = $request->validate([
            'category_id' => 'required|integer|exists:grade_categories,id',
            'title' => 'required|string|max:191',
            'maximum_score' => 'required|numeric|min:0',
            'date_given' => 'nullable|date',
        ]);
        if (! $class->gradeCategories()->whereKey($payload['category_id'])->exists()) {
            abort(422, 'Category does not belong to this class.');
        }
        $item->update($payload);

        return response()->json(['data' => $item->load('category')]);
    }

    public function destroyItem(Request $request, SchoolClass $class, GradeItem $item): JsonResponse
    {
        $this->authorizeClass($request, $class);
        if ($item->class_id !== $class->id) {
            abort(404);
        }
        $item->delete();

        return response()->json(['message' => 'Grade item deleted.']);
    }

    /**
     * Student Grades CRUD + bulk save.
     */
    public function indexStudentGrades(Request $request, SchoolClass $class, int $studentId): JsonResponse
    {
        $this->authorizeClass($request, $class);

        return response()->json(['data' => StudentGrade::whereHas('gradeItem', fn ($q) => $q->where('class_id', $class->id))
            ->where('student_id', $studentId)->with('gradeItem.category')->get()]);
    }

    public function saveStudentGrades(Request $request, SchoolClass $class, int $studentId): JsonResponse
    {
        $this->authorizeClass($request, $class);
        $payload = $request->validate([
            'scores' => 'required|array',
            'scores.*.grade_item_id' => 'required|integer|exists:grade_items,id',
            'scores.*.score' => 'required|numeric|min:0',
            'scores.*.remarks' => 'nullable|string|max:255',
        ]);

        if (! $class->students()->where('students.id', $studentId)->exists()) {
            abort(422, 'Student is not enrolled in this class.');
        }

        $items = GradeItem::where('class_id', $class->id)
            ->whereIn('id', collect($payload['scores'])->pluck('grade_item_id'))
            ->get()
            ->keyBy('id');

        $now = now();
        $rows = [];
        foreach ($payload['scores'] as $s) {
            $item = $items->get($s['grade_item_id']);
            if (! $item) {
                abort(422, 'Grade item does not belong to this class.');
            }
            if ((float) $s['score'] > (float) $item->maximum_score) {
                abort(422, "Score cannot exceed maximum score for {$item->title}.");
            }
            $rows[] = [
                'student_id' => $studentId,
                'grade_item_id' => $s['grade_item_id'],
                'score' => $s['score'],
                'remarks' => $s['remarks'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('student_grades')->upsert(
            $rows,
            ['student_id', 'grade_item_id'],
            ['score', 'remarks', 'updated_at']
        );

        return response()->json(['message' => 'Scores saved.']);
    }

    /**
     * Weighted final grade per plan §4.6:
     * For each category, compute mean of (score / max_score) over items.
     * Then weighted_sum = sum(category_mean * category_percentage_weight).
     * Returns breakdown by category + final grade on 0-100 scale.
     */
    public function finalGrade(Request $request, SchoolClass $class, int $studentId): JsonResponse
    {
        $this->authorizeClass($request, $class);

        $categories = $class->gradeCategories()->with(['items' => function ($q) use ($studentId, $class) {
            $q->where('class_id', $class->id)->with(['studentGrades' => fn ($sg) => $sg->where('student_id', $studentId)]);
        }])->get();

        $breakdown = [];
        $finalGrade = 0.0;

        foreach ($categories as $cat) {
            $ratios = [];
            foreach ($cat->items as $item) {
                if ((float) $item->maximum_score <= 0) {
                    continue;
                }
                $grade = $item->studentGrades->first();
                if (! $grade) {
                    continue;
                } // skip items with no recorded score
                $ratios[] = ((float) $grade->score) / ((float) $item->maximum_score);
            }
            $categoryAverage = count($ratios) > 0 ? array_sum($ratios) / count($ratios) : 0.0;
            $weighted = $categoryAverage * ((float) $cat->percentage_weight);
            $finalGrade += $weighted;
            $breakdown[] = [
                'category_id' => $cat->id,
                'category_name' => $cat->category_name,
                'percentage_weight' => (float) $cat->percentage_weight,
                'category_average' => round($categoryAverage * 100, 2), // as percent 0-100
                'weighted_contribution' => round($weighted, 4),
                'items_counted' => count($ratios),
            ];
        }

        return response()->json([
            'data' => [
                'class_id' => $class->id,
                'student_id' => $studentId,
                'final_grade' => round($finalGrade, 2),
                'breakdown' => $breakdown,
            ],
        ]);
    }

    private function authorizeClass(Request $request, SchoolClass $class): void
    {
        $user = $request->user();
        if ($user->isAdmin()) {
            return;
        }
        $teacher = $user->teacher;
        if (! $teacher || $class->teacher_id !== $teacher->id) {
            abort(403, 'You can only manage grades for your own classes.');
        }
    }
}
