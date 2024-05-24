<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileRequest extends FormRequest
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
            'user_id' => [
                'required',
                Rule::exists('users', 'id'),
                Rule::unique('profiles', 'user_id')
            ],
            'phone' => 'nullable|string|max:255',
            'address' =>'required|string|max:255',
            'birthday' => 'nullable|date',
            'gender' => 'required|integer|between:1,2,3',
            'avatar' =>  'nullable|string|max:600',
            'position_id' => 'required|integer|exists:positions,id',
            'division_id' => 'required|integer|exists:divisions,id',
            'created_at' => 'nullable',
            'updated_at' => 'nullable',
            'deleted_at' => 'nullable'
        ];
    }
}
