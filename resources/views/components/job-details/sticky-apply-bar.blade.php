@props(['job'])

@php
    $isOpen = true;
    $statusText = 'Apply Open';
    $statusClass = 'status-open';

    if ($job->post_type === 'job') {
        if ($job->last_date_to_apply && $job->last_date_to_apply->isPast()) {
            $isOpen = false;
            $statusText = 'Closed';
            $statusClass = 'status-closed';
        } elseif ($job->start_date && $job->start_date->isFuture()) {
            $isOpen = false;
            $statusText = 'Upcoming';
            $statusClass = 'status-upcoming';
        }
    } else {
        $statusText = match($job->post_type) {
            'result' => 'Result Out',
            'admit_card' => 'Admit Card Out',
            'answer_key' => 'Answer Key Out',
            'syllabus' => 'Syllabus Out',
            default => 'Active'
        };
        $statusClass = 'status-open';
    }

    $pdfUrl = $job->notification_pdf_path 
        ? (str_starts_with($job->notification_pdf_path, 'http') ? $job->notification_pdf_path : Storage::url($job->notification_pdf_path))
        : null;

    $applyUrl = $job->apply_link ?? $job->official_website_link;
@endphp

<div class="mobile-sticky-bar" id="mobileStickyBar">
    <div class="sticky-info">
        <span class="sticky-title">{{ Str::limit($job->title, 40) }}</span>
        <span class="sticky-status {{ $statusClass }}">{{ $statusText }}</span>
    </div>
    <div class="sticky-actions">
        @if($pdfUrl)
            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="sticky-btn btn-pdf" title="Download PDF">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </a>
        @endif
        @if($isOpen && $applyUrl)
            <a href="{{ $applyUrl }}" target="_blank" rel="nofollow noopener" class="sticky-btn btn-apply btn-pulse">
                <i class="fa-solid fa-pen-to-square"></i> Apply
            </a>
        @elseif($job->post_type === 'result' && $applyUrl)
            <a href="{{ $applyUrl }}" target="_blank" rel="nofollow noopener" class="sticky-btn btn-apply" style="background:#8b5cf6;">
                <i class="fa-solid fa-trophy"></i> Result
            </a>
        @elseif($job->post_type === 'admit_card' && $applyUrl)
            <a href="{{ $applyUrl }}" target="_blank" rel="nofollow noopener" class="sticky-btn btn-apply" style="background:#3b82f6;">
                <i class="fa-solid fa-id-card"></i> Admit Card
            </a>
        @endif
    </div>
</div>

<style>
    .mobile-sticky-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--bg-secondary);
        border-top: 1px solid var(--border-color);
        box-shadow: 0 -8px 24px rgba(0, 0, 0, 0.15);
        padding: 0.75rem 1.25rem;
        display: none;
        align-items: center;
        justify-content: space-between;
        z-index: 999;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        transition: transform 0.3s ease;
    }

    @media (max-width: 768px) {
        .mobile-sticky-bar {
            display: flex;
        }
        /* Offset main page container to prevent content overlap */
        body {
            padding-bottom: 70px !important;
        }
    }

    .sticky-info {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
        text-align: left;
        max-width: 60%;
    }

    .sticky-title {
        font-family: 'Outfit', sans-serif;
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-ellipsis: strip;
        text-overflow: ellipsis;
    }

    .sticky-status {
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        align-self: flex-start;
        padding: 1px 6px;
        border-radius: 4px;
    }

    .sticky-actions {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }

    .sticky-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.5rem 0.9rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none !important;
        color: #fff !important;
        transition: all 0.2s ease;
    }

    .sticky-btn:hover {
        transform: translateY(-1px);
    }

    .sticky-btn.btn-pdf {
        background: #dc2626;
        box-shadow: 0 4px 10px rgba(220, 38, 38, 0.2);
    }

    .sticky-btn.btn-apply {
        background: #10b981;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
    }
</style>
