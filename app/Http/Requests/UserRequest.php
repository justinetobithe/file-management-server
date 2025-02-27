<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'sometimes|nullable|string|min:8',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'role' => 'required|in:user,admin',
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'status' => 'nullable|integer|in:0,1',
            'department_id' => 'nullable|exists:departments,id',
            'designation_id' => 'nullable|exists:designations,id',

            'current_password' => 'sometimes|nullable|string|min:8',
        ];
    }
}
