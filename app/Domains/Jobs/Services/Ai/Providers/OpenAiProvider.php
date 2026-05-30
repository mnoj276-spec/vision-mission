<?php

namespace App\Domains\Jobs\Services\Ai\Providers;

use App\Domains\Jobs\Services\Ai\Contracts\AiProviderInterface;
use App\Models\JobPost;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiProvider implements AiProviderInterface
{
    protected ?string $apiKey = null;
    protected string $model;

    public function __construct()
    {
        $this->apiKey = config('services.ai.openai.key', '');
        $this->model  = config('services.ai.openai.model', 'gpt-4o-mini');
    }

    public function generateContent(JobPost $job): array
    {
        if (empty($this->apiKey)) {
            throw new \Exception("OpenAI API key is missing. Please configure OPENAI_API_KEY.");
        }

        $prompt = $this->buildPrompt($job);

        Log::info("Dispatching OpenAI content generation for Job ID: {$job->id}");

        $response = Http::withToken($this->apiKey)
            ->timeout(60)
            ->post('https://api.openai.com/v1/chat/completions', [
                'model'           => $this->model,
                'messages'        => [
                    [
                        'role'    => 'system',
                        'content' => 'You are a structured database parser that outputs ONLY strictly valid JSON.'
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature'     => 0.2,
                'response_format' => ['type' => 'json_object'],
            ]);

        if (!$response->successful()) {
            $error = $response->json('error.message') ?? $response->body();
            Log::error("OpenAI API call failed: {$error}");
            throw new \Exception("OpenAI returned error: {$error}");
        }

        $body = $response->json();
        $rawJson = $body['choices'][0]['message']['content'] ?? '{}';
        
        $decoded = json_decode($rawJson, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error("OpenAI returned invalid JSON: {$rawJson}");
            throw new \Exception("OpenAI output was not valid JSON: " . json_last_error_msg());
        }

        return $this->validateAndNormalizeResponse($decoded);
    }

    protected function buildPrompt(JobPost $job): string
    {
        $year = date('Y');
        $title = $job->title;
        $category = $job->category->name ?? 'Government Job';
        $department = $job->department->name ?? 'Government Board';
        $state = $job->state->name ?? 'India';
        $desc = strip_tags($job->description);
        $salary = $job->salary_min > 0 ? "INR " . number_format($job->salary_min) . " to " . number_format($job->salary_max) . " per month" : "As per Government Scale";
        $age = $job->age_limit ?: "Standard government rules";
        $vacancies = $job->vacancy_count > 0 ? $job->vacancy_count : "Announced";
        $fee = $job->application_fee > 0 ? "INR " . number_format($job->application_fee, 2) : "Free / No Fee";
        $deadline = $job->last_date_to_apply ? $job->last_date_to_apply->format('d F Y') : 'Announced soon';

        return "You are an expert Government Recruitment Content Architect and Senior SEO copywriter.
Analyze the following government recruitment details:
- Title: \"{$title}\"
- Department: \"{$department}\"
- Category: \"{$category}\"
- Region/State: \"{$state}\"
- Original Raw Description: \"{$desc}\"
- Salary particulars: \"{$salary}\"
- Age Criteria: \"{$age}\"
- Total vacancies: \"{$vacancies}\"
- Application Fee: \"{$fee}\"
- Last date to apply: \"{$deadline}\"

Generate a highly structured JSON response to enrich this recruitment announcement.

CRITICAL ORIGINALITY & SEO RULES:
1. ORIGINALITY: Do not copy sentences verbatim from the original description. Paraphrase and restructure everything into professional, readable English. Avoid generic placeholders.
2. SEO META DATA:
   - meta_title: Must be a highly clickable title under 60 characters. Example: \"[Title] recruitment {$year} - Apply Now!\"
   - meta_description: A compelling summary between 120 and 150 characters with active voice, ending with a strong call-to-action.
3. DETAILED SUMMARY: 2-3 paragraphs. Discuss the importance of the department, general role summary, career prospects, and dynamic insights into the posting. Format using rich paragraphs.
4. ELIGIBILITY SECTION: Detailed list of academic requirements, age relaxations, nationality rules, and physical metrics if applicable, written using markdown list tags (e.g. bullet points).
5. SELECTION PROCESS: Clear step-by-step description of selection rounds (e.g., Written Exam, Interview, Skill Test, Document Verification).
6. FAQs: Generate exactly 3 to 5 highly relevant Frequently Asked Questions and detailed answers that candidates search for regarding this job (e.g. syllabus, age limits, post details).
7. SCHEMA CONTENT: Generate standard schema properties for Google JobPosting schema. Return an array of key-value attributes specifically representing:
   - 'skills': Array of critical skills needed
   - 'responsibilities': Array of job responsibilities
   - 'educationRequirements': Academic credentials required
   - 'experienceRequirements': Experience required (or Fresher friendly)

You MUST respond ONLY with a strictly valid JSON object matching the schema below. No markdown wrappers like ```json or ```, no trailing text, no extra spaces.
Strict JSON Schema:
{
  \"summary\": \"[detailed html or markdown paragraphs, using <p> or newlines]\",
  \"eligibility\": \"[detailed markdown bullet points describing educational and age criteria]\",
  \"selection_process\": \"[detailed description of rounds or phases]\",
  \"faqs\": [
    {
      \"question\": \"[FAQ Question 1]\",
      \"answer\": \"[FAQ Answer 1]\"
    },
    ...
  ],
  \"meta_title\": \"[custom high-CTR SEO title under 60 chars]\",
  \"meta_description\": \"[compelling summary under 160 chars]\",
  \"schema_content\": {
    \"skills\": [\"...\"],
    \"responsibilities\": [\"...\"],
    \"educationRequirements\": \"...\",
    \"experienceRequirements\": \"...\"
  }
}";
    }

    protected function validateAndNormalizeResponse(array $data): array
    {
        return [
            'summary'           => $data['summary'] ?? '',
            'eligibility'       => $data['eligibility'] ?? '',
            'selection_process' => $data['selection_process'] ?? '',
            'faqs'              => is_array($data['faqs'] ?? null) ? $data['faqs'] : [],
            'meta_title'        => $data['meta_title'] ?? '',
            'meta_description'  => $data['meta_description'] ?? '',
            'schema_content'    => is_array($data['schema_content'] ?? null) ? $data['schema_content'] : [],
        ];
    }
}
