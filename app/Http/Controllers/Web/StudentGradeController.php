<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class StudentGradeController extends Controller
{
    public function index(Request $request)
    {
        $student = $this->student($request);

        // Load the student's enrolled classes with their gradeCategories.items.studentGrades
        $classes = $student->classes()
            ->wherePivot('status', 'enrolled')
            ->with(['gradeCategories.items.studentGrades'])
            ->orderBy('class_name')
            ->get();

        // Compute final grade per class for this student
        $rows = $classes->map(function (SchoolClass $class) use ($student) {
            $total = 0.0;
            foreach ($class->gradeCategories as $cat) {
                $ratios = [];
                foreach ($cat->items as $item) {
                    if ((float) $item->maximum_score <= 0) {
                        continue;
                    }
                    $sg = $item->studentGrades->firstWhere('student_id', $student->id);
                    if (! $sg) {
                        continue;
                    }
                    $ratios[] = ((float) $sg->score) / ((float) $item->maximum_score);
                }
                $catAvg = count($ratios) > 0 ? array_sum($ratios) / count($ratios) : 0.0;
                $total += $catAvg * ((float) $cat->percentage_weight);
            }
            $final = round($total, 2);

            return [
                'class' => $class,
                'final' => $final,
                'letter' => $this->letter($final),
                'category_count' => $class->gradeCategories->count(),
            ];
        });

        $countWithGrades = $rows->filter(fn ($r) => $r['final'] > 0)->count();
        $averageFinal = $rows->count() > 0 ? round($rows->avg('final'), 2) : 0.0;

        return view('student.grades.index', [
            'student' => $student,
            'rows' => $rows,
            'countWithGrades' => $countWithGrades,
            'averageFinal' => $averageFinal,
        ]);
    }

    public function show(Request $request, SchoolClass $class)
    {
        $student = $this->student($request);

        // 403 if not enrolled
        $enrolled = $student->classes()
            ->wherePivot('status', 'enrolled')
            ->whereKey($class->id)
            ->exists();
        if (! $enrolled) {
            abort(403, 'You are not enrolled in this class.');
        }

        $class->load(['gradeCategories.items.studentGrades', 'teacher.user']);

        // Compute the student's final grade for this class
        $total = 0.0;
        foreach ($class->gradeCategories as $cat) {
            $ratios = [];
            foreach ($cat->items as $item) {
                if ((float) $item->maximum_score <= 0) {
                    continue;
                }
                $sg = $item->studentGrades->firstWhere('student_id', $student->id);
                if (! $sg) {
                    continue;
                }
                $ratios[] = ((float) $sg->score) / ((float) $item->maximum_score);
            }
            $catAvg = count($ratios) > 0 ? array_sum($ratios) / count($ratios) : 0.0;
            $total += $catAvg * ((float) $cat->percentage_weight);
        }
        $final = round($total, 2);

        // Build a per-category breakdown for the Blade view
        $categories = $class->gradeCategories->map(function ($cat) use ($student) {
            $rows = $cat->items->map(function ($item) use ($student) {
                $sg = $item->studentGrades->firstWhere('student_id', $student->id);
                $score = $sg?->score;
                $max = (float) $item->maximum_score;
                $ratio = ($score !== null && $max > 0) ? round(((float) $score) / $max * 100, 1) : null;

                return [
                    'title' => $item->title,
                    'date_given' => $item->date_given,
                    'score' => $score,
                    'maximum_score' => $item->maximum_score,
                    'ratio' => $ratio,
                    'remarks' => $sg?->remarks,
                ];
            });

            $catRatios = $rows->pluck('ratio')->filter(fn ($v) => $v !== null);
            $categoryAverage = $catRatios->count() > 0 ? round($catRatios->avg(), 1) : null;
            $weighted = $categoryAverage !== null ? round($categoryAverage * ((float) $cat->percentage_weight) / 100, 2) : 0.0;

            return [
                'name' => $cat->category_name,
                'weight' => $cat->percentage_weight,
                'rows' => $rows,
                'category_average' => $categoryAverage,
                'weighted_contribution' => $weighted,
            ];
        });

        return view('student.grades.show', [
            'student' => $student,
            'class' => $class,
            'categories' => $categories,
            'final' => $final,
            'letter' => $this->letter($final),
        ]);
    }

    private function letter(float $score): string
    {
        return match (true) {
            $score >= 90 => 'A',
            $score >= 85 => 'B+',
            $score >= 80 => 'B',
            $score >= 75 => 'C+',
            $score >= 70 => 'C',
            $score >= 60 => 'D',
            default => 'F',
        };
    }

    private function student(Request $request)
    {
        $student = $request->user()->student;
        if (! $student) {
            abort(403, 'No student profile linked to this account.');
        }

        return $student;
    }
}
