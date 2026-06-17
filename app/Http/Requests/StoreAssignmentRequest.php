<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'class_id' => 'required|integer|exists:classes,id',
            'title' => 'required|string|max:191',
            'description' => 'nullable|string',
            'date_assigned' => 'required|date',
            'deadline' => 'required|date|after_or_equal:date_assigned',
            'maximum_score' => 'required|numeric|min:0',
            'status' => 'nullable|in:Upcoming,Active,Overdue,Completed,Cancelled',
            'attachment_link' => 'nullable|url|max:255',
        ];
    }
}
