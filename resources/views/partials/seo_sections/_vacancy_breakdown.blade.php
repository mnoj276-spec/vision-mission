<div class='vacancy-section-wrapper'>
<section class="details-section">
            <h4 style="font-family:'Outfit'; color:var(--accent-color); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                <i class="fa-solid fa-chart-pie"></i> Category Wise Vacancy Details
            </h4>
            @if($job->categoryWiseVacancies && $job->categoryWiseVacancies->count() > 0)
                <div class="vacancy-table-wrapper" style="overflow-x: auto; background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); border-radius: 12px; padding: 1rem; margin-bottom: 1.5rem;">
                    <table class="vacancy-detail-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary);">Post Name</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">UR</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">EWS</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">EBC</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">BC</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">BC (F)</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">SC</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right;">ST</th>
                                <th style="padding: 0.75rem 1rem; font-weight: 700; color: var(--text-primary); text-align: right; background: rgba(37, 99, 235, 0.05);">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($job->categoryWiseVacancies as $cwv)
                                <tr>
                                    <td style="padding: 0.75rem 1rem; color: var(--text-primary); font-weight: 600;">{{ $cwv->post_name }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->ur }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->ews }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->ebc }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->bc }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->bc_female }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->sc }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; color: var(--text-secondary);">{{ $cwv->st }}</td>
                                    <td style="padding: 0.75rem 1rem; text-align: right; font-weight: 700; color: var(--accent-color); background: rgba(37, 99, 235, 0.05);">{{ $cwv->total }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div style="background: rgba(255,255,255,0.01); border: 1px dashed var(--border-color); border-radius: 12px; padding: 2rem; text-align: center; color: var(--text-secondary);">
                    <i class="fa-solid fa-folder-open" style="font-size: 2rem; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                    <p style="margin: 0; font-size: 0.9rem;">No category wise vacancy details specified for this post.</p>
                </div>
            @endif
        </section>
<!-- Vacancy Distribution Breakdown (Tabular Matrix redesign) -->
        @if($job->categoryVacancies && $job->categoryVacancies->count() > 0)
            <section class="details-section">
                <h4 style="font-family:'Outfit'; color:var(--accent-color); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;" data-i18n="vacancy_breakdown_title">
                    Vacancy Distribution Breakdown
                </h4>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                    @foreach(['post', 'caste_category', 'department', 'trade', 'discipline'] as $type)
                        @php
                            $items = $job->categoryVacancies->where('type', $type);
                        @endphp
                        @if($items->count() > 0)
                            @php
                                $groupTitle = '';
                                if ($type === 'post') $groupTitle = 'Trade-wise / Post-wise Posts';
                                elseif ($type === 'caste_category') $groupTitle = 'Category-wise Posts';
                                elseif ($type === 'department') $groupTitle = 'Department-wise Posts';
                                elseif ($type === 'trade') $groupTitle = 'Trade-wise Posts';
                                elseif ($type === 'discipline') $groupTitle = 'Discipline-wise Posts';
                            @endphp
                            <div style="background: rgba(255,255,255,0.01); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.25rem;">
                                <h5 style="font-size:0.85rem; text-transform:uppercase; color:var(--text-secondary); font-weight:700; margin-bottom:0.75rem; border-bottom: 1px solid var(--border-color); padding-bottom:0.4rem; font-family:'Outfit';">
                                    <span data-translate-lookup="{{ $groupTitle }}">{{ $groupTitle }}</span>
                                </h5>
                                <div class="vacancy-type-table-wrapper">
                                    <table class="vacancy-detail-table">
                                        <thead>
                                            <tr>
                                                <th>Post / Category</th>
                                                <th style="text-align: right;">Vacancies</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($items as $cv)
                                                <tr>
                                                    <td><span data-translate-lookup="{{ $cv->category_name }}">{{ $cv->category_name }}</span></td>
                                                    <td style="text-align: right;">
                                                        <span class="badge badge-vacancy-number">
                                                            {{ $cv->vacancy_count }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </section>
        @endif

        <!-- Physical Eligibility Matrix (Dynamic display for police/military notifications) -->
        @if($isPhysicalJob)
            <section class="details-section">
                <h4 style="font-family:'Outfit'; color:var(--accent-color); margin-bottom:1rem; display:flex; align-items:center; gap:0.5rem;">
                    <i class="fa-solid fa-person-running"></i> Physical Standards & Eligibility
                </h4>
                <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:1rem;">
                    Indicative physical criteria matching standard police and defense recruitment commissions:
                </p>
                <div class="physical-table-container">
                    <table class="physical-table" style="width: 100%;">
                        <thead>
                            <tr>
                                <th>Physical Test Element</th>
                                <th>Male (General/OBC/BC)</th>
                                <th>Male (SC/ST)</th>
                                <th>Female (All Category)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Height</strong></td>
                                <td>170 Cms</td>
                                <td>162.5 Cms</td>
                                <td>157 Cms</td>
                            </tr>
                            <tr>
                                <td><strong>Chest</strong></td>
                                <td>80 - 85 Cms</td>
                                <td>76 - 81 Cms</td>
                                <td>N/A</td>
                            </tr>
                            <tr>
                                <td><strong>Running</strong></td>
                                <td>1.6 Km in 6 Minutes</td>
                                <td>1.6 Km in 6 Minutes</td>
                                <td>800 Meters in 4 Minutes</td>
                            </tr>
                            <tr>
                                <td><strong>Long Jump</strong></td>
                                <td>12 Feet</td>
                                <td>12 Feet</td>
                                <td>9 Feet</td>
                            </tr>
                            <tr>
                                <td><strong>High Jump</strong></td>
                                <td>3 Feet 9 Inches</td>
                                <td>3 Feet 9 Inches</td>
                                <td>3 Feet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="font-size: 0.8rem; color: var(--text-secondary); line-height: 1.5; border-top: 1px solid var(--border-color); padding-top: 0.5rem;">
                    * <em>Physical standards vary by board guidelines. Always check the official PDF notification below for final validated requirements.</em>
                </div>
            </section>
        @endif

        <!-- Description -->
</div>