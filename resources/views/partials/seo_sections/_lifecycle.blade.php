<!-- Recruitment Update Timeline & Lifecycle -->
        @if($timeline && $timeline->count() > 1)
            <section class="details-section" style="border-top:none; padding-top:0;">
                <h4 style="font-family:'Outfit'; color:var(--accent-color); margin-bottom:0.75rem;"><i class="fa-solid fa-code-fork"></i> <span data-i18n="lifecycle_title">Recruitment Lifecycle & Update Timeline</span></h4>
                <div class="timeline-container">
                    @foreach($timeline as $item)
                        @php
                            $isCurrent = ($item->id === $job->id);
                            $itemType = match($item->post_type) {
                                'job' => 'Original Announcement',
                                'admit_card' => 'Admit Card Available',
                                'result' => 'Final Exam Results',
                                'answer_key' => 'Answer Key Objections',
                                'syllabus' => 'Exam Syllabus Published',
                                'cutoff' => 'Declared Cutoffs',
                                'notice' => 'Official Notice / Corrigendum',
                                default => ucfirst($item->post_type)
                            };
                        @endphp
                        <div class="timeline-node {{ $isCurrent ? 'current-node' : '' }}">
                            <div class="node-icon">
                                @if($item->post_type === 'job') <i class="fa-solid fa-bullhorn"></i>
                                @elseif($item->post_type === 'admit_card') <i class="fa-solid fa-id-card"></i>
                                @elseif($item->post_type === 'result') <i class="fa-solid fa-trophy"></i>
                                @elseif($item->post_type === 'answer_key') <i class="fa-solid fa-key"></i>
                                @elseif($item->post_type === 'syllabus') <i class="fa-solid fa-book-open"></i>
                                @else <i class="fa-solid fa-circle-info"></i>
                                @endif
                            </div>
                            <div class="node-content">
                                <span class="node-date">{{ $item->published_at ? $item->published_at->format('d M Y') : $item->created_at->format('d M Y') }}</span>
                                <h5 class="node-title">
                                    @if($isCurrent)
                                        <strong><span data-translate-lookup="{{ $itemType }}">{{ $itemType }}</span>: {{ $item->title }} <span data-translate-lookup="(This Page)">(This Page)</span></strong>
                                    @else
                                        <a href="{{ route('seo.job_detail', ['slug' => $item->slug]) }}"><span data-translate-lookup="{{ $itemType }}">{{ $itemType }}</span>: {{ $item->title }}</a>
                                    @endif
                                </h5>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Vacancy Details Card -->