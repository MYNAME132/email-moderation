<?php

namespace App\Http\Requests;

use App\Enums\ResponseDecisionEnum;
use Illuminate\Foundation\Http\FormRequest;

class GetEmailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $decisionValues = array_map(fn(ResponseDecisionEnum $item) => $item->value, ResponseDecisionEnum::cases());

        return [
            'response_decision' => ['sometimes', 'string', 'in:' . implode(',', $decisionValues)],
            'sender' => ['sometimes', 'string', 'max:225'],
            'subject' => ['sometimes', 'string', 'max:225'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function toDto(): array
    {
        return $this->validated();
    }
}
