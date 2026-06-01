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

    protected function prepareForValidation(): void
    {
        $this->merge([
            'meta_title'       => \App\Services\HtmlSanitizer::sanitizeString($this->meta_title),
            'meta_description' => \App\Services\HtmlSanitizer::sanitizeString($this->meta_description),
            'meta_keywords'    => \App\Services\HtmlSanitizer::sanitizeString($this->meta_keywords),
        ]);
    }
}
