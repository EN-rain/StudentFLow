<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'class_id' => 'nullable|integer|exists:classes,id',
            'title' => 'required|string|max:191',
            'message' => 'required|string',
            'priority' => 'nullable|in:Normal,Important,Urgent',
            'publish_date' => 'required|date',
            'expiration_date' => 'nullable|date|after_or_equal:publish_date',
        ];
    }
}
