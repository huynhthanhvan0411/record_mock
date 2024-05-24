<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AuthRequest extends FormRequest
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
            'role_id' => 'required|integer|between:1,2', // '0' => 'user', '1' => 'admin
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'status' => 'required|integer|between:0,1', // '0' => 'inactive', '1' => 'active
            'password' => 'required|string|min:6|confirmed'
        ];
    }
}