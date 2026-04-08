<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
//dont know if this is actualy good practice for laravel

class EmailIdBaseRequest extends FormRequest
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
    protected function prepareForValidation(): void
    {
        if ($this->route('emailId')) {
            $this->merge([
                'email_id' => $this->route('emailId')
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'email_id' => ['required', 'uuid', 'exists:emails,id'],
        ];
    }

    public function getEmailId(): string
    {
        return $this->validated('email_id');
    }
}
