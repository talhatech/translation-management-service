<?php

namespace App\Http\Requests\Api\Language;

use Illuminate\Foundation\Http\FormRequest;

class StoreLanguageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|max:10|unique:languages,code',
            'name' => 'required|string|max:255',
            'is_active' => 'boolean',
        ];
    }
}
