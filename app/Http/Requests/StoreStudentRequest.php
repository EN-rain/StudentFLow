<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id;

        return [
            'student_number' => ['required', 'string', 'max:32', Rule::unique('students', 'student_number')->ignore($studentId)],
            'first_name' => 'required|string|max:128',
            'middle_name' => 'nullable|string|max:128',
            'last_name' => 'required|string|max:128',
            'gender' => 'nullable|in:Male,Female,Other',
            'birth_date' => 'nullable|date',
            'email' => ['required', 'email', 'max:191', Rule::unique('students', 'email')->ignore($studentId)],
            'contact_number' => 'nullable|string|max:32',
            'address' => 'nullable|string|max:255',
            'guardian_name' => 'nullable|string|max:191',
            'guardian_contact' => 'nullable|string|max:32',
            'status' => 'nullable|in:active,disabled',
        ];
    }
}
