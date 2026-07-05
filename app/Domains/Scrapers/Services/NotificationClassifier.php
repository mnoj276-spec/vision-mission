<?php

namespace App\Domains\Scrapers\Services;

use App\Domains\Scrapers\Enums\NotificationType;

class NotificationClassifier
{
    protected array $rules = [];

    public function __construct()
    {
        // Define regex patterns for every government notification type
        $this->rules = [
            NotificationType::ADMIT_CARD->value => [
                '/\badmit\s*card\b/i',
                '/\bhall\s*ticket\b/i',
                '/\bcall\s*letter\b/i',
                '/\bdownload\s*admit\b/i',
                '/\be-admit\b/i',
                '/\binterview\s*call\b/i'
            ],
            NotificationType::FINAL_ANSWER_KEY->value => [
                '/\bfinal\s*answer\s*key\b/i',
                '/\bmodified\s*answer\s*key\b/i',
                '/\bdecision\s*on\s*objections\b/i'
            ],
            NotificationType::ANSWER_KEY->value => [
                '/\banswer\s*key\b/i',
                '/\bkey\s*answers\b/i',
                '/\bresponse\s*sheet\b/i',
                '/\bomr\s*sheet\b/i',
                '/\bprovisional\s*key\b/i',
                '/\bprovisional\s*answer\s*key\b/i',
                '/\bobjection\s*notice\b/i',
                '/\bobjection\s*window\b/i'
            ],
            NotificationType::OBJECTION->value => [
                '/\bobjection\b/i',
                '/\braise\s*objections?\b/i',
                '/\brepresentation\s*on\s*questions\b/i',
                '/\bchallenge\s*key\b/i'
            ],
            NotificationType::MERIT_LIST->value => [
                '/\bmerit\s*list\b/i',
                '/\bshortlisted\s*candidates\b/i',
                '/\bselect\s*list\b/i',
                '/\brank\s*list\b/i',
                '/\bwaiting\s*list\b/i',
                '/\bselection\s*list\b/i',
                '/\bshortlisted\b/i'
            ],
            NotificationType::RESULT->value => [
                '/\bresult\b/i',
                '/\bmarks\b/i',
                '/\bcutoff\b/i',
                '/\bcut\s*off\b/i',
                '/\bcut-off\b/i',
                '/\bscore\s*card\b/i',
                '/\bscore-card\b/i',
                '/\bqualified\s*candidates\b/i',
                '/\bwritten\s*exam\s*status\b/i',
                '/\bwritten\s*result\b/i',
                '/\bfinal\s*result\b/i',
                '/\binterview\s*result\b/i'
            ],
            NotificationType::SYLLABUS->value => [
                '/\bsyllabus\b/i',
                '/\bexam\s*pattern\b/i',
                '/\bscheme\s*of\s*exam\b/i',
                '/\bcurriculum\b/i',
                '/\bselection\s*procedure\b/i'
            ],
            NotificationType::SCHOLARSHIP->value => [
                '/\bscholarship\b/i',
                '/\bfellowship\b/i',
                '/\bfinancial\s*assistance\b/i',
                '/\bresearch\s*grant\b/i',
                '/\bstudent\s*grant\b/i',
                '/\bstipend\b/i'
            ],
            NotificationType::ADMISSION->value => [
                '/\badmission\b/i',
                '/\bentrance\s*exam\b/i',
                '/\bcounselling\b/i',
                '/\bcounseling\b/i',
                '/\bseat\s*allotment\b/i',
                '/\badmission\s*schedule\b/i',
                '/\bprospectus\b/i'
            ],
            NotificationType::EXAM_NOTICE->value => [
                '/\bexam\s*notice\b/i',
                '/\bexam\s*schedule\b/i',
                '/\bexam\s*date\b/i',
                '/\bcbt\s*notice\b/i',
                '/\bwritten\s*exam\b/i',
                '/\bskill\s*test\b/i',
                '/\btyping\s*test\b/i',
                '/\bpet\s*\/?\s*pst\b/i',
                '/\bdv\s*schedule\b/i'
            ],
            NotificationType::WALK_IN->value => [
                '/\bwalk\s*-\s*in\b/i',
                '/\bwalk\s*in\b/i',
                '/\bwalkin\b/i'
            ],
            NotificationType::INTERVIEW->value => [
                '/\binterview\b/i',
                '/\bpersonality\s*test\b/i',
                '/\bviva\s*voce\b/i',
                '/\binterview\s*schedule\b/i'
            ],
            NotificationType::DV->value => [
                '/\bdocument\s*verification\b/i',
                '/\bdv\b/i',
                '/\bverification\s*of\s*documents\b/i',
                '/\bcertificate\s*verification\b/i'
            ],
            NotificationType::MEDICAL->value => [
                '/\bmedical\s*exam\b/i',
                '/\bmedical\s*test\b/i',
                '/\bmedical\s*examination\b/i',
                '/\bdme\b/i',
                '/\brme\b/i',
                '/\bfitnes\s*test\b/i'
            ],
            NotificationType::COUNSELLING->value => [
                '/\bcounselling\b/i',
                '/\bcounseling\b/i',
                '/\bseat\s*selection\b/i'
            ],
            NotificationType::SEAT_ALLOTMENT->value => [
                '/\bseat\s*allotment\b/i',
                '/\ballotment\s*letter\b/i',
                '/\ballocation\s*list\b/i'
            ],
            NotificationType::JOINING->value => [
                '/\bjoining\b/i',
                '/\bappointment\s*order\b/i',
                '/\breporting\s*date\b/i',
                '/\bposting\s*order\b/i'
            ],
            NotificationType::TENDER_RECRUITMENT->value => [
                '/\btender\s*for\s*recruitment\b/i',
                '/\boutsource\s*agency\b/i',
                '/\bagency\s*selection\b/i'
            ],
            NotificationType::GUEST_FACULTY->value => [
                '/\bguest\s*faculty\b/i',
                '/\bguest\s*teacher\b/i',
                '/\bguest\s*lecturer\b/i',
                '/\bpart\s*time\s*teacher\b/i'
            ],
            NotificationType::INTERNSHIP->value => [
                '/\binternship\b/i',
                '/\binterns\b/i'
            ],
            NotificationType::APPRENTICESHIP->value => [
                '/\bapprenticeship\b/i',
                '/\bapprentice\b/i'
            ],
            NotificationType::SPORTS_QUOTA->value => [
                '/\bsports\s*quota\b/i',
                '/\bsports\s*recruitment\b/i',
                '/\boutstanding\s*sportsperson\b/i'
            ],
            NotificationType::PWD->value => [
                '/\bpwd\b/i',
                '/\bphysically\s*handicapped\b/i',
                '/\bdivyangjan\b/i',
                '/\bperson\s*with\s*disabilit\b/i'
            ],
            NotificationType::SC->value => [
                '/\bsc\s*backlog\b/i',
                '/\bscheduled\s*caste\b/i'
            ],
            NotificationType::ST->value => [
                '/\bst\s*backlog\b/i',
                '/\bscheduled\s*tribe\b/i'
            ],
            NotificationType::OBC->value => [
                '/\bobc\s*backlog\b/i',
                '/\bother\s*backward\s*class\b/i'
            ],
            NotificationType::EWS->value => [
                '/\bews\b/i',
                '/\beconomically\s*weaker\b/i'
            ],
            NotificationType::EX_SERVICEMEN->value => [
                '/\bex\s*-\s*servicemen\b/i',
                '/\bex\s*servicemen\b/i',
                '/\besm\b/i'
            ],
            NotificationType::PROMOTION->value => [
                '/\bpromotion\b/i',
                '/\bdepartmental\s*exam\b/i',
                '/\bldce\b/i'
            ],
            NotificationType::TRANSFER->value => [
                '/\btransfer\b/i',
                '/\bmutual\s*transfer\b/i',
                '/\bposting\s*list\b/i'
            ],
            NotificationType::CONTRACT->value => [
                '/\bcontract\b/i',
                '/\bcontractual\b/i',
                '/\btemporary\s*basis\b/i',
                '/\bad\s*hoc\b/i'
            ],
            NotificationType::RETIREMENT_VACANCY->value => [
                '/\bretired\b/i',
                '/\bretirement\b/i',
                '/\bvacancy\s*for\s*retired\b/i'
            ],
            NotificationType::RECRUITMENT->value => [
                '/\brecruitment\b/i',
                '/\bvacancy\b/i',
                '/\bvacancies\b/i',
                '/\bnotification\b/i',
                '/\bapply\s*online\b/i',
                '/\bonline\s*application\b/i',
                '/\bjob\b/i',
                '/\bpost\b/i',
                '/\bposts\b/i',
                '/\bofficer\b/i',
                '/\bclerk\b/i',
                '/\bassistant\b/i'
            ]
        ];
    }

    /**
     * Classify the notification type of a post.
     *
     * @param string $title
     * @param string $rawText
     * @return string
     */
    public function classify(string $title, string $rawText = ''): string
    {
        $input = $title . ' ' . $rawText;

        // 1. Run Regex Patterns
        foreach ($this->rules as $type => $patterns) {
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $input)) {
                    return $type;
                }
            }
        }

        // 2. Fallback to AI-based Semantic classification (Simulated or Real AI call)
        $aiType = $this->classifyWithAI($title, $rawText);
        if ($aiType && $aiType !== NotificationType::UNKNOWN->value) {
            return $aiType;
        }

        // 3. Strict Safety Fallback Gate
        return NotificationType::UNKNOWN->value;
    }

    /**
     * Call AI service or simulator to classify notification.
     *
     * @param string $title
     * @param string $rawText
     * @return string
     */
    protected function classifyWithAI(string $title, string $rawText): string
    {
        $t = strtolower($title . ' ' . $rawText);
        if (str_contains($t, 'draft answer') || str_contains($t, 'provisional key')) {
            return NotificationType::ANSWER_KEY->value;
        }
        if (str_contains($t, 'syllabus') || str_contains($t, 'exam pattern') || str_contains($t, 'scheme of examination')) {
            return NotificationType::SYLLABUS->value;
        }
        if (str_contains($t, 'scholarship') || str_contains($t, 'fellowship') || str_contains($t, 'stipend')) {
            return NotificationType::SCHOLARSHIP->value;
        }
        if (str_contains($t, 'admission') || str_contains($t, 'entrance exam') || str_contains($t, 'counseling') || str_contains($t, 'counselling')) {
            return NotificationType::ADMISSION->value;
        }
        if (str_contains($t, 'admit card') || str_contains($t, 'hall ticket') || str_contains($t, 'call letter')) {
            return NotificationType::ADMIT_CARD->value;
        }
        if (str_contains($t, 'result') || str_contains($t, 'merit list') || str_contains($t, 'shortlisted')) {
            return NotificationType::RESULT->value;
        }

        return NotificationType::UNKNOWN->value;
    }
}
