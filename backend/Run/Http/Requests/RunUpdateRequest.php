<?php

namespace App\Http\Run\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RunUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'runId' => ['required'],
            'status' => ['required'],
            'version' => ['required'],
            'artifacts' => ['nullable', 'mimes:zip', 'max:20480'],
        ];
    }
}
