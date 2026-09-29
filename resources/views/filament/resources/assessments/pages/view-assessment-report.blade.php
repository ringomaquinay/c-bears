@php
    use App\Filament\Resources\Assessments\AssessmentResource;

    $assessment = $this->getReportRecord();
    $buildingSnapshot = $assessment->buildingSnapshot;
    $structuralDetail = $assessment->structuralDetail;
    $isCompleted = $assessment->status === 'Completed';
    $modifiers = $this->appliedModifiers($assessment);
    $traceRows = $this->traceRows($assessment);
@endphp

<x-filament-panels::page>
    <style>
        .cbears-report {
            background: #ffffff;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.35;
            padding: 28px;
            border: 1px solid #d1d5db;
        }

        .cbears-report__header {
            display: flex;
            justify-content: space-between;
            gap: 24px;
            border-bottom: 3px solid #374151;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }

        .cbears-report__title {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
            letter-spacing: 0;
        }

        .cbears-report__subtitle {
            margin: 4px 0 0;
            color: #4b5563;
            font-size: 13px;
        }

        .cbears-report__number {
            text-align: right;
            font-size: 13px;
        }

        .cbears-report__number strong {
            display: block;
            font-size: 18px;
            color: #111827;
        }

        .cbears-report__draft {
            border: 2px solid #92400e;
            color: #92400e;
            font-weight: 700;
            padding: 10px 12px;
            margin-bottom: 18px;
            background: #fffbeb;
        }

        .cbears-report__section {
            margin-top: 20px;
            break-inside: avoid;
        }

        .cbears-report__section h2 {
            font-size: 15px;
            margin: 0 0 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #9ca3af;
            color: #111827;
        }

        .cbears-report__grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 18px;
        }

        .cbears-report__field {
            display: grid;
            grid-template-columns: 180px minmax(0, 1fr);
            gap: 10px;
            font-size: 13px;
        }

        .cbears-report__label {
            color: #4b5563;
            font-weight: 700;
        }

        .cbears-report__value {
            color: #111827;
            overflow-wrap: anywhere;
        }

        .cbears-report__note {
            color: #4b5563;
            font-size: 12px;
            margin: 0 0 10px;
        }

        .cbears-report__table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .cbears-report__table th,
        .cbears-report__table td {
            border: 1px solid #d1d5db;
            padding: 7px 8px;
            vertical-align: top;
        }

        .cbears-report__table th {
            background: #f3f4f6;
            color: #111827;
            text-align: left;
        }

        .cbears-report__score {
            max-width: 720px;
            border: 1px solid #9ca3af;
        }

        .cbears-report__score-row {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 130px;
            gap: 16px;
            padding: 7px 10px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 13px;
        }

        .cbears-report__score-row:last-child {
            border-bottom: 0;
        }

        .cbears-report__score-row--total {
            font-weight: 700;
            background: #f9fafb;
        }

        .cbears-report__score-value {
            text-align: right;
            font-variant-numeric: tabular-nums;
        }

        .cbears-report__recommendation {
            border: 1px solid #9ca3af;
            padding: 12px;
            background: #f9fafb;
        }

        .cbears-report__recommendation-title {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .cbears-report__footer {
            margin-top: 26px;
            border-top: 1px solid #d1d5db;
            padding-top: 10px;
            font-size: 11px;
            color: #6b7280;
        }

        @media print {
            body {
                background: #ffffff !important;
            }

            .fi-sidebar,
            .fi-topbar,
            .fi-header-actions,
            .fi-breadcrumbs,
            nav,
            aside {
                display: none !important;
            }

            .fi-main,
            .fi-page,
            .fi-page-content {
                padding: 0 !important;
                margin: 0 !important;
                max-width: none !important;
            }

            .cbears-report {
                border: 0;
                padding: 0;
            }
        }
    </style>

    <article class="cbears-report">
        <header class="cbears-report__header">
            <div>
                <h1 class="cbears-report__title">C-BEARS FEMA P-154 Level 1 Assessment Report</h1>
                <p class="cbears-report__subtitle">Printable assessment-time summary for Rapid Visual Screening records.</p>
            </div>
            <div class="cbears-report__number">
                Assessment Number
                <strong>{{ $this->display($assessment->assessment_number) }}</strong>
                Status: {{ $this->display($assessment->status) }}
            </div>
        </header>

        @unless ($isCompleted)
            <div class="cbears-report__draft">DRAFT - This report preview is not finalized.</div>
        @endunless

        <section class="cbears-report__section">
            <h2>Assessment Information</h2>
            <div class="cbears-report__grid">
                <div class="cbears-report__field"><span class="cbears-report__label">Assessment Number</span><span class="cbears-report__value">{{ $this->display($assessment->assessment_number) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Assessment Date</span><span class="cbears-report__value">{{ $this->displayDate($assessment->assessment_date) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Assessment Level</span><span class="cbears-report__value">{{ $this->display($assessment->assessment_level) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Status</span><span class="cbears-report__value">{{ $this->display($assessment->status) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Assessor</span><span class="cbears-report__value">{{ $this->display($assessment->assessor?->name) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">FEMA Version</span><span class="cbears-report__value">{{ $this->display($structuralDetail?->fema_version_code_snapshot) }} {{ $this->display($structuralDetail?->fema_version_edition_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Completion Date</span><span class="cbears-report__value">{{ $this->display($assessment->completed_at) }}</span></div>
            </div>
        </section>

        <section class="cbears-report__section">
            <h2>Building Information</h2>
            <p class="cbears-report__note">Assessment-time building details from the historical Building Snapshot.</p>
            <div class="cbears-report__grid">
                <div class="cbears-report__field"><span class="cbears-report__label">Building Code</span><span class="cbears-report__value">{{ $this->display($buildingSnapshot?->building_code_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Building Name</span><span class="cbears-report__value">{{ $this->display($buildingSnapshot?->building_name_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Owner / Office</span><span class="cbears-report__value">{{ $this->display($buildingSnapshot?->owner_or_responsible_office_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Barangay</span><span class="cbears-report__value">{{ $this->display($buildingSnapshot?->barangay_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Address</span><span class="cbears-report__value">{{ $this->display($buildingSnapshot?->address_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Occupancy</span><span class="cbears-report__value">{{ $this->display($buildingSnapshot?->primary_occupancy_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Number of Storeys</span><span class="cbears-report__value">{{ $this->display($buildingSnapshot?->number_of_storeys_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Year Built</span><span class="cbears-report__value">{{ $this->display($buildingSnapshot?->year_built_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Floor Area</span><span class="cbears-report__value">{{ $this->display($buildingSnapshot?->approximate_floor_area_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Permit Reference</span><span class="cbears-report__value">{{ $this->display($buildingSnapshot?->building_permit_number_snapshot) }}</span></div>
            </div>
        </section>

        <section class="cbears-report__section">
            <h2>FEMA Structural Classification</h2>
            <div class="cbears-report__grid">
                <div class="cbears-report__field"><span class="cbears-report__label">FEMA Building Type</span><span class="cbears-report__value">{{ $this->display($structuralDetail?->fema_building_type_name_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Building Type Code</span><span class="cbears-report__value">{{ $this->display($structuralDetail?->fema_building_type_code_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Material Category</span><span class="cbears-report__value">{{ $this->display($structuralDetail?->material_category_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Structural System</span><span class="cbears-report__value">{{ $this->display($structuralDetail?->structural_system_snapshot) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Seismicity Level</span><span class="cbears-report__value">{{ $this->display($structuralDetail?->seismicity_level) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Soil Type</span><span class="cbears-report__value">{{ $this->optionLabel($structuralDetail?->soil_type, AssessmentResource::soilTypeOptions()) }}</span></div>
            </div>
        </section>

        <section class="cbears-report__section">
            <h2>Level 1 Observations</h2>
            <div class="cbears-report__grid">
                <div class="cbears-report__field"><span class="cbears-report__label">Vertical Irregularity</span><span class="cbears-report__value">{{ $this->optionLabel($structuralDetail?->vertical_irregularity_type, AssessmentResource::verticalIrregularityOptions()) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Plan Irregularity</span><span class="cbears-report__value">{{ $this->optionLabel($structuralDetail?->plan_irregularity_type, AssessmentResource::planIrregularityOptions()) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Pre-Code Condition</span><span class="cbears-report__value">{{ $this->booleanLabel($structuralDetail?->has_pre_code_condition) }}</span></div>
                <div class="cbears-report__field"><span class="cbears-report__label">Post-Benchmark Condition</span><span class="cbears-report__value">{{ $this->booleanLabel($structuralDetail?->has_post_benchmark_condition) }}</span></div>
            </div>
            <table class="cbears-report__table" style="margin-top: 10px;">
                <tbody>
                    <tr><th>Site Condition Notes</th><td>{{ $this->display($structuralDetail?->site_condition_notes) }}</td></tr>
                    <tr><th>Structural Observation Notes</th><td>{{ $this->display($structuralDetail?->structural_observation_notes) }}</td></tr>
                </tbody>
            </table>
        </section>

        <section class="cbears-report__section">
            <h2>FEMA Level 1 Score</h2>
            <div class="cbears-report__score">
                <div class="cbears-report__score-row"><span>Basic Score</span><span class="cbears-report__score-value">{{ $this->score($structuralDetail?->basic_score_snapshot) }}</span></div>
                @forelse ($modifiers as $modifier)
                    <div class="cbears-report__score-row"><span>{{ $this->display(($modifier['code'] ?? null) . ' - ' . ($modifier['name'] ?? null)) }}</span><span class="cbears-report__score-value">{{ $this->signedScore($modifier['value'] ?? null) }}</span></div>
                @empty
                    <div class="cbears-report__score-row"><span>Applied Modifiers</span><span class="cbears-report__score-value">Not recorded</span></div>
                @endforelse
                <div class="cbears-report__score-row cbears-report__score-row--total"><span>Modifier Total</span><span class="cbears-report__score-value">{{ $this->signedScore($structuralDetail?->level_one_modifier_total_snapshot) }}</span></div>
                <div class="cbears-report__score-row"><span>Calculated Level 1 Score</span><span class="cbears-report__score-value">{{ $this->score($structuralDetail?->calculated_level_one_score) }}</span></div>
                <div class="cbears-report__score-row"><span>Minimum Score</span><span class="cbears-report__score-value">{{ $this->score($structuralDetail?->minimum_score_snapshot) }}</span></div>
                <div class="cbears-report__score-row cbears-report__score-row--total"><span>Final Level 1 Score</span><span class="cbears-report__score-value">{{ $this->score($structuralDetail?->final_level_one_score) }}</span></div>
                <div class="cbears-report__score-row"><span>Calculated Timestamp</span><span class="cbears-report__score-value">{{ $this->display($structuralDetail?->level_one_calculated_at) }}</span></div>
            </div>
        </section>

        <section class="cbears-report__section">
            <h2>Screening Recommendation</h2>
            <div class="cbears-report__recommendation">
                <div class="cbears-report__recommendation-title">{{ $this->display($structuralDetail?->level_one_recommendation_label) }}</div>
                <div class="cbears-report__grid">
                    <div class="cbears-report__field"><span class="cbears-report__label">Final Level 1 Score</span><span class="cbears-report__value">{{ $this->score($structuralDetail?->final_level_one_score) }}</span></div>
                    <div class="cbears-report__field"><span class="cbears-report__label">Screening Cutoff</span><span class="cbears-report__value">{{ $this->score($structuralDetail?->level_one_screening_cutoff_snapshot) }}</span></div>
                    <div class="cbears-report__field"><span class="cbears-report__label">Generated At</span><span class="cbears-report__value">{{ $this->display($structuralDetail?->level_one_recommendation_generated_at) }}</span></div>
                </div>
                <p style="margin: 10px 0 0;">{{ $this->display($structuralDetail?->level_one_recommendation_explanation) }}</p>
            </div>
        </section>

        <section class="cbears-report__section">
            <h2>Calculation Details</h2>
            <table class="cbears-report__table">
                <tbody>
                    @forelse ($traceRows as $label => $value)
                        <tr><th>{{ $label }}</th><td>{{ $this->display($value) }}</td></tr>
                    @empty
                        <tr><td>Calculation trace not recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <footer class="cbears-report__footer">
            This report summarizes FEMA P-154 Level 1 screening data stored for this assessment. It is based on assessment-time snapshots and persisted scoring outputs where available.
        </footer>
    </article>
</x-filament-panels::page>