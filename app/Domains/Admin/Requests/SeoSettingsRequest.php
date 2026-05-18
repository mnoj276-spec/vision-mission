<?php

namespace App\Domains\Admin\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SeoSettingsRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'meta_title'       => 'required|string|max:255',
            'meta_description' => 'required|string|max:500',
            'meta_keywords'    => 'required|string|max:255',
        ];
    }
}
