<?php

namespace App\Domains\Jobs\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title'                 => 'required|string|min:10|max:255',
            'category_id'           => 'required|integer|exists:categories,id',
            'department_id'         => 'required|integer|exists:departments,id',
            'state_id'              => 'required|integer|exists:states,id',
            'qualification_id'      => 'required|integer|exists:qualifications,id',
            'description'           => 'required|string|min:20',
            'salary_min'            => 'required|numeric|min:0',
            'salary_max'            => 'required|numeric|min:0',
            'vacancy_count'         => 'required|integer|min:1',
            'application_fee'       => 'required|numeric|min:0',
            'last_date_to_apply'    => 'required|date',
            'official_website_link' => 'required|url',
        ];
    }
}
