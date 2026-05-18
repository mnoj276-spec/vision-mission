<?php

namespace App\Domains\Scrapers\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AIService — moved from App\Services\AIService.
 * All logic preserved exactly; only namespace updated.
 */
class AIService
{
    protected ?string $apiKey;
    protected string $provider;

    public function __construct()
    {
        $this->apiKey   = config('services.ai.key');
        $this->provider = config('services.ai.provider', 'gemini');
    }

    public function cleanAndSummarize(string $rawText): array
    {
        Log::info("AI cleaning initialized.");
        if (!empty($this->apiKey)) {
            try { return $this->callAIEngine($rawText); }
            catch (\Exception $e) { Log::error("AI call failed: " . $e->getMessage() . ". Falling back."); }
        }
        return $this->runDeterministicAISimulator($rawText);
    }

    protected function callAIEngine(string $text): array
    {
        $prompt = "You are a Government Recruitment Database parser. Convert the following text into a highly precise JSON structure.\nRaw Text: \"{$text}\"\n\nStrict JSON Output Fields:\n- title (string)\n- description (string): 2-3 sentence overview.\n- age_limit (string or null)\n- salary_min (numeric or null)\n- salary_max (numeric or null)\n- vacancy_count (integer)\n- application_fee (numeric)\n- last_date_to_apply (string: YYYY-MM-DD)\n- selection_process (string)\n- exam_pattern (string)\n\nCRITICAL: temperature=0.0, no hallucinations, null for missing fields.";

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
            $result     = json_decode($response->body(), true);
            $parsedJson = $this->provider === 'openai'
                ? json_decode($result['choices'][0]['message']['content'] ?? '{}', true)
                : json_decode($result['candidates'][0]['content']['parts'][0]['text'] ?? '{}', true);
            if (!empty($parsedJson)) return $parsedJson;
        }
        throw new \Exception("AI Provider returned empty response.");
    }

    protected function runDeterministicAISimulator(string $text): array
    {
        $data = ['title' => null, 'description' => null, 'age_limit' => null, 'salary_min' => null, 'salary_max' => null, 'vacancy_count' => 0, 'application_fee' => 0.00, 'last_date_to_apply' => null, 'selection_process' => null, 'exam_pattern' => null];
        if (preg_match('/^([^\n\r.]+)/', $text, $m)) $data['title'] = trim($m[1]);
        if (preg_match('/(?:Age|Age\s+Limit)\s*:\s*([\d\s\-\s\w]+years|[\d\s\-]+)/i', $text, $m)) $data['age_limit'] = trim($m[1]) . ' Years';
        else $data['age_limit'] = '21-30 Years';
        if (preg_match('/(?:Vacancy|Vacancies|Posts)\s*:\s*(\d+)|\b(\d+)\s*(?:posts|vacancies)\b/i', $text, $m)) $data['vacancy_count'] = (int)($m[1] ?: $m[2]);
        $data['description']      = substr(trim($text), 0, 300) . '...';
        $data['selection_process']= 'Written Examination followed by Personal Interview / Document Verification.';
        $data['exam_pattern']     = 'Objective type questions covering General Knowledge, Quantitative Aptitude, Reasoning, and English.';
        if (preg_match('/(?:Salary|Pay\s+Scale|Rs\.?)\s*([\d,]+)\s*(?:-|to)\s*([\d,]+)/i', $text, $m)) {
            $data['salary_min'] = (float) str_replace(',', '', $m[1]);
            $data['salary_max'] = (float) str_replace(',', '', $m[2]);
        } else { $data['salary_min'] = 35400.00; $data['salary_max'] = 112400.00; }
        return $data;
    }
}
