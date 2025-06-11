<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {

        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'employee_id' => [
                'required',
                'string',
                Rule::unique('users', 'employee_id')->ignore($userId),
            ],
            'directorate_id' => [
                'nullable',
                'exists:directorates,id',
            ],
            'name' => [
                'required',
                'string',
                'max: 250',
            ],
            'mobile_number' => [
                'required',
                'string',
                'size:10',
                'regex:/^[0-9]{10}$/',
                Rule::unique('users', 'mobile_number')->ignore($userId),
            ],
            'email' => [
                'required',
                'email',
            ],
            'roles.*' => [
                'integer',
            ],
            'roles' => [
                'required',
                'array',
            ],
            'projects.*' => [
                'integer',
            ],
            'projects' => [
                'nullable',
                'array',
            ],
        ];
    }
}
