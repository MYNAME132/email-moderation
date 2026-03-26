<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendEmailRequest extends FormRequest
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
            'sender' => ['required', 'email', 'email', 'max:225'],
            'receiver' => ['required', 'string', 'email', 'max:225'],
            'subject' => ['nullable', 'string', 'max:225'],
            'body' => ['nullable', 'string'],
        ];
    }

    public function toDto(): array
    {
        return $this->validated();
    }
}
