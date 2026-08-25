<!-- Left: Important Dates -->
            <div class="split-info-column">
                <h5 class="column-title"><i class="fa-regular fa-calendar-days"></i> <span data-i18n="important_dates">Important Dates</span></h5>
                <ul class="info-list">
                    @if(in_array($job->post_type, ['job', 'admission', 'scholarship']))
                        <li>
                            <span class="info-label" data-i18n="app_begin">Application Begin:</span>
                            <span class="info-val">
                                @if($job->start_date)
                                    {{ $job->start_date->format('d/m/Y') }}
                                @elseif($job->published_at)
                                    {{ $job->published_at->format('d/m/Y') }}
                                @else
                                    <span data-translate-lookup="Refer Notification">Refer Notification</span>
                                @endif
                            </span>
                        </li>
                        <li>
                            <span class="info-label" data-i18n="last_date_apply">Last Date to Apply:</span>
                            <span class="info-val deadline-text">
                                @if($job->last_date_to_apply)
                                    {{ $job->last_date_to_apply->format('d/m/Y') }}
                                @else
                                    <span data-translate-lookup="Announced Soon">Announced Soon</span>
                                @endif
                            </span>
                        </li>
                        <li>
                            <span class="info-label" data-i18n="fee_last_date">Online Fee Last Date:</span>
                            <span class="info-val">
                                @if($job->last_date_to_apply)
                                    {{ $job->last_date_to_apply->format('d/m/Y') }}
                                @else
                                    <span data-translate-lookup="Announced Soon">Announced Soon</span>
                                @endif
                            </span>
                        </li>
                    @endif

                    @if(in_array($job->post_type, ['admit_card', 'result', 'answer_key', 'syllabus']))
                        <li>
                            <span class="info-label" data-i18n="release_date_lbl">Release Date:</span>
                            <span class="info-val">
                                {{ $job->published_at ? $job->published_at->format('d/m/Y') : 'Announced Soon' }}
                            </span>
                        </li>
                    @endif

                    @if($job->post_type === 'notice')
                        <li>
                            <span class="info-label" data-i18n="issue_date_lbl">Issue Date:</span>
                            <span class="info-val">
                                {{ $job->published_at ? $job->published_at->format('d/m/Y') : 'Available Now' }}
                            </span>
                        </li>
                    @endif

                    @if($job->exam_date && !in_array($job->post_type, ['notice', 'result']))
                        <li>
                            <span class="info-label" data-i18n="exam_date_lbl">Exam Date:</span>
                            <span class="info-val exam-text">
                                {{ $job->exam_date->format('d/m/Y') }}
                            </span>
                        </li>
                    @elseif(in_array($job->post_type, ['job', 'admit_card', 'syllabus']))
                        <li>
                            <span class="info-label" data-i18n="exam_date_lbl">Exam Date:</span>
                            <span class="info-val exam-text">
                                <span data-translate-lookup="Announced Soon">Announced Soon</span>
                            </span>
                        </li>
                    @endif
                    
                    @if($job->result_date && in_array($job->post_type, ['job', 'result']))
                        <li>
                            <span class="info-label" data-i18n="result_date_lbl">Result Date:</span>
                            <span class="info-val result-text">{{ $job->result_date->format('d/m/Y') }}</span>
                        </li>
                    @endif
                </ul>
            </div>