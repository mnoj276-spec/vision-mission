<?php

namespace App\Domains\Scrapers\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AIService — moved from App\Services\AIService.
 * All logic preserved exactly; only namespace updated.
 * Reinforced by AI Reliability Engineer for Zero-Hallucination & Confidence Scoring.
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
            try {
                return $this->callAIEngine($rawText);
            } catch (\Exception $e) {
                Log::error("AI call failed: " . $e->getMessage() . ". Falling back.");
            }
        }
        return $this->runDeterministicAISimulator($rawText);
    }

    protected function callAIEngine(string $text): array
    {
        $prompt = "You are a Government Recruitment Database parser. Convert the following text into a highly precise JSON structure.\n"
            . "Raw Text: \"{$text}\"\n\n"
            . "Strict JSON Output Fields:\n"
            . "- title (string)\n"
            . "- description (string): 2-3 sentence overview of the role.\n"
            . "- age_limit (string or null)\n"
            . "- salary_min (numeric or null)\n"
            . "- salary_max (numeric or null)\n"
            . "- vacancy_count (integer)\n"
            . "- vacancies_breakdown (array of objects or null): Detailed vacancies breakdown. Each object must have fields: name (string), count (integer), type (string, must be one of: 'caste_category', 'department', 'trade', 'discipline', 'post').\n"
            . "- application_fee (numeric)\n"
            . "- last_date_to_apply (string: YYYY-MM-DD)\n"
            . "- selection_process (string or null)\n"
            . "- exam_pattern (string or null)\n\n"
            . "CRITICAL RELIABILITY RULES:\n"
            . "1. NEVER generate or make up any facts or assumptions.\n"
            . "2. You must ONLY summarize, format, and extract entities directly present in the Raw Text.\n"
            . "3. If a field (e.g. age_limit, salary, fee, dates, exam_pattern) is not explicitly and physically mentioned in the text, you MUST return null. NEVER output a default or placeholder value.\n"
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
            $result     = json_decode($response->body(), true);
            $parsedJson = $this->provider === 'openai'
                ? json_decode($result['choices'][0]['message']['content'] ?? '{}', true)
                : json_decode($result['candidates'][0]['content']['parts'][0]['text'] ?? '{}', true);
            if (!empty($parsedJson)) return $parsedJson;
        }
        throw new \Exception("AI Provider returned empty response.");
    }

    public function runDeterministicAISimulator(string $text): array
    {
        $data = [
            'title' => null,
            'description' => null,
            'age_limit' => null,
            'salary_min' => null,
            'salary_max' => null,
            'salary_grade' => null,
            'pay_level' => null,
            'pay_matrix' => null,
            'pay_scale' => null,
            'stipend' => null,
            'salary' => null,
            'vacancy_count' => 0,
            'vacancies_breakdown' => [],
            'application_fee' => 0.00,
            'last_date_to_apply' => null,
            'selection_process' => null,
            'exam_pattern' => null
        ];

        // Extract title
        if (preg_match('/^([^\n\r.]+)/', $text, $m)) {
            $data['title'] = trim($m[1]);
        }

        // Extract age limit strictly, no default placeholder
        if (preg_match('/(?:Age|Age\s+Limit)\s*:\s*([\d\s\-\s\w]+years|[\d\s\-]+)/i', $text, $m)) {
            $data['age_limit'] = trim($m[1]) . ' Years';
        }

        // Extract vacancy count
        if (preg_match('/(?:Vacancy|Vacancies|Posts)\s*:\s*(\d+)|\b(\d+)\s*(?:posts|vacancies)\b/i', $text, $m)) {
            $data['vacancy_count'] = (int)($m[1] ?: $m[2]);
        }

        // Description - Summarize existing text
        $data['description'] = mb_strlen($text) > 300 ? substr(trim($text), 0, 300) . '...' : trim($text);

        // Extract selection process and exam pattern strictly, no default placeholders
        if (preg_match('/(?:selection\s+process|selection\s+method|how\s+to\s+select)\b([^.]+)/i', $text, $m)) {
            $data['selection_process'] = trim($m[1]) . '.';
        }

        if (preg_match('/(?:exam\s+pattern|syllabus|test\s+pattern)\b([^.]+)/i', $text, $m)) {
            $data['exam_pattern'] = trim($m[1]) . '.';
        }

        // Extract Salary strictly, using SalaryParser
        if (preg_match('/(?:Salary|Pay\s+Scale|Salary\s+Range|Pay\s+Matrix|Pay\s+Level|Stipend|Rs\.?|INR|₹)\s*(?::|-|\b)\s*([^\n\r.]+)/i', $text, $m)) {
            $rawSalary = trim($m[0]);
            $data['salary'] = $rawSalary;
            $parsedSalary = \App\Helpers\SalaryParser::parse($rawSalary);
            foreach ($parsedSalary as $k => $v) {
                $data[$k] = $v;
            }
        }

        // Extract application fee
        if (preg_match('/(?:fee|application\s+fee|payment)\s*(?:Rs\.?|INR)?\s*(\d+)/i', $text, $m)) {
            $data['application_fee'] = (float)$m[1];
        }

        // Extract last date to apply
        if (preg_match('/(?:last\s+date|apply\s+before|deadline)\s*(?:is|on)?\s*([\d]{1,2}[-\/\.][\d]{1,2}[-\/\.][\d]{2,4})/i', $text, $m)) {
            try {
                $data['last_date_to_apply'] = \Carbon\Carbon::parse(str_replace('/', '-', $m[1]))->format('Y-m-d');
            } catch (\Exception $e) {}
        }

        // Parse Vacancies Breakdown
        $breakdown = [];
        if (preg_match_all('/(?:([A-Za-z\s]+)(?:-|–|:)\s*(\d+))/i', $text, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = trim($match[1]);
                $count = (int)$match[2];
                $lowerName = strtolower($name);
                if (in_array($lowerName, [
                    'vacancy', 'vacancies', 'posts', 'post', 'total', 'age', 'fee', 'rs', 'inr', 'stipend',
                    'salary', 'min', 'max', 'years', 'year', 'fee amount', 'application fee', 'vacancy count',
                    'date', 'last date', 'start date', 'opening date', 'closing date', 'exam date', 'result date',
                    'phone', 'mobile', 'contact', 'code', 'pin', 'otp', 'id', 'class', 'grade', 'level'
                ]) || str_contains($lowerName, 'date') || str_contains($lowerName, 'time')) {
                    continue;
                }
                $type = 'post';
                if (in_array(strtoupper($name), ['UR', 'OBC', 'SC', 'ST', 'EWS', 'GEN', 'GENERAL'])) {
                    $type = 'caste_category';
                }
                $breakdown[] = [
                    'name'  => $name,
                    'count' => $count,
                    'type'  => $type,
                ];
            }
        }
        $data['vacancies_breakdown'] = $breakdown;

        if ($data['vacancy_count'] === 0 && !empty($breakdown)) {
            $hasPostBreakdown = false;
            $hasCasteBreakdown = false;
            $postSum = 0;
            $casteSum = 0;
            foreach ($breakdown as $item) {
                if (($item['type'] ?? '') === 'post') {
                    $hasPostBreakdown = true;
                    $postSum += (int)($item['count'] ?? 0);
                } elseif (($item['type'] ?? '') === 'caste_category') {
                    $hasCasteBreakdown = true;
                    $casteSum += (int)($item['count'] ?? 0);
                }
            }
            if ($hasPostBreakdown) {
                $data['vacancy_count'] = $postSum;
            } elseif ($hasCasteBreakdown) {
                $data['vacancy_count'] = $casteSum;
            }
        }

        return $data;
    }

    /**
     * Compute a programmatic confidence breakdown and overall reliability score for the extraction.
     */
    public function computeConfidence(array $extracted, string $rawText): array
    {
        $scores = [];
        $rawTextLower = mb_strtolower($rawText);

        foreach ($extracted as $key => $val) {
            if ($val === null) {
                // If it is correctly null and not present in the raw text, we award 100%.
                $absent = true;
                if ($key === 'salary_min' || $key === 'salary_max') {
                    $absent = !preg_match('/(?:Salary|Pay\s+Scale|Rs\.?)\s*([\d,]+)/i', $rawText);
                } elseif ($key === 'age_limit') {
                    $absent = !preg_match('/(?:Age|Age\s+Limit)\b/i', $rawText);
                } elseif ($key === 'last_date_to_apply') {
                    $absent = !preg_match('/(?:Last\s+Date|Apply\s+before|Deadline)\b/i', $rawText);
                } elseif ($key === 'selection_process') {
                    $absent = !preg_match('/(?:selection\s+process|selection\s+method|how\s+to\s+select)\b/i', $rawText);
                } elseif ($key === 'exam_pattern') {
                    $absent = !preg_match('/(?:exam\s+pattern|syllabus|test\s+pattern)\b/i', $rawText);
                }
                $scores[$key] = $absent ? 100.0 : 50.0;
                continue;
            }

            switch ($key) {
                case 'title':
                case 'description':
                case 'selection_process':
                case 'exam_pattern':
                    $valLower = mb_strtolower($val);
                    $words = array_filter(preg_split('/[\s,\.\-\(\)\/]+/u', $valLower));
                    if (empty($words)) {
                        $scores[$key] = 100.0;
                    } else {
                        $matchedCount = 0;
                        foreach ($words as $word) {
                            if (mb_strlen($word) > 2 && str_contains($rawTextLower, $word)) {
                                $matchedCount++;
                            }
                        }
                        $scores[$key] = round(($matchedCount / count($words)) * 100, 2);
                    }
                    break;

                case 'salary_min':
                case 'salary_max':
                case 'application_fee':
                case 'vacancy_count':
                    $numStr = (string)$val;
                    $numStrClean = preg_replace('/\.00$/', '', $numStr);
                    if ($numStrClean === '0' || str_contains($rawTextLower, $numStrClean) || str_contains(str_replace(',', '', $rawTextLower), $numStrClean)) {
                        $scores[$key] = 100.0;
                    } else {
                        $scores[$key] = 0.0;
                    }
                    break;

                case 'age_limit':
                    preg_match_all('/\d+/', $val, $valNums);
                    if (empty($valNums[0])) {
                        $scores[$key] = str_contains($rawTextLower, mb_strtolower($val)) ? 100.0 : 50.0;
                    } else {
                        $matched = 0;
                        foreach ($valNums[0] as $num) {
                            if (str_contains($rawTextLower, $num)) {
                                $matched++;
                            }
                        }
                        $scores[$key] = round(($matched / count($valNums[0])) * 100, 2);
                    }
                    break;

                case 'last_date_to_apply':
                    try {
                        $date = \Carbon\Carbon::parse($val);
                        $day = $date->format('j');
                        $dayZero = $date->format('d');
                        $year = $date->format('Y');
                        $monthNum = $date->format('m');
                        $monthName = mb_strtolower($date->format('F'));
                        $monthShort = mb_strtolower($date->format('M'));

                        $hasDay = str_contains($rawTextLower, $day) || str_contains($rawTextLower, $dayZero);
                        $hasMonth = str_contains($rawTextLower, $monthNum) || str_contains($rawTextLower, $monthName) || str_contains($rawTextLower, $monthShort);
                        $hasYear = str_contains($rawTextLower, $year) || str_contains($rawTextLower, substr($year, 2));

                        $matchesCount = ($hasDay ? 1 : 0) + ($hasMonth ? 1 : 0) + ($hasYear ? 1 : 0);
                        $scores[$key] = round(($matchesCount / 3) * 100, 2);
                    } catch (\Exception $e) {
                        $scores[$key] = 0.0;
                    }
                    break;

                default:
                    $scores[$key] = 100.0;
                    break;
            }
        }

        $overall = count($scores) > 0 ? array_sum($scores) / count($scores) : 100.0;

        return [
            'scores' => $scores,
            'overall' => round($overall, 2)
        ];
    }
}
