<?php

namespace App\Http\Requests\Api\Language;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'string|max:10|unique:languages,code,' . $this->route('language')->id,
            'name' => 'string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
