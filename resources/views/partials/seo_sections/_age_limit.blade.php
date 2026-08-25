<!-- Age Limit Card -->
        <div class="age-limit-card">
            <h5 class="column-title" style="border-bottom:none; margin-bottom:0; padding-bottom:0;"><i class="fa-regular fa-clock"></i> <span data-i18n="age_limit_title">Age Limit Details</span></h5>
            <div class="age-grid">
                <div class="age-box">
                    <span class="age-label" data-i18n="age_min_lbl">Minimum Age</span>
                    <span class="age-val">{{ $job->age_min ?? '18' }} <span data-translate-lookup="Years">Years</span></span>
                </div>
                <div class="age-box">
                    <span class="age-label" data-i18n="age_max_lbl">Maximum Age</span>
                    <span class="age-val">{{ $job->age_max ?? ($job->age_limit ?? '32') }} <span data-translate-lookup="Years">Years</span></span>
                </div>
            </div>
            <div class="age-cutoff-info">
                <strong data-i18n="age_ref_lbl">Age Limit Reference:</strong> <span data-i18n="age_relaxation_desc">Calculated based on the board's recruitment guidelines. Age relaxation is applicable extra as per government reservation rules.</span>
            </div>
        </div>