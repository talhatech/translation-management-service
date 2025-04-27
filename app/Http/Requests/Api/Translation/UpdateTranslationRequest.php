<?php

namespace App\Http\Requests\Api\Translation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'string|max:255',
            'value' => 'string',
            'language_id' => 'exists:languages,id',
            'tags' => 'array',
            'tags.*' => 'string|max:255',
        ];
    }
}
