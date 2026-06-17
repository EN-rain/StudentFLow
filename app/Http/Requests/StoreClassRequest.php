<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'class_name' => 'required|string|max:255',
            'section' => 'nullable|string|max:64',
            'subject' => 'required|string|max:255',
            'grade_level' => 'nullable|string|max:128',
            'school_year' => 'nullable|string|max:32',
            'semester' => 'nullable|string|max:64',
            'schedule' => 'nullable|string|max:255',
            'room' => 'nullable|string|max:128',
            'teacher_id' => 'required|integer|exists:teachers,id',
            'status' => 'nullable|in:active,archived',
        ];
    }
}
