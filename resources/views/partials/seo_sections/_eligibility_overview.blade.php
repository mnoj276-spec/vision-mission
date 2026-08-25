<section class="details-section">
            <h4 data-i18n="overview_title">Recruitment Overview & Requirements</h4>
            @if($aiContent && !empty($aiContent->eligibility))
                <div style="margin-bottom: 1.5rem;">
                    <h5 style="font-family: 'Outfit'; font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem;" data-i18n="eligibility_title">Detailed Eligibility Criteria</h5>
                    <div style="font-size: 0.95rem; color: var(--text-secondary); line-height: 1.7;">
                        {!! \Illuminate\Support\Str::markdown($aiContent->eligibility) !!}
                    </div>
                </div>
            @endif
            
            <h5 style="font-family: 'Outfit'; font-size: 1.05rem; font-weight: 700; color: var(--text-primary); margin-bottom: 0.5rem; margin-top: 1rem;" data-i18n="original_overview_title">Original Announcement Overview</h5>
            <div class="original-overview-content">{!! $job->description !!}</div>
        </section>