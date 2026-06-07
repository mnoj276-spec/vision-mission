<?php

namespace App\Domains\Extraction\Services;

use Illuminate\Support\Facades\Validator;

class ValidationService
{
    /**
     * Validate the structured extraction data.
     *
     * @param array $data
     * @return array ['isValid' => bool, 'errors' => array]
     */
    public function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'title' => 'required|string|min:10',
            'department' => 'required|string',
            'vacancy_count' => 'required|integer|min:0',
            'qualification' => 'required|string',
            'age_limit' => 'nullable|string',
            'salary' => 'nullable|string',
            'application_fee' => 'required|numeric|min:0',
            'selection_process' => 'nullable|string',
            'important_dates' => 'required|array',
            'important_dates.start_date' => 'nullable|date_format:Y-m-d',
            'important_dates.last_date_to_apply' => 'required|date_format:Y-m-d',
            'important_dates.exam_date' => 'nullable|date_format:Y-m-d',
            'important_dates.result_date' => 'nullable|date_format:Y-m-d',
            'official_website' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return [
                'isValid' => false,
                'errors' => $validator->errors()->toArray(),
            ];
        }

        return [
            'isValid' => true,
            'errors' => [],
        ];
    }
}
