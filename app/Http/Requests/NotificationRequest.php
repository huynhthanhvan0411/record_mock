<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NotificationRequest extends FormRequest
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
            'subject' => 'required|string',
            'message' => 'required|string',
            'send_all' => 'required|boolean',
            'scheduled_time' => 'required|in:8:00,17:00',
            'emails' => 'required_if:send_all,false|array', // Required if send_all is false
            'emails.*' => 'email', // Each email should be valid
        ];
    }
}
