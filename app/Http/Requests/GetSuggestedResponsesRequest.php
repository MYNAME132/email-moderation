<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetSuggestedResponsesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'emailId' => $this->route('emailId')
        ]);
    }

    public function rules(): array
    {
        return [
            'emailId' => ['required', 'uuid', 'exists:emails,id'],
        ];
    }

    public function getEmailId(): string
    {
        return $this->route('emailId');
    }
}
