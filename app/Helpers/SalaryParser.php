<?php

namespace App\Helpers;

class SalaryParser
{
    /**
     * Parses a raw salary text string into structured salary attributes.
     *
     * @param string|null $text
     * @return array
     */
    public static function parse(?string $text): array
    {
        $result = [
            'salary_min' => null,
            'salary_max' => null,
            'salary_grade' => null,
            'pay_level' => null,
            'pay_matrix' => null,
            'pay_scale' => null,
            'stipend' => null,
        ];

        if (empty($text)) {
            return $result;
        }

        $originalText = trim($text);
        
        // Normalize various dash-like symbols (en-dash, em-dash, tilde, and 'to' word) to standard hyphen '-'
        // Also trim leading/trailing spaces around dashes
        $normalized = str_replace(['–', '—', '~', ' to ', ' TO '], '-', $text);
        $normalized = preg_replace('/\s*-\s*/', '-', $normalized);
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        // 1. Identify Stipend / Honorarium / Fellowship
        if (preg_match('/(stipend|fellowship|honorarium|consolidated|fixed pay|fixed salary|consolidated pay)/i', $normalized)) {
            // It has stipend context
            $result['stipend'] = $originalText;
        }

        // 2. Extract Pay Level / Pay Matrix Level
        // Matches: "Pay Level-4", "Level 10", "Pay Matrix Level 7", "Level-10"
        if (preg_match('/(?:pay\s+)?(?:matrix\s+)?level\s*[- ]*([a-zA-Z0-9]+)/i', $normalized, $levelMatches)) {
            $levelVal = trim($levelMatches[1]);
            $result['pay_level'] = "Level " . $levelVal;
        }

        // 3. Extract Pay Matrix details
        if (preg_match('/(?:pay\s+matrix|7th\s+cpc|cpc)/i', $normalized)) {
            if (preg_match('/(?:pay\s+matrix\s+(?:level\s+\d+)?|7th\s+cpc|cpc)/i', $normalized, $matrixMatches)) {
                $result['pay_matrix'] = trim($matrixMatches[0]);
            } else {
                $result['pay_matrix'] = "7th CPC";
            }
        }

        // 4. Extract Grade Pay / GP
        if (preg_match('/(?:grade\s+pay|gp)\s*(?:rs\.?|inr|₹)?\s*([\d,]+)/i', $normalized, $gpMatches)) {
            $gpVal = str_replace(',', '', $gpMatches[1]);
            $result['salary_grade'] = "Grade Pay " . $gpVal;
        }

        // 5. Extract Numeric Salary Min and Max (Pay Scale ranges)
        // Strip grade pay from the string we look at to prevent matching it as min/max
        $cleanForNumbers = preg_replace('/(?:grade\s+pay|gp)\s*(?:rs\.?|inr|₹)?\s*[\d,]+/i', '', $normalized);

        // Look for number ranges: "56100-177500", "₹19,900-₹63,200", "Rs.56100-177500", "Rs. 15600-39100"
        if (preg_match('/(?:rs\.?|inr|₹)?\s*([\d,]+)\s*-\s*(?:rs\.?|inr|₹)?\s*([\d,]+)/i', $cleanForNumbers, $rangeMatches)) {
            $min = (float) str_replace(',', '', $rangeMatches[1]);
            $max = (float) str_replace(',', '', $rangeMatches[2]);
            if ($min > 100) {
                $result['salary_min'] = $min;
            }
            if ($max > 100) {
                $result['salary_max'] = $max;
            }
        } else {
            // Check for single salary numbers: "Rs.25500 + Allowances", "Rs 25500"
            if (preg_match('/(?:rs\.?|inr|₹|pay\s+of|stipend\s+of|fixed\s+pay\s+of)\s*([\d,]+)/i', $cleanForNumbers, $singleMatches)) {
                $min = (float) str_replace(',', '', $singleMatches[1]);
                if ($min > 100) {
                    $result['salary_min'] = $min;
                }
            }
        }

        // Failsafe: if salary_min is still null, extract any numeric integers of 4 to 6 digits
        if (is_null($result['salary_min'])) {
            preg_match_all('/\b\d{4,6}\b/', $cleanForNumbers, $failsafeMatches);
            if (!empty($failsafeMatches[0])) {
                $numbers = array_map(fn($n) => (float)$n, $failsafeMatches[0]);
                if (count($numbers) >= 2) {
                    sort($numbers);
                    $result['salary_min'] = $numbers[0];
                    $result['salary_max'] = $numbers[count($numbers) - 1];
                } elseif (count($numbers) === 1) {
                    $result['salary_min'] = $numbers[0];
                }
            }
        }

        // 6. Formulate default pay scale (original text normalized)
        $result['pay_scale'] = $originalText;

        return $result;
    }
}
