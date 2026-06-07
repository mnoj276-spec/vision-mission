<?php

namespace App\Domains\Extraction\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiStructuringService
{
    protected ?string $apiKey;
    protected string $provider;

    public function __construct()
    {
        $this->apiKey   = config('services.ai.key');
        $this->provider = config('services.ai.provider', 'gemini');
    }

    /**
     * Structure raw text into structured JSON.
     *
     * @param string $rawText
     * @return array
     */
    public function structureText(string $rawText): array
    {
        if (empty($rawText)) {
            return $this->getDefaultDataStructure();
        }

        if (empty($this->apiKey) || app()->environment('testing')) {
            return $this->runDeterministicStructuringSimulator($rawText);
        }

        try {
            return $this->callAIEngine($rawText);
        } catch (\Exception $e) {
            Log::error("AiStructuringService API call failed: " . $e->getMessage() . ". Falling back to simulator.");
            return $this->runDeterministicStructuringSimulator($rawText);
        }
    }

    /**
     * Call the structured AI engine (Gemini or OpenAI).
     */
    protected function callAIEngine(string $text): array
    {
        $prompt = "You are a Government Recruitment Database parser. Convert the following text into a highly precise JSON structure.\n"
            . "Raw Text: \"{$text}\"\n\n"
            . "Strict JSON Output Fields:\n"
            . "- title (string or null): Job Title\n"
            . "- department (string or null): Department name\n"
            . "- vacancy_count (integer): Vacancy count (0 if not mentioned)\n"
            . "- qualification (string or null): Required Qualification\n"
            . "- age_limit (string or null): Age constraints\n"
            . "- salary (string or null): Salary range or pay scale details\n"
            . "- application_fee (numeric): Application fee amount (0.00 if free or not mentioned)\n"
            . "- selection_process (string or null): Details on how candidates are selected\n"
            . "- important_dates (object): Important dates with fields:\n"
            . "  * start_date (string/null, format YYYY-MM-DD)\n"
            . "  * last_date_to_apply (string/null, format YYYY-MM-DD)\n"
            . "  * exam_date (string/null, format YYYY-MM-DD)\n"
            . "  * result_date (string/null, format YYYY-MM-DD)\n"
            . "- official_website (string or null): URL of the official website\n\n"
            . "CRITICAL RELIABILITY RULES:\n"
            . "1. NEVER generate or make up any facts or assumptions.\n"
            . "2. You must ONLY extract entities directly present in the Raw Text.\n"
            . "3. If a field is not explicitly mentioned in the text, return null. Do not use default or placeholder values.\n"
            . "4. Output STRICT JSON format, temperature=0.0.";

        if ($this->provider === 'openai') {
            $response = Http::withToken($this->apiKey)->post('https://api.openai.com/v1/chat/completions', [
                'model'           => 'gpt-4o-mini',
                'messages'        => [['role' => 'user', 'content' => $prompt]],
                'temperature'     => 0.0,
                'response_format' => ['type' => 'json_object'],
            ]);
        } else {
            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$this->apiKey}", [
                'contents'        => [['parts' => [['text' => $prompt]]]],
                'generationConfig'=> ['temperature' => 0.0, 'responseMimeType' => 'application/json'],
            ]);
        }

        if ($response->successful()) {
            $body = $response->json();
            $jsonText = $this->provider === 'openai'
                ? ($body['choices'][0]['message']['content'] ?? '{}')
                : ($body['candidates'][0]['content']['parts'][0]['text'] ?? '{}');

            $parsed = json_decode($jsonText, true);
            if (is_array($parsed)) {
                return array_merge($this->getDefaultDataStructure(), $parsed);
            }
        }

        throw new \Exception("AI Structuring Provider returned empty/error response.");
    }

    /**
     * Default structural template.
     */
    protected function getDefaultDataStructure(): array
    {
        return [
            'title' => null,
            'department' => null,
            'vacancy_count' => 0,
            'qualification' => null,
            'age_limit' => null,
            'salary' => null,
            'application_fee' => 0.00,
            'selection_process' => null,
            'important_dates' => [
                'start_date' => null,
                'last_date_to_apply' => null,
                'exam_date' => null,
                'result_date' => null,
            ],
            'official_website' => null,
        ];
    }

    /**
     * Regex-based parsing simulator for fallback.
     */
    protected function runDeterministicStructuringSimulator(string $text): array
    {
        $data = $this->getDefaultDataStructure();

        // 1. Job Title (typically first line/sentence)
        if (preg_match('/(?:Job Title|Title)\s*:\s*([^\n\r]+)/i', $text, $m)) {
            $data['title'] = trim($m[1]);
        } elseif (preg_match('/^([^\n\r.]+)/', $text, $m)) {
            $data['title'] = trim($m[1]);
        }

        // 2. Department
        if (preg_match('/(?:Department|Organization|Dept)\s*:\s*([^\n\r]+)/i', $text, $m)) {
            $data['department'] = trim($m[1]);
        }

        // 3. Vacancy Count
        if (preg_match('/(?:Vacancy\s+Count|Vacancy|Vacancies|Posts|Post\s+Count)\s*:\s*(\d+)/i', $text, $m)) {
            $data['vacancy_count'] = (int)$m[1];
        }

        // 4. Qualification
        if (preg_match('/(?:Qualification|Qualifications|Eligibility)\s*:\s*([^\n\r]+)/i', $text, $m)) {
            $data['qualification'] = trim($m[1]);
        }

        // 5. Age Limit
        if (preg_match('/(?:Age|Age\s+Limit)\s*:\s*([^\n\r]+)/i', $text, $m)) {
            $data['age_limit'] = trim($m[1]);
        }

        // 6. Salary
        if (preg_match('/(?:Salary|Pay\s+Scale|Salary\s+Range)\s*:\s*([^\n\r]+)/i', $text, $m)) {
            $data['salary'] = trim($m[1]);
        }

        // 7. Application Fee
        if (preg_match('/(?:Fee|Application\s+Fee)\s*:?\s*(?:Rs\.?|INR|₹)?\s*(\d+)/i', $text, $m)) {
            $data['application_fee'] = (float)$m[1];
        }

        // 8. Selection Process
        if (preg_match('/(?:Selection\s+Process|Selection\s+Method)\s*:\s*([^\n\r]+)/i', $text, $m)) {
            $data['selection_process'] = trim($m[1]);
        }

        // 9. Important Dates
        if (preg_match('/(?:Start\s+Date|Opening\s+Date)\s*:\s*([\d\-]+)/i', $text, $m)) {
            $data['important_dates']['start_date'] = trim($m[1]);
        }
        if (preg_match('/(?:Last\s+Date|Closing\s+Date|Deadline|Last\s+Date\s+to\s+Apply)\s*:\s*([\d\-]+)/i', $text, $m)) {
            $data['important_dates']['last_date_to_apply'] = trim($m[1]);
        }
        if (preg_match('/(?:Exam\s+Date)\s*:\s*([\d\-]+)/i', $text, $m)) {
            $data['important_dates']['exam_date'] = trim($m[1]);
        }
        if (preg_match('/(?:Result\s+Date)\s*:\s*([\d\-]+)/i', $text, $m)) {
            $data['important_dates']['result_date'] = trim($m[1]);
        }

        // 10. Official Website
        if (preg_match('/(?:Official\s+Website|Website|Link)\s*:\s*(https?:\/\/[^\s\n\r]+)/i', $text, $m)) {
            $data['official_website'] = trim($m[1]);
        }

        return $data;
    }
}
