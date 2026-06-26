<?php

namespace App\Domains\Jobs\Services;

use App\Models\JobPost;
use Illuminate\Support\Facades\Log;

class RecruitmentLifecycleManager
{
    /**
     * Transition the parent post status and propagate attributes based on the new child notice.
     *
     * @param JobPost $parent
     * @param JobPost $child
     * @return void
     */
    public function transition(JobPost $parent, JobPost $child): void
    {
        $childTitle = strtolower($child->title);
        $childText = strtolower($child->description ?? '');
        $combinedText = $childTitle . ' ' . $childText;

        // 1. Cancellation Notice check (Transition to archived status)
        if (str_contains($combinedText, 'cancellation') || str_contains($combinedText, 'cancelled') || str_contains($combinedText, 'postponed indefinitely')) {
            $parent->update(['status' => 'archived']);
            Log::info("Lifecycle transition: Parent ID {$parent->id} transitioned to 'archived' due to Child ID {$child->id}");
            return;
        }

        // 2. Date Extension check (Set parent back to active/published, update deadline)
        if (str_contains($combinedText, 'extension') || str_contains($combinedText, 'extended') || str_contains($combinedText, 'reopen')) {
            if ($child->last_date_to_apply) {
                $parent->update([
                    'status' => 'published', // Active state
                    'last_date_to_apply' => $child->last_date_to_apply,
                    'expires_at' => $child->last_date_to_apply
                ]);
                Log::info("Lifecycle transition: Parent ID {$parent->id} deadline extended to {$child->last_date_to_apply->toDateString()}");
            }
            return;
        }

        // 3. Document Verification check (Transition to dv_schedule)
        if (str_contains($combinedText, 'document verification') || str_contains($combinedText, 'certificate verification') || str_contains($combinedText, 'dv schedule') || $child->post_type === 'dv_schedule') {
            $parent->update(['status' => 'dv_schedule']);
            Log::info("Lifecycle transition: Parent ID {$parent->id} transitioned to 'dv_schedule'");
            return;
        }

        // 4. Interview Schedule check (Transition to interview_schedule)
        if (str_contains($combinedText, 'interview') || str_contains($combinedText, 'viva') || str_contains($combinedText, 'personality test') || $child->post_type === 'interview_schedule') {
            $parent->update(['status' => 'interview_schedule']);
            Log::info("Lifecycle transition: Parent ID {$parent->id} transitioned to 'interview_schedule'");
            return;
        }

        // 5. Medical check (Transition to medical_exam)
        if (str_contains($combinedText, 'medical exam') || str_contains($combinedText, 'medical test') || str_contains($combinedText, 'medical examination') || $child->post_type === 'medical_exam') {
            $parent->update(['status' => 'medical_exam']);
            Log::info("Lifecycle transition: Parent ID {$parent->id} transitioned to 'medical_exam'");
            return;
        }

        // 6. Final Selection / Joining check (Transition to final_selection)
        if (str_contains($combinedText, 'joining') || str_contains($combinedText, 'appointment order') || str_contains($combinedText, 'final selection') || $child->post_type === 'final_selection') {
            $parent->update(['status' => 'final_selection']);
            Log::info("Lifecycle transition: Parent ID {$parent->id} transitioned to 'final_selection'");
            return;
        }

        // 7. Admit Card check (Transition to admit_card_released)
        if ($child->post_type === 'admit_card' || str_contains($combinedText, 'admit card') || str_contains($combinedText, 'hall ticket')) {
            $parent->update(['status' => 'admit_card_released']);
            Log::info("Lifecycle transition: Parent ID {$parent->id} transitioned to 'admit_card_released'");
            return;
        }

        // 8. Answer Key check (Transition to answer_key_released)
        if ($child->post_type === 'answer_key' || str_contains($combinedText, 'answer key') || str_contains($combinedText, 'objection')) {
            $parent->update(['status' => 'answer_key_released']);
            Log::info("Lifecycle transition: Parent ID {$parent->id} transitioned to 'answer_key_released'");
            return;
        }

        // 9. Result check (Transition to result_declared)
        if ($child->post_type === 'result' || str_contains($combinedText, 'result') || str_contains($combinedText, 'merit list')) {
            $parent->update(['status' => 'result_declared']);
            Log::info("Lifecycle transition: Parent ID {$parent->id} transitioned to 'result_declared'");
            return;
        }
    }
}
