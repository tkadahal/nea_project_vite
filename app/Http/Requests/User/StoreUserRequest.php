<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'string',
                'unique:users,employee_id',
            ],
            'directorate_id' => [
                'required',
                'integer',
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
                'unique:users,mobile_number',
            ],
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
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
