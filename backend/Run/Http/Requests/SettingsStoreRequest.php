<?php

namespace App\Http\Run\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SettingsStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'rootDir' => ['nullable', 'string'],
        ];
    }
}
