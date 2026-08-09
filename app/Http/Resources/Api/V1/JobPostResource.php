<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobPostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                => (int) $this->id,
            'postType'          => (string) $this->post_type,
            'title'             => (string) $this->title,
            'slug'              => (string) $this->slug,
            'description'       => (string) $this->description,
            'examPattern'       => $this->exam_pattern ? (string) $this->exam_pattern : null,
            'selectionProcess'  => $this->selection_process ? (string) $this->selection_process : null,
            'ageLimit'          => $this->age_limit ? (string) $this->age_limit : null,
            'salaryMin'         => $this->salary_min ? (float) $this->salary_min : null,
            'salaryMax'         => $this->salary_max ? (float) $this->salary_max : null,
            'salaryGrade'       => $this->salary_grade ? (string) $this->salary_grade : null,
            'payLevel'          => $this->pay_level ? (string) $this->pay_level : null,
            'payMatrix'         => $this->pay_matrix ? (string) $this->pay_matrix : null,
            'payScale'          => $this->pay_scale ? (string) $this->pay_scale : null,
            'stipend'           => $this->stipend ? (string) $this->stipend : null,
            'vacancyCount'      => (int) $this->vacancy_count,
            'applicationFee'    => (float) $this->application_fee,
            'officialWebsiteLink'=> $this->official_website_link ? (string) $this->official_website_link : null,
            'applyLink'         => $this->apply_link ? (string) $this->apply_link : null,
            'notificationPdfPath'=> $this->notification_pdf_path ? (string) $this->notification_pdf_path : null,
            'lastDateToApply'   => $this->last_date_to_apply ? $this->last_date_to_apply->toDateString() : null,
            'examDate'          => $this->exam_date ? $this->exam_date->toDateString() : null,
            'publishedAt'       => $this->published_at ? $this->published_at->toIso8601String() : null,
            'isFeatured'        => (bool) $this->is_featured,
            
            // Nested clean objects for relationships
            'category'          => $this->relationLoaded('category') && $this->category ? [
                'id'   => (int) $this->category->id,
                'name' => (string) $this->category->name,
                'slug' => (string) $this->category->slug,
            ] : ($this->category ? [
                'id'   => (int) $this->category->id,
                'name' => (string) $this->category->name,
                'slug' => (string) $this->category->slug,
            ] : null),

            'department'        => $this->relationLoaded('department') && $this->department ? [
                'id'   => (int) $this->department->id,
                'name' => (string) $this->department->name,
            ] : ($this->department ? [
                'id'   => (int) $this->department->id,
                'name' => (string) $this->department->name,
            ] : null),

            'state'             => $this->relationLoaded('state') && $this->state ? [
                'id'   => (int) $this->state->id,
                'name' => (string) $this->state->name,
            ] : ($this->state ? [
                'id'   => (int) $this->state->id,
                'name' => (string) $this->state->name,
            ] : null),

            'district'          => $this->relationLoaded('district') && $this->district ? [
                'id'   => (int) $this->district->id,
                'name' => (string) $this->district->name,
            ] : ($this->district ? [
                'id'   => (int) $this->district->id,
                'name' => (string) $this->district->name,
            ] : null),

            'qualification'     => $this->relationLoaded('qualification') && $this->qualification ? [
                'id'   => (int) $this->qualification->id,
                'name' => (string) $this->qualification->name,
            ] : ($this->qualification ? [
                'id'   => (int) $this->qualification->id,
                'name' => (string) $this->qualification->name,
            ] : null),

            // Dynamic Bookmark Checking
            'isBookmarked'      => auth('api')->check() 
                ? $this->bookmarks()->where('user_id', auth('api')->id())->exists() 
                : false,

            'parentId'          => $this->parent_id ? (int) $this->parent_id : null,
            'parent'            => $this->parent_id && $this->parent ? [
                'id'    => (int) $this->parent->id,
                'title' => (string) $this->parent->title,
                'slug'  => (string) $this->parent->slug,
            ] : null,
            'children'          => $this->children && $this->children->count() > 0 ? $this->children->map(fn($child) => [
                'id'       => (int) $child->id,
                'title'    => (string) $child->title,
                'slug'     => (string) $child->slug,
                'postType' => (string) $child->post_type,
            ])->toArray() : [],
            'categoryVacancies' => $this->relationLoaded('categoryVacancies') || $this->categoryVacancies ? $this->categoryVacancies->map(fn($cv) => [
                'id'            => (int) $cv->id,
                'categoryName'  => (string) $cv->category_name,
                'vacancyCount'  => (int) $cv->vacancy_count,
                'type'          => (string) $cv->type,
            ])->toArray() : [],
        ];
    }
}
