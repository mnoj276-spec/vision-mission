<!-- Right: Application Fees -->
            <div class="split-info-column">
                <h5 class="column-title"><i class="fa-solid fa-indian-rupee-sign"></i> <span data-i18n="app_fee_lbl">Application Fee</span></h5>
                <ul class="info-list">
                    @if($job->application_fee > 0)
                        <li>
                            <span class="info-label" data-i18n="fee_gen">General / OBC / EWS:</span>
                            <span class="info-val">₹ {{ number_format($job->application_fee, 2) }}</span>
                        </li>
                        <li>
                            <span class="info-label" data-i18n="fee_sc">SC / ST / PH:</span>
                            <span class="info-val">₹ 0.00 <span data-translate-prefix="(" data-translate-suffix=")" data-translate-lookup="Exempted">(Exempted)</span></span>
                        </li>
                        <li>
                            <span class="info-label" data-i18n="fee_female">Females (All Category):</span>
                            <span class="info-val">₹ 0.00 <span data-translate-prefix="(" data-translate-suffix=")" data-translate-lookup="Exempted">(Exempted)</span></span>
                        </li>
                    @else
                        <li>
                            <span class="info-label" data-i18n="fee_all">All Category Candidates:</span>
                            <span class="info-val text-success" data-translate-lookup="Free (No Fee)">Free (No Fee)</span>
                        </li>
                    @endif
                    <li class="fee-note">
                        <span class="info-label" data-i18n="pay_mode_lbl">Payment Mode:</span>
                        <span class="info-val" data-i18n="pay_mode_desc">Pay the examination fee through Debit Card, Credit Card, Net Banking, or UPI mode only.</span>
                    </li>
                </ul>
            </div>