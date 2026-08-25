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
            'official_website_link' => [
                'required',
                'url',
                function ($attribute, $value, $fail) {
                    try {
                        \App\Services\UrlSecurity::verifySafeUrl($value);
                    } catch (\Exception $e) {
                        $fail($e->getMessage());
                    }
                }
            ],
            'vacancy_details' => 'nullable|array',
            'vacancy_details.*.post_name' => 'required|string',
            'vacancy_details.*.total_post' => 'required|integer|min:0',
            'vacancy_details.*.eligibility' => 'required|string',
            'vacancy_details.*.sort_order' => 'nullable|integer',
            'category_wise_vacancies' => 'nullable|array',
            'category_wise_vacancies.*.post_name' => 'required|string',
            'category_wise_vacancies.*.ur' => 'required|integer|min:0',
            'category_wise_vacancies.*.ews' => 'required|integer|min:0',
            'category_wise_vacancies.*.ebc' => 'required|integer|min:0',
            'category_wise_vacancies.*.bc' => 'required|integer|min:0',
            'category_wise_vacancies.*.bc_female' => 'required|integer|min:0',
            'category_wise_vacancies.*.sc' => 'required|integer|min:0',
            'category_wise_vacancies.*.st' => 'required|integer|min:0',
            'category_wise_vacancies.*.total' => 'required|integer|min:0',
            'category_wise_vacancies.*.sort_order' => 'nullable|integer',
        ];
    }
}
