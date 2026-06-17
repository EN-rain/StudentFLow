<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() === true;
    }

    public function rules(): array
    {
        $teacher = $this->route('teacher');
        $userId = $teacher?->user_id;
        $teacherId = $teacher?->id;

        $rules = [
            'username' => ['required', 'string', 'max:64', Rule::unique('users', 'username')->ignore($userId)],
            'name' => 'required|string|max:191',
            'email' => ['required', 'email', 'max:191', Rule::unique('users', 'email')->ignore($userId)],
            'status' => 'required|in:active,disabled',
            'employee_number' => ['required', 'string', 'max:64', Rule::unique('teachers', 'employee_number')->ignore($teacherId)],
            'first_name' => 'required|string|max:128',
            'middle_name' => 'nullable|string|max:128',
            'last_name' => 'required|string|max:128',
            'department' => 'nullable|string|max:128',
            'contact_number' => 'nullable|string|max:32',
        ];

        if ($this->isMethod('post')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        } else {
            $rules['password'] = 'nullable|string|min:8|confirmed';
        }

        return $rules;
    }
}
