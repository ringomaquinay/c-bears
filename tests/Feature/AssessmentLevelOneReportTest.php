<?php

namespace Tests\Feature;

use App\Filament\Resources\Assessments\Pages\ViewAssessment;
use App\Filament\Resources\Assessments\Pages\ViewAssessmentReport;
use App\Models\Assessment;
use App\Models\AssessmentStructuralDetail;
use App\Models\Building;
use App\Models\FemaBasicScore;
use App\Models\FemaBuildingType;
use App\Models\FemaVersion;
use App\Models\User;
use App\Services\Fema\FemaLevelOneScoreSnapshotter;
use Database\Seeders\FemaBasicScoreSeeder;
use Database\Seeders\FemaBuildingTypeSeeder;
use Database\Seeders\FemaMinimumScoreSeeder;
use Database\Seeders\FemaScoreModifierSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AssessmentLevelOneReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_completed_level_one_assessment_can_open_report_with_required_sections(): void
    {
        [$assessment] = $this->createCompletedReportAssessment();

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessmentReport::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('C-BEARS FEMA P-154 Level 1 Assessment Report')
            ->assertSee('Assessment Information')
            ->assertSee($assessment->assessment_number)
            ->assertSee('Building Information')
            ->assertSee('Assessment-time building details')
            ->assertSee('FEMA Structural Classification')
            ->assertSee('Level 1 Observations')
            ->assertSee('FEMA Level 1 Score')
            ->assertSee('Screening Recommendation')
            ->assertSee('Calculation Details');
    }

    public function test_report_uses_historical_building_snapshot_values(): void
    {
        [$assessment, $building] = $this->createCompletedReportAssessment();

        $building->update([
            'building_name' => 'Changed Live Registry Name',
            'address' => 'Changed Live Registry Address',
            'number_of_storeys' => 42,
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessmentReport::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('Report Snapshot Building')
            ->assertSee('Assessment-Time Address')
            ->assertSee('5')
            ->assertDontSee('Changed Live Registry Name')
            ->assertDontSee('Changed Live Registry Address')
            ->assertDontSee('42');
    }

    public function test_report_displays_structural_fema_snapshot_values(): void
    {
        [$assessment] = $this->createCompletedReportAssessment();

        FemaBuildingType::where('code', 'C1')->update([
            'name' => 'Changed Live FEMA Type Name',
            'material_category' => 'Changed Material',
        ]);

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessmentReport::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('Concrete moment-resisting frame buildings')
            ->assertSee('C1')
            ->assertSee('Concrete')
            ->assertSee('Moment-resisting frame')
            ->assertSee('High')
            ->assertSee('Soil Type E - More Than 3 Stories')
            ->assertDontSee('Changed Live FEMA Type Name')
            ->assertDontSee('Changed Material');
    }

    public function test_report_displays_persisted_score_modifiers_final_score_and_recommendation(): void
    {
        [$assessment] = $this->createCompletedReportAssessment();

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessmentReport::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('Basic Score')
            ->assertSee('1.50')
            ->assertSee('VERTICAL_MODERATE')
            ->assertSee('PLAN_IRREGULARITY')
            ->assertSee('POST_BENCHMARK')
            ->assertSee('SOIL_E_MID_HIGH_RISE')
            ->assertSee('Modifier Total')
            ->assertSee('1.80')
            ->assertSee('Final Level 1 Score')
            ->assertSee('Further Detailed Seismic Evaluation Recommended')
            ->assertSee('Screening Cutoff')
            ->assertSee('2.00');
    }

    public function test_report_does_not_change_after_fema_reference_or_cutoff_config_changes(): void
    {
        [$assessment] = $this->createCompletedReportAssessment();

        FemaBasicScore::query()->update(['basic_score' => '9.99']);
        config(['cbears.fema.level_one_screening_cutoff' => '9.00']);

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessmentReport::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('1.50')
            ->assertSee('1.80')
            ->assertSee('2.00')
            ->assertSee('Further Detailed Seismic Evaluation Recommended')
            ->assertDontSee('9.99')
            ->assertDontSee('9.00');
    }

    public function test_report_is_read_only_and_view_report_action_is_available_on_completed_assessment(): void
    {
        [$assessment] = $this->createCompletedReportAssessment();

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessment::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertActionVisible('viewReport');

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessmentReport::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('Print Report')
            ->assertDontSee('Save')
            ->assertDontSee('Create')
            ->assertDontSee('Delete');
    }

    public function test_draft_report_preview_is_clearly_marked_draft(): void
    {
        [$assessment] = $this->createDraftReportAssessment();

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessmentReport::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertSee('DRAFT - This report preview is not finalized.')
            ->assertSee($assessment->assessment_number);
    }

    public function test_report_uses_neutral_recommendation_language(): void
    {
        [$assessment] = $this->createCompletedReportAssessment();

        Livewire::actingAs(User::factory()->create())
            ->test(ViewAssessmentReport::class, ['record' => $assessment->getKey()])
            ->assertSuccessful()
            ->assertDontSee('Safe')
            ->assertDontSee('Unsafe')
            ->assertDontSee('Passed')
            ->assertDontSee('Failed');
    }

    /**
     * @return array{0: Assessment, 1: Building, 2: AssessmentStructuralDetail}
     */
    private function createCompletedReportAssessment(): array
    {
        Carbon::setTestNow('2026-09-29 16:00:00');

        [$assessment, $building, $structuralDetail] = $this->createDraftReportAssessment();

        app(FemaLevelOneScoreSnapshotter::class)->calculateAndPersist($assessment);
        $assessment->completeLevelOne();

        return [$assessment->fresh(), $building->fresh(), $structuralDetail->fresh()];
    }

    /**
     * @return array{0: Assessment, 1: Building, 2: AssessmentStructuralDetail}
     */
    private function createDraftReportAssessment(): array
    {
        $this->seed([
            FemaBuildingTypeSeeder::class,
            FemaBasicScoreSeeder::class,
            FemaMinimumScoreSeeder::class,
            FemaScoreModifierSeeder::class,
        ]);

        $femaVersion = FemaVersion::where('code', 'P154-3E')->firstOrFail();
        $femaBuildingType = FemaBuildingType::where('fema_version_id', $femaVersion->id)
            ->where('code', 'C1')
            ->firstOrFail();
        $building = Building::create([
            'building_name' => 'Report Snapshot Building',
            'owner_or_responsible_office' => 'Assessment Office',
            'address' => 'Assessment-Time Address',
            'barangay' => 'Barangay Report',
            'primary_occupancy' => 'Office',
            'number_of_storeys' => 5,
            'year_built' => 1998,
            'approximate_floor_area' => '1234.50',
            'building_permit_number' => 'BP-REPORT-001',
            'record_status' => 'active',
        ]);
        $assessment = Assessment::create([
            'building_id' => $building->id,
            'assessment_date' => '2026-09-29',
            'assessment_type' => 'Initial',
            'assessment_level' => 'Level 1',
            'fema_version_id' => $femaVersion->id,
            'status' => 'Draft',
        ]);
        $structuralDetail = AssessmentStructuralDetail::create([
            'assessment_id' => $assessment->id,
            'fema_building_type_id' => $femaBuildingType->id,
            'seismicity_level' => 'High',
            'soil_type' => 'SOIL_E_MID_HIGH_RISE',
            'vertical_irregularity_type' => 'moderate',
            'plan_irregularity_type' => 'irregular',
            'has_pre_code_condition' => false,
            'has_post_benchmark_condition' => true,
            'site_condition_notes' => 'Report site notes.',
            'structural_observation_notes' => 'Report structural notes.',
        ]);

        return [$assessment, $building, $structuralDetail];
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }
}
