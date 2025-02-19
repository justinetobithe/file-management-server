<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FolderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'folder_name' => 'required|string|max:255',
            'local_path' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'parent_id' => 'nullable|exists:folders,id',

            'department_id' => 'nullable|array',
            'department_id.*' => 'exists:departments,id',

            'current_files' => 'nullable|string',
            'uploaded_files.*' => 'nullable|file|max:20480',
        ];
    }
}
