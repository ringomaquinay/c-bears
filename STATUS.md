# C-BEARS Status

## Last Updated

2026-09-28

## Current Phase

Assessment Filament Level 1 Score Summary and Calculate Action

## Last Completed Task

Assessment Filament read-only FEMA Level 1 score summary and explicit Calculate / Refresh action implemented.

## Completed Items

- Laravel + Filament + PostgreSQL environment is operational.
- Building Registry model, migration, generated building code, Filament CRUD, validation, and UI are implemented.
- Assessment master record model, migration, generated assessment number, Filament CRUD, building relationship, assessor relationship, FEMA version selection, and statuses are implemented.
- FEMA reference data tables, models, and seeders are implemented for:
  - FEMA versions
  - FEMA building types
  - FEMA basic scores
  - FEMA minimum scores / S_MIN
  - FEMA Level 1 score modifiers
- Historical building snapshot table, model, relationship, automatic creation, and feature test are implemented.
- Assessment Structural Detail data layer is implemented with:
  - `assessment_structural_details` table
  - `AssessmentStructuralDetail` model
  - Assessment `structuralDetail()` relationship
  - FEMA Building Type relationship
  - FEMA version and building type descriptive snapshot synchronization
  - nullable draft structural/FEMA input fields
  - feature tests for relationships, constraints, nullable values, cascade/restrict behavior, snapshots, and existing building snapshot behavior
- Assessment Structural Detail Filament UI is implemented inside the Assessment Resource with:
  - FEMA Classification section
  - Site / Soil section
  - Irregularities section
  - Code Conditions section with nullable boolean tri-state controls
  - Structural Notes section
  - read-only structural reference snapshot display in form and view pages
  - relationship-based `structuralDetail()` persistence
  - feature tests for create/edit/loading/null boolean/snapshot behavior
- FEMA Basic Score and Level 1 Modifier lookup integration is implemented with:
  - dedicated `FemaLevelOneScoreLookup` service
  - Basic Score lookup by FEMA version, FEMA building type, and seismicity level
  - Minimum Score / S_MIN lookup by FEMA version, FEMA building type, and seismicity level
  - Level 1 modifier lookup from structural detail inputs
  - soil modifier mapping using historical assessment building snapshot storeys
  - incomplete Draft assessment handling without unnecessary runtime exceptions
  - missing and ambiguous reference row detection
  - focused feature tests for lookup behavior and existing behavior preservation
- Persisted FEMA Level 1 scoring snapshots and calculation trace are implemented with:
  - Level 1 scoring snapshot columns on `assessment_structural_details`
  - dedicated `FemaLevelOneScoreSnapshotter` service
  - Basic Score and Minimum Score reference IDs plus numeric snapshots
  - Level 1 modifier total snapshot
  - calculated Level 1 score before minimum-floor handling
  - final Level 1 score after minimum-score floor handling
  - applied modifier snapshot JSON
  - readable calculation trace JSON
  - `level_one_calculated_at` timestamp
  - focused tests for persistence, historical stability, minimum-floor handling, and incomplete lookup handling
- Assessment Filament Level 1 Score Summary UI is implemented with:
  - read-only summary section in Assessment edit and view experiences
  - persisted Basic Score, modifier total, calculated score, minimum score, final score, and last calculated timestamp display
  - applied modifier display from persisted snapshots only
  - compact calculation details from persisted trace
  - explicit `Calculate Level 1 Score` / `Refresh Level 1 Score` page action
  - warning notifications for missing inputs or lookup errors
  - no automatic recalculation on save
  - focused Filament tests for rendering, action behavior, recalculation, errors, and read-only score fields
- Current full test suite result: pending final run for this task.

## Current Known Issues

- Existing assessments were not backfilled with building snapshots.
- Building snapshot data is not displayed in the Assessment Filament Resource yet.
- Level 2 modifier lookup/application is not implemented yet.
- Final scoring workflow/status completion validation is not implemented yet.
- Report generation is not implemented yet.
- Complex stale-score change tracking is not implemented; users can explicitly refresh the Level 1 score after input changes.
- Findings, photos, recommendations, reports, roles/permissions, audit trail, dashboard, and GIS are not implemented yet.
- Some source-of-truth docs still have stale historical "current phase" sections compared with the latest codebase state.

## NEXT STEP

Level 2 modifier lookup/application design.

## Steps After That

1. Final scoring workflow and completion validation.
2. Screening recommendation logic.
3. Findings, photos/documents, and recommendations.
4. Review/approval workflow.
5. Reports/PDF.
6. Roles/permissions, audit trail, dashboard, and GIS/map.

## GitHub Repository

https://github.com/ringomaquinay/c-bears.git

## Main Documentation Files

- `DOCUMENTATION.md`
- `docs/AI-DEVELOPMENT-PROTOCOL.md`
- `docs/FRAMEWORK.md`
- `docs/SRS.md`
- `docs/data/FEMA_P154_Level1_Modifiers_Verified.csv`

## Maintenance Note

After every future completed development task, update this `STATUS.md` file together with `DOCUMENTATION.md`.
