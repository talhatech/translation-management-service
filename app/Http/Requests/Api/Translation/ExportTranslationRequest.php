<?php

namespace App\Http\Requests\Api\Translation;

use Illuminate\Foundation\Http\FormRequest;

class ExportTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => 'required|string|exists:languages,code',
            'tags' => 'array',
            'tags.*' => 'string',
        ];
    }
}
