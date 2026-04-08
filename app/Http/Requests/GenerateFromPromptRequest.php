<?php

namespace App\Http\Requests;

use App\Http\Requests\EmailIdBaseRequest;

class GenerateFromPromptRequest extends EmailIdBaseRequest
{
    public function rules(): array
    {
        return array_merge(
            parent::rules(),
            [
                'prompt' => ['required', 'string', 'max:5000'],
            ]
        );
    }
}
