# Building Assessment System

## Project Overview

The Building Assessment System is a web-based application for recording buildings, conducting building assessments, storing findings and photos, computing assessment results, and generating reports.

The system will be developed step by step to keep the project simple, stable, and easy to maintain.

---

## Technology Stack

- Laravel
- Filament
- PostgreSQL
- PHP
- Composer
- Node.js / Vite

---

## Current Development Status

### Completed

- PostgreSQL 18 installed
- pgAdmin 4 installed
- Database created: `building_assessment`
- Laravel project created
- Laravel connected to PostgreSQL
- PostgreSQL PHP extensions enabled
- Default Laravel migrations completed
- Filament installed
- Filament Admin Panel installed
- Filament administrator account created
- Filament Dashboard successfully tested


---

## Project Directory

```text
D:\building-assessment
```

---

## Current Phase

```text
Assessment Structural Detail Filament UI
```

---

## Building Registry Implementation Log

### Files Created / Modified

- `app/Models/Building.php`
- `database/migrations/2026_09_11_132410_create_buildings_table.php`
- `app/Filament/Resources/Buildings/BuildingResource.php`
- `app/Filament/Resources/Buildings/Pages/ListBuildings.php`
- `app/Filament/Resources/Buildings/Pages/CreateBuilding.php`
- `app/Filament/Resources/Buildings/Pages/ViewBuilding.php`
- `app/Filament/Resources/Buildings/Pages/EditBuilding.php`
- `DOCUMENTATION.md`

### Implementation Completed

- Created the `Building` model.
- Created the `buildings` table migration using PostgreSQL-compatible column types.
- Added the approved Building Registry fields.
- Enforced unique `building_code` at the database level.
- Added timestamps to the `buildings` table.
- Created the Filament Building Resource.
- Added List, Create, View, and Edit pages for buildings.
- Added searchable table columns for:
  - `building_code`
  - `building_name`
  - `owner_or_responsible_office`
  - `barangay`
- Displayed `record_status` in the Building table.
- Added basic validation for required, unique, numeric, and bounded fields.
- Made `building_code` automatically generated and read-only in the Filament form.
- Implemented model-level building code generation using the format:

```text
CBEARS-YYYY-XXXXXX
```

- Existing building codes remain unchanged during updates.
- User-submitted changes to `building_code` are not allowed through the Filament form.

### Tests / Checks Performed

- `php -l app\Models\Building.php`
- `php -l database\migrations\2026_09_11_132410_create_buildings_table.php`
- `php -l app\Filament\Resources\Buildings\BuildingResource.php`
- `php -l app\Filament\Resources\Buildings\Pages\ListBuildings.php`
- `php -l app\Filament\Resources\Buildings\Pages\CreateBuilding.php`
- `php -l app\Filament\Resources\Buildings\Pages\ViewBuilding.php`
- `php -l app\Filament\Resources\Buildings\Pages\EditBuilding.php`
- `php artisan migrate`
- `php artisan route:list --path=admin`
- `php artisan route:list --path=admin/buildings`
- `php artisan filament:about`
- `php artisan filament:cache-components`
- `php artisan test`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- A `php artisan tinker --execute` smoke check failed because PsySH attempted to write history to `C:/Users/Administrator/AppData/Roaming/PsySH/psysh_history`, which was not allowed in the current sandbox.
- Follow-up inline `php -r` smoke checks hit PowerShell quoting issues.
- Resolution: completed syntax checks, route checks, Filament component cache check, and the Laravel test suite successfully. No Laravel application errors were found.

### Current Limitations

- Building Registry supports master building records only.
- Building code generation is application-level and based on existing building records for the current year.
- No assessment module has been implemented yet.
- No FEMA scoring has been implemented yet.
- No GIS or map feature has been implemented yet.
- No roles, permissions, audit trail, reports, dashboard, or reassessment workflow enhancements have been implemented yet.

### Next Approved Development Step

```text
Assessment Master Record
```

Do not proceed to the next development phase until explicitly approved.

---

## Assessment Master Record Implementation Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Created / Modified

- `app/Models/Assessment.php`
- `database/migrations/2026_09_11_135715_create_assessments_table.php`
- `app/Models/Building.php`
- `DOCUMENTATION.md`

### Approved Assessment Fields

- `id`
- `assessment_number`
- `building_id`
- `assessment_date`
- `assessment_time`
- `assessor_id`
- `assessment_type`
- `assessment_level`
- `fema_version_id`
- `status`
- `remarks`
- `created_at`
- `updated_at`

### Implementation Completed

- Created the `Assessment` model.
- Created the `assessments` table migration.
- Added unique `assessment_number` as a string field.
- Added required `building_id` foreign key to `buildings`.
- Used `restrictOnDelete()` for `building_id` to protect historical assessments from accidental building deletion.
- Added required `assessment_date`.
- Added nullable `assessment_time`.
- Added nullable `assessor_id` foreign key to `users`.
- Used `nullOnDelete()` for `assessor_id` so assessment history can remain if a user record is removed.
- Added `assessment_type` string field for future values such as `Initial` and `Reassessment`.
- Added `assessment_level` string field for future values such as `Level 1` and `Level 2`.
- Added nullable unsigned big integer `fema_version_id`.
- Added `status` string field with default value `Draft`.
- Added nullable `remarks` text field.
- Added timestamps.
- Assessment number auto-generation is implemented in the `Assessment` model.
- Did not create a FEMA version reference table yet.
- Did not add snapshot fields or score fields.

### Relationships Implemented

- `Assessment` belongs to `Building`.
- `Assessment` belongs to `User` as `assessor`.
- `Building` has many `Assessment` records.

### Migration Status

```text
2026_09_11_135715_create_assessments_table ... DONE
```

The migration is shown as `Ran` in `php artisan migrate:status`.

### Tests / Checks Performed

- `php -l app\Models\Assessment.php`
- `php -l app\Models\Building.php`
- `php -l database\migrations\2026_09_11_135715_create_assessments_table.php`
- `php artisan migrate`
- `php artisan test`
- `php artisan route:list --path=admin`
- `php artisan migrate:status`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- No errors were encountered during the Assessment Master Record model and migration implementation.

### Current Limitations

- No Filament Assessment Resource has been created yet.
- Assessment number auto-generation is implemented in the `Assessment` model.
- `fema_version_id` is a nullable unsigned big integer placeholder only.
- The `fema_version_id` foreign key will be added after the FEMA version reference table exists.
- No FEMA scoring has been implemented.
- No assessment findings have been implemented.
- No assessment photos or documents have been implemented.
- No GIS, role, permission, audit, dashboard, or report features were added.
- No Building Registry schema changes were made.

### Next Approved Development Step

```text
FEMA Reference Data
```

Do not proceed to the next development task until explicitly approved.

---

## Assessment Number Generation Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Assessment Number Format

```text
CBEARS-ASMT-YYYY-XXXXXX
```

Example generated during smoke testing:

```text
CBEARS-ASMT-2026-000001
CBEARS-ASMT-2026-000002
```

### Files Modified

- `app/Models/Assessment.php`
- `DOCUMENTATION.md`

### Implementation Approach

- Implemented assessment number generation in the `Assessment` model.
- Removed `assessment_number` from mass-assignable fields.
- Added an Eloquent `creating` hook that always assigns the generated assessment number when a new assessment is created.
- Used the year from `assessment_date` for the year portion of the number.
- Incremented the numeric sequence within the same assessment year by checking existing assessment records with the same yearly prefix.
- Handled the no-previous-assessment case by starting the sequence at `000001`.
- Added clear failure behavior if `assessment_date` is missing before number generation.
- Added an Eloquent `updating` hook that restores the original `assessment_number` if a later update attempts to change it.
- Preserved the existing unique database constraint.
- Did not change the assessment database schema.

### Checks / Tests Performed

- `php -l app\Models\Assessment.php`
- `php artisan test`
- `php artisan route:list --path=admin`
- Safe smoke test using a temporary PHP script:
  - created a temporary building and two temporary assessments inside a database transaction;
  - generated `CBEARS-ASMT-2026-000001`;
  - generated `CBEARS-ASMT-2026-000002`;
  - rolled back the transaction;
  - deleted the temporary smoke-test script.
- Confirmed the temporary smoke-test file was removed.

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- An inline `php -r` smoke-test attempt failed due to PowerShell/PHP quoting before application code executed.
- Resolution: used a temporary PHP smoke-test script, ran it successfully inside a rolled-back transaction, then deleted the temporary file.
- The first temporary smoke-test run failed because Composer autoloading was not loaded before Laravel bootstrap.
- Resolution: added `vendor/autoload.php` to the temporary smoke-test script before running it again.

### Current Limitations

- No Filament Assessment Resource has been created yet.
- No assessment-number UI display has been implemented yet.
- Assessment number generation does not use explicit database locks, queues, retries, or external coordination.
- Concurrent assessment creation for the same assessment year may still race and rely on the existing unique database constraint to prevent duplicates.
- No FEMA scoring has been implemented.
- No assessment findings, photos, GIS, roles, permissions, audit, dashboard, or report features were added.

### Next Approved Development Step

```text
FEMA Reference Data
```

Do not proceed to the next development task until explicitly approved.

---

## Building Registry UI Refinement Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Modified

- `app/Filament/Resources/Buildings/BuildingResource.php`
- `app/Providers/Filament/AdminPanelProvider.php`
- `DOCUMENTATION.md`

### Implementation Completed

- Improved the Building Resource form layout using clear Filament sections:
  - Building Identification
  - Location
  - Building Information
  - Permits / References
  - Status / Remarks
- Kept the C-BEARS Building Code read-only in the form.
- Added helper text for fields where guidance is useful.
- Improved the Building table presentation with sensible visible columns.
- Preserved searchable columns for:
  - `building_code`
  - `building_name`
  - `owner_or_responsible_office`
  - `barangay`
- Displayed `record_status` as a clean badge with readable status text.
- Grouped the Buildings navigation item under `Building Registry`.
- Changed the Buildings navigation icon to a building-related icon.
- Set the Filament panel brand name to `C-BEARS` using existing panel configuration.

### Tests / Checks Performed

- `php -l app\Filament\Resources\Buildings\BuildingResource.php`
- `php -l app\Providers\Filament\AdminPanelProvider.php`
- `php artisan route:list --path=admin/buildings`
- `php artisan filament:cache-components`
- `php artisan test`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- `php artisan route:list --path=admin/buildings` initially failed because `BuildingResource::$navigationGroup` used `?string`, while Filament 5 requires `UnitEnum|string|null`.
- Resolution: updated the resource property type to `string|UnitEnum|null`. The route check, Filament component cache check, and test suite then passed.

### Current Limitations

- This task only refined Building Registry UI presentation.
- No business logic was changed.
- No database schema was changed.
- No assessment, GIS, roles, authentication, reporting, dashboard, or audit features were added.
- Building code generation logic remains unchanged.

### Next Approved Development Step

```text
Assessment Master Record
```

Do not proceed to the next development phase until explicitly approved.

---

## Assessment Filament Resource Implementation Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Created / Modified

- `app/Filament/Resources/Assessments/AssessmentResource.php`
- `app/Filament/Resources/Assessments/Pages/ListAssessments.php`
- `app/Filament/Resources/Assessments/Pages/CreateAssessment.php`
- `app/Filament/Resources/Assessments/Pages/ViewAssessment.php`
- `app/Filament/Resources/Assessments/Pages/EditAssessment.php`
- `DOCUMENTATION.md`

### Fields Implemented

- `assessment_number`
- `building_id`
- `assessment_date`
- `assessment_time`
- `assessor_id`
- `assessment_type`
- `assessment_level`
- `status`
- `remarks`

`fema_version_id` was not exposed as a normal editable field.

### Form Sections

- Assessment Identification
- Assessment Details
- Status and Remarks

### Form Behavior and Validation

- `assessment_number` is read-only / disabled and is generated by the `Assessment` model.
- `building_id` is required and uses a searchable building selector.
- Building selector labels use:

```text
building_code - building_name
```

- `assessment_date` is required and uses a date picker.
- `assessment_time` is optional and uses a time picker.
- `assessor_id` is nullable and uses a searchable User relationship selector.
- No assessor role filtering was added.
- `assessment_type` is required and limited to:
  - Initial
  - Reassessment
- `assessment_level` is required and limited to:
  - Level 1
  - Level 2
- `status` is required and defaults to `Draft`.
- Status options are:
  - Draft
  - For Review
  - Reviewed
  - Completed
  - Returned
  - Cancelled
  - Incomplete
- `remarks` is nullable and uses a textarea.

### Table / List Behavior

- Added List, Create, View, and Edit Assessment pages.
- The assessment table shows:
  - assessment number
  - building
  - assessment date
  - assessment type
  - assessment level
  - status
  - assessor
- Useful columns are searchable and/or sortable where appropriate.
- Status is displayed as a readable badge.
- No custom workflow actions were added.
- Delete actions were not added to the Assessment Resource.

### Checks / Tests Performed

- `php -l app\Filament\Resources\Assessments\AssessmentResource.php`
- `php -l app\Filament\Resources\Assessments\Pages\ListAssessments.php`
- `php -l app\Filament\Resources\Assessments\Pages\CreateAssessment.php`
- `php -l app\Filament\Resources\Assessments\Pages\ViewAssessment.php`
- `php -l app\Filament\Resources\Assessments\Pages\EditAssessment.php`
- `php artisan route:list --path=admin/assessments`
- `php artisan route:list --path=admin`
- `php artisan filament:cache-components`
- `php artisan test`
- Confirmed `fema_version_id` is not exposed in the Assessment Resource.
- Confirmed custom workflow/delete actions were not added.

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- No errors were encountered during the Assessment Filament Resource implementation.

### Current Limitations

- Basic status editing is available, but no workflow actions such as Submit, Review, Approve, Return, or Complete have been implemented.
- No FEMA scoring has been implemented.
- No FEMA reference tables have been implemented.
- No assessment findings have been implemented.
- No assessment photos or documents have been implemented.
- No GIS features have been implemented.
- No role or permission changes were made.
- No building snapshot fields were added.
- `fema_version_id` remains unused until the FEMA Reference Data phase.

### Next Approved Development Step

```text
FEMA Reference Data
```

Do not proceed to the next development task until explicitly approved.

---

## Assessment Building Selector Search Fix Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Modified

- `app/Filament/Resources/Assessments/AssessmentResource.php`
- `DOCUMENTATION.md`

### Issue Fixed

- The Building selector in the Assessment form showed relationship options, but searching by building name such as `carmona` returned no matching options.

### Implementation

- Kept the selector based on the existing `building` relationship.
- Replaced the custom Building search callback with standard Filament relationship Select behavior.
- Configured Building search to use both:
  - `building_code`
  - `building_name`
- Preserved option labels in this format:

```text
building_code - building_name
```

- Enabled preloading so available buildings can appear before typing.
- Enabled case-insensitive search behavior for PostgreSQL-friendly searching.

### Checks / Tests Performed

- `php -l app\Filament\Resources\Assessments\AssessmentResource.php`
- `php artisan route:list --path=admin/assessments`
- `php artisan filament:cache-components`
- `php artisan test`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- No errors were encountered during this fix.

### Current Limitations

- This task only fixed the Assessment form Building selector search.
- No database schema changes were made.
- Building code generation and Assessment number generation were not changed.
- No FEMA scoring, findings, photos, GIS, roles, permissions, or workflow actions were implemented.

### Next Approved Development Step

```text
FEMA Reference Data
```

Do not proceed to the next development task until explicitly approved.

---

## FEMA Version Reference Data Implementation Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Created / Modified

- `app/Models/FemaVersion.php`
- `database/migrations/2026_09_11_142740_create_fema_versions_table.php`
- `DOCUMENTATION.md`

### FEMA Baseline

```text
FEMA P-154, Third Edition
```

### Companion Reference

```text
FEMA P-155, Third Edition
```

### Approved Fields

- `id`
- `code`
- `title`
- `edition`
- `publication_year`
- `effective_date`
- `is_active`
- `remarks`
- `created_at`
- `updated_at`

### Model / Table Structure

- Created the `FemaVersion` model.
- Created the `fema_versions` table.
- `code` is required, unique, and stored as a string.
- `title` is required and stored as a string.
- `edition` is required and stored as a string.
- `publication_year` is nullable and stored as an unsigned small integer.
- `effective_date` is nullable and stored as a date.
- `effective_date` represents local/system adoption or effective use date, not necessarily the FEMA publication date.
- `is_active` is a boolean with default value `true`.
- `remarks` is nullable text.
- Timestamps were added.

### Migration Status

```text
2026_09_11_142740_create_fema_versions_table ... DONE
```

The migration is shown as `Ran` in `php artisan migrate:status`.

### Tests / Checks Performed

- `php -l app\Models\FemaVersion.php`
- `php -l database\migrations\2026_09_11_142740_create_fema_versions_table.php`
- `php artisan migrate`
- `php artisan test`
- `php artisan migrate:status`
- `php artisan route:list --path=admin`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- No errors were encountered during the FEMA Version reference model and migration implementation.

### Current Limitations

- No FEMA Version Filament Resource has been created yet.
- No FEMA building types have been created yet.
- No basic scores have been created yet.
- No score modifiers have been created yet.
- No scoring logic has been implemented.
- No seed data was added.
- No unverified FEMA scoring data was added.
- No foreign key from `assessments.fema_version_id` to `fema_versions.id` was added in this task.
- No roles, permissions, GIS, findings, photos, or Assessment Resource changes were made.

### Next Approved Development Step

```text
Structural Classification
```

Do not proceed to the next development task until explicitly approved.

---

## FEMA Building Type Reference Data Implementation Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Created / Modified

- `app/Models/FemaBuildingType.php`
- `database/migrations/2026_09_11_143619_create_fema_building_types_table.php`
- `app/Models/FemaVersion.php`
- `DOCUMENTATION.md`

### Approved Fields

- `id`
- `fema_version_id`
- `code`
- `name`
- `description`
- `material_category`
- `structural_system`
- `is_active`
- `remarks`
- `created_at`
- `updated_at`

### Model / Table Structure

- Created the `FemaBuildingType` model.
- Created the `fema_building_types` table.
- `fema_version_id` is required and references `fema_versions`.
- `fema_version_id` uses `restrictOnDelete()` to protect FEMA reference integrity.
- `code` is required and stored as a string.
- `name` is required and stored as a string.
- `description` is nullable text.
- `material_category` is nullable string.
- `structural_system` is nullable string.
- `is_active` is a boolean with default value `true`.
- `remarks` is nullable text.
- Timestamps were added.

### Relationships Implemented

- `FemaBuildingType` belongs to `FemaVersion`.
- `FemaVersion` has many `FemaBuildingType` records.

### Constraints Added

- Added a composite unique constraint on:

```text
fema_version_id + code
```

### Migration Status

```text
2026_09_11_143619_create_fema_building_types_table ... DONE
```

The migration is shown as `Ran` in `php artisan migrate:status`.

### Tests / Checks Performed

- `php -l app\Models\FemaBuildingType.php`
- `php -l app\Models\FemaVersion.php`
- `php -l database\migrations\2026_09_11_143619_create_fema_building_types_table.php`
- `php artisan migrate`
- `php artisan test`
- `php artisan migrate:status`
- `php artisan route:list --path=admin`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- No errors were encountered during the FEMA Building Type reference model and migration implementation.

### Current Limitations

- No FEMA Building Type Filament Resource has been created yet.
- No FEMA building type seed data was added.
- No basic score data has been implemented.
- No score modifier data has been implemented.
- No scoring logic has been implemented.
- No Assessment Filament Resource changes were made.
- No roles, permissions, GIS, findings, photos, or package changes were made.

### Next Approved Development Step

```text
Basic Score Reference Data
```

Do not proceed to the next development task until explicitly approved.

---

## FEMA Building Type Seed Data Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Created / Modified

- `database/seeders/FemaBuildingTypeSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `DOCUMENTATION.md`

### FEMA P-154 Third Edition Version Record

The seeder ensures this FEMA version exists:

- `code`: `P154-3E`
- `title`: `Rapid Visual Screening of Buildings for Potential Seismic Hazards`
- `edition`: `Third Edition`
- `publication_year`: `2015`
- `is_active`: `true`

### Verified FEMA Building Types Seeded

Exactly 17 FEMA P-154 Third Edition building type records were seeded and linked to `P154-3E`:

- `W1`
- `W1A`
- `W2`
- `S1`
- `S2`
- `S3`
- `S4`
- `S5`
- `C1`
- `C2`
- `C3`
- `PC1`
- `PC2`
- `RM1`
- `RM2`
- `URM`
- `MH`

### Idempotent Seeding Approach

- Used `updateOrCreate()` for the `P154-3E` FEMA version record.
- Used `updateOrCreate()` for each FEMA building type using:

```text
fema_version_id + code
```

- Added `FemaBuildingTypeSeeder` to the normal database seeding flow through `DatabaseSeeder`.
- Ran the FEMA seeder directly during verification to avoid touching unrelated existing default user seed behavior.
- Did not add Basic Scores or Score Modifiers to the seeded records.

### Seeding Result

Initial seeder run:

```text
Seeded 17 new and updated 0 existing FEMA P-154 Third Edition building type records.
```

Idempotency check run:

```text
Seeded 0 new and updated 17 existing FEMA P-154 Third Edition building type records.
```

### Verification Result

Safe database verification confirmed:

```text
version=P154-3E
count=17
codes=C1,C2,C3,MH,PC1,PC2,RM1,RM2,S1,S2,S3,S4,S5,URM,W1,W1A,W2
```

### Checks / Tests Performed

- `php -l database\seeders\FemaBuildingTypeSeeder.php`
- `php -l database\seeders\DatabaseSeeder.php`
- `php artisan db:seed --class=FemaBuildingTypeSeeder`
- `php artisan db:seed --class=FemaBuildingTypeSeeder`
- `php artisan test`
- `php artisan migrate:status`
- Temporary safe Laravel bootstrap verification script for the `P154-3E` building type count.
- Confirmed the temporary verification script was removed.

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- An inline `php -r` database verification attempt failed due to PowerShell/PHP quoting before application code executed.
- Resolution: used a temporary Laravel bootstrap verification script, confirmed exactly 17 linked `P154-3E` building type records, then removed the temporary script.

### Current Limitations

- No Basic Score reference table has been created yet.
- No Score Modifier reference table has been created yet.
- No scoring logic has been implemented.
- No Assessment Filament Resource changes were made.
- No FEMA Building Type Filament Resource has been created yet.
- No GIS, findings, photos, roles, permissions, or package changes were made.

### Next Approved Development Step

```text
Basic Score Reference Data
```

Do not proceed to the next development task until explicitly approved.

---

## FEMA Basic Score Reference Structure Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Created / Modified

- `app/Models/FemaBasicScore.php`
- `database/migrations/2026_09_11_144655_create_fema_basic_scores_table.php`
- `app/Models/FemaVersion.php`
- `app/Models/FemaBuildingType.php`
- `DOCUMENTATION.md`

### Approved Fields

- `id`
- `fema_version_id`
- `fema_building_type_id`
- `seismicity_level`
- `basic_score`
- `is_active`
- `remarks`
- `created_at`
- `updated_at`

### Seismicity Levels

The `seismicity_level` field is a required string intended for these approved values:

- `Low`
- `Moderate`
- `Moderately High`
- `High`
- `Very High`

### Model / Table Structure

- Created the `FemaBasicScore` model.
- Created the `fema_basic_scores` table.
- `fema_version_id` is required and references `fema_versions`.
- `fema_version_id` uses `restrictOnDelete()` to protect reference and history integrity.
- `fema_building_type_id` is required and references `fema_building_types`.
- `fema_building_type_id` uses `restrictOnDelete()` to protect reference and history integrity.
- `seismicity_level` is required and stored as a string.
- `basic_score` is required and stored as a decimal using precision `5,2`.
- `is_active` is a boolean with default value `true`.
- `remarks` is nullable text.
- Timestamps were added.

### Relationships Implemented

- `FemaBasicScore` belongs to `FemaVersion`.
- `FemaBasicScore` belongs to `FemaBuildingType`.
- `FemaVersion` has many `FemaBasicScore` records.
- `FemaBuildingType` has many `FemaBasicScore` records.

### Constraints Added

- Added a composite unique constraint on:

```text
fema_version_id + fema_building_type_id + seismicity_level
```

### Migration Status

```text
2026_09_11_144655_create_fema_basic_scores_table ... DONE
```

The migration is shown as `Ran` in `php artisan migrate:status`.

### Checks / Tests Performed

- `php -l app\Models\FemaBasicScore.php`
- `php -l app\Models\FemaVersion.php`
- `php -l app\Models\FemaBuildingType.php`
- `php -l database\migrations\2026_09_11_144655_create_fema_basic_scores_table.php`
- `php artisan migrate`
- `php artisan test`
- `php artisan migrate:status`
- `php artisan route:list --path=admin`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- No errors were encountered during the FEMA Basic Score reference model and migration implementation.

### Current Limitations

- No actual Basic Score values were seeded.
- No Score Modifier reference table has been created yet.
- No scoring logic or score calculation service has been implemented.
- No Assessment Filament Resource changes were made.
- No FEMA Basic Score Filament Resource has been created yet.
- No GIS, findings, photos, roles, permissions, or package changes were made.

### Next Approved Development Step

```text
Score Modifier Reference Data
```

Do not proceed to the next development task until explicitly approved.

---

## FEMA Basic Score Seed Data Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Created / Modified

- `database/seeders/FemaBasicScoreSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `DOCUMENTATION.md`

### Source Basis

```text
FEMA P-154 / FEMA P-155, Third Edition
```

### Implementation Completed

- Created `FemaBasicScoreSeeder`.
- Seeded FEMA P-154 Third Edition Basic Scores for FEMA Version `P154-3E`.
- Used the existing `FemaVersion`, `FemaBuildingType`, and `FemaBasicScore` models.
- Seeded exactly 85 records:

```text
17 FEMA building types x 5 seismicity levels
```

- Used the approved seismicity levels:
  - `Low`
  - `Moderate`
  - `Moderately High`
  - `High`
  - `Very High`
- Kept `is_active` set to `true`.
- Added `FemaBasicScoreSeeder` to the normal database seeding flow after `FemaBuildingTypeSeeder`.
- Did not seed FEMA `Very High MAX` electronic-scoring values.
- Did not add Score Modifiers.
- Did not implement scoring logic.

### Idempotent Seeding Approach

- Used `updateOrCreate()` based on:

```text
fema_version_id + fema_building_type_id + seismicity_level
```

- Used the supplied FEMA score values directly.
- Did not infer, calculate, normalize, or alter the supplied values.

### Seeding Result

Initial seeder run:

```text
Seeded 85 new and updated 0 existing FEMA P-154 Third Edition basic score records.
```

Idempotency check run:

```text
Seeded 0 new and updated 85 existing FEMA P-154 Third Edition basic score records.
```

### Verification Result

Safe database verification confirmed:

```text
version=P154-3E
total=85
Low=17
Moderate=17
Moderately High=17
High=17
Very High=17
```

### Checks / Tests Performed

- `php -l database\seeders\FemaBasicScoreSeeder.php`
- `php -l database\seeders\DatabaseSeeder.php`
- `php artisan db:seed --class=FemaBasicScoreSeeder`
- `php artisan db:seed --class=FemaBasicScoreSeeder`
- Temporary safe Laravel bootstrap verification script for the `P154-3E` Basic Score count and seismicity-level counts.
- Confirmed the temporary verification script was removed.
- `php artisan test`
- `php artisan migrate:status`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- No implementation errors were encountered during the FEMA Basic Score seed data task.
- A temporary verification script was used proactively to avoid prior inline PowerShell/PHP quoting issues.

### Current Limitations

- FEMA `Very High MAX` electronic-scoring values were not included.
- No Score Modifier reference table has been created yet.
- No scoring logic or score calculation service has been implemented.
- No Assessment Filament Resource changes were made.
- No FEMA Basic Score Filament Resource has been created yet.
- No GIS, findings, photos, roles, permissions, or package changes were made.

### Next Approved Development Step

```text
Score Modifier Reference Data
```

Do not proceed to the next development task until explicitly approved.

---

## FEMA Score Modifier Reference Structure Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Created / Modified

- `app/Models/FemaScoreModifier.php`
- `database/migrations/2026_09_11_150530_create_fema_score_modifiers_table.php`
- `app/Models/FemaVersion.php`
- `app/Models/FemaBuildingType.php`
- `DOCUMENTATION.md`

### Approved Fields

- `id`
- `fema_version_id`
- `fema_building_type_id`
- `seismicity_level`
- `assessment_level`
- `modifier_category`
- `modifier_code`
- `modifier_name`
- `modifier_value`
- `is_applicable`
- `applicability_notes`
- `is_active`
- `remarks`
- `created_at`
- `updated_at`

### Seismicity Levels

The `seismicity_level` field is a required string intended for these approved values:

- `Low`
- `Moderate`
- `Moderately High`
- `High`
- `Very High`

### Assessment Levels

The `assessment_level` field is a required string intended for these approved values:

- `Level 1`
- `Level 2`

### Supported Initial Level 1 Modifier Categories

- `Plan Irregularity`
- `Vertical Irregularity`
- `Pre-Code`
- `Post-Benchmark`

### Model / Table Structure

- Created the `FemaScoreModifier` model.
- Created the `fema_score_modifiers` table.
- `fema_version_id` is required and references `fema_versions`.
- `fema_version_id` uses `restrictOnDelete()` to protect reference and history integrity.
- `fema_building_type_id` is required and references `fema_building_types`.
- `fema_building_type_id` uses `restrictOnDelete()` to protect reference and history integrity.
- `seismicity_level` is required and stored as a string.
- `assessment_level` is required and stored as a string.
- `modifier_category` is required and stored as a string.
- `modifier_code` is required and stored as a string.
- `modifier_name` is required and stored as a string.
- `modifier_value` is nullable and stored as a decimal using precision `5,2`.
- `is_applicable` is a boolean with default value `true`.
- `applicability_notes` is nullable text.
- `is_active` is a boolean with default value `true`.
- `remarks` is nullable text.
- Timestamps were added.

### Relationships Implemented

- `FemaScoreModifier` belongs to `FemaVersion`.
- `FemaScoreModifier` belongs to `FemaBuildingType`.
- `FemaVersion` has many `FemaScoreModifier` records.
- `FemaBuildingType` has many `FemaScoreModifier` records.

### Constraints Added

- Added a composite unique constraint on:

```text
fema_version_id + fema_building_type_id + seismicity_level + assessment_level + modifier_code
```

### Migration Status

```text
2026_09_11_150530_create_fema_score_modifiers_table ... DONE
```

The migration is shown as `Ran` in `php artisan migrate:status`.

### Checks / Tests Performed

- `php -l app\Models\FemaScoreModifier.php`
- `php -l app\Models\FemaVersion.php`
- `php -l app\Models\FemaBuildingType.php`
- `php -l database\migrations\2026_09_11_150530_create_fema_score_modifiers_table.php`
- `php artisan migrate`
- `php artisan test`
- `php artisan migrate:status`
- `php artisan route:list --path=admin`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- No errors were encountered during the FEMA Score Modifier reference model and migration implementation.

### Current Limitations

- No actual FEMA modifier values were seeded.
- No Score Modifier Filament Resource has been created yet.
- No scoring logic or score calculation service has been implemented.
- No Assessment Filament Resource changes were made.
- No GIS, findings, photos, roles, permissions, or package changes were made.

### Next Approved Development Step

```text
Score Modifier Reference Data
```

Do not proceed to the next development task until explicitly approved.

---

## FEMA Minimum Score Reference Structure Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Created / Modified

- `app/Models/FemaMinimumScore.php`
- `database/migrations/2026_09_12_000000_create_fema_minimum_scores_table.php`
- `app/Models/FemaVersion.php`
- `app/Models/FemaBuildingType.php`
- `DOCUMENTATION.md`

### Approved Fields

- `id`
- `fema_version_id`
- `fema_building_type_id`
- `seismicity_level`
- `minimum_score`
- `is_active`
- `remarks`
- `created_at`
- `updated_at`

### Seismicity Levels

The `seismicity_level` field is a required string intended for these approved values:

- `Low`
- `Moderate`
- `Moderately High`
- `High`
- `Very High`

### Model / Table Structure

- Created the `FemaMinimumScore` model.
- Created the `fema_minimum_scores` table.
- `fema_version_id` is required and references `fema_versions`.
- `fema_version_id` uses `restrictOnDelete()` to protect reference and history integrity.
- `fema_building_type_id` is required and references `fema_building_types`.
- `fema_building_type_id` uses `restrictOnDelete()` to protect reference and history integrity.
- `seismicity_level` is required and stored as a string.
- `minimum_score` is required and stored as a decimal using precision `5,2`.
- `is_active` is a boolean with default value `true`.
- `remarks` is nullable text.
- Timestamps were added.
- FEMA `S_MIN` minimum scores are stored separately from additive score modifiers.
- Minimum score was not treated as an additive modifier.

### Relationships Implemented

- `FemaMinimumScore` belongs to `FemaVersion`.
- `FemaMinimumScore` belongs to `FemaBuildingType`.
- `FemaVersion` has many `FemaMinimumScore` records.
- `FemaBuildingType` has many `FemaMinimumScore` records.

### Constraints Added

- Added a composite unique constraint on:

```text
fema_version_id + fema_building_type_id + seismicity_level
```

### Migration Status

```text
2026_09_12_000000_create_fema_minimum_scores_table ... DONE
```

The migration is shown as `Ran` in `php artisan migrate:status`.

### Checks / Tests Performed

- `php -l app\Models\FemaMinimumScore.php`
- `php -l app\Models\FemaVersion.php`
- `php -l app\Models\FemaBuildingType.php`
- `php -l database\migrations\2026_09_12_000000_create_fema_minimum_scores_table.php`
- `php artisan migrate`
- `php artisan test`
- `php artisan migrate:status`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- The sandbox helper intermittently failed to acquire its lock directory during file reads, patching, and command execution.
- Resolution: used approved elevated command execution for the required reads, narrowly scoped file writes, PHP syntax checks, migration, test suite, and migration status checks.
- An initial documentation update malformed and duplicated document sections during PowerShell replacement.
- Resolution: rebuilt `DOCUMENTATION.md` from the final clean document copy, then appended one clean implementation log and latest status block.
- No Laravel application, migration, syntax, or test errors were encountered after implementation.

### Current Limitations

- No actual FEMA minimum-score values were seeded.
- No score modifier values were seeded in this task.
- No scoring logic or score calculation service has been implemented.
- No Assessment Filament Resource changes were made.
- No FEMA Minimum Score Filament Resource has been created yet.
- No GIS, findings, photos, roles, permissions, or package changes were made.

### Next Approved Development Step

```text
Minimum Score Reference Data
```

Do not proceed to the next development task until explicitly approved.

---
---

## FEMA Basic Score Audit Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Modified

- `database/seeders/FemaBasicScoreSeeder.php`
- `DOCUMENTATION.md`

A temporary read-only verification script, `_tmp_basic_score_audit.php`, was created for database verification and removed after the audit.

### Source Basis

```text
FEMA P-154 / FEMA P-155, Third Edition
```

The supplied matrix for FEMA Version `P154-3E` was treated as the approved Basic Score dataset for this task. Values were not inferred, calculated, normalized, or substituted.

### Audit Result

- Re-audited all 85 Basic Score combinations:

```text
17 FEMA building types x 5 seismicity levels
```

- Confirmed the seeder continues to use idempotent `updateOrCreate()` behavior keyed by:

```text
fema_version_id + fema_building_type_id + seismicity_level
```

- Kept `is_active = true`.
- Kept the existing schema, relationships, and unique constraints unchanged.
- Did not touch `fema_minimum_scores`.
- Did not create or modify Score Modifier records.
- Did not implement scoring logic.

### Corrected Basic Score Records

The pre-seed audit found 4 mismatched Basic Score records, all corrected by running the updated seeder:

- `W1A` / `Moderately High`: `3.70` corrected to `3.20`
- `W1A` / `Very High`: `1.90` corrected to `1.80`
- `W2` / `Moderately High`: `3.20` corrected to `3.70`
- `W2` / `Very High`: `1.80` corrected to `1.90`

### Seeder Result

```text
Seeded 0 new and updated 85 existing FEMA P-154 Third Edition basic score records.
```

Existing records were updated in place. No duplicate Basic Score records were created.

### Verification Result

Safe database verification after seeding confirmed:

```text
version=P154-3E
total=85
Low=17
Moderate=17
Moderately High=17
High=17
Very High=17
mismatch_count=0
```

Explicit W1A verification:

```text
W1A|Low=5.90
W1A|Moderate=4.50
W1A|Moderately High=3.20
W1A|High=3.20
W1A|Very High=1.80
```

Explicit W2 verification:

```text
W2|Low=5.70
W2|Moderate=3.80
W2|Moderately High=3.70
W2|High=2.90
W2|Very High=1.90
```

### Checks / Tests Performed

- `php -l database\seeders\FemaBasicScoreSeeder.php`
- Pre-seed safe database verification for all 85 Basic Score combinations
- `php artisan db:seed --class=FemaBasicScoreSeeder`
- Post-seed safe database verification for all 85 Basic Score combinations
- Explicit post-seed W1A and W2 value verification
- `php artisan test`
- `php artisan migrate:status`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- The sandbox helper intermittently failed to acquire its lock directory during file reads and patching.
- Resolution: used approved elevated command execution for required reads, narrowly scoped seeder editing, syntax checks, seeding, verification, tests, and migration status checks.
- `apply_patch` was blocked by the sandbox helper while editing the seeder.
- Resolution: used a narrow PowerShell replacement only for the two affected seeder rows.
- No Laravel application, seeder, test, or migration-status errors were encountered.

### Current Limitations

- No FEMA Minimum Score / `S_MIN` values were seeded yet.
- No score modifiers were seeded yet.
- No scoring engine or score-calculation service has been implemented.
- No Assessment Filament Resource changes were made.
- No GIS, findings, photos, roles, permissions, or package changes were made.

### Next Approved Development Step

```text
FEMA Minimum Score (S_MIN) seeding
```

Do not proceed to the next development task until explicitly approved.

---

## FEMA Minimum Score Seed Data Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Created / Modified

- `database/seeders/FemaMinimumScoreSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `DOCUMENTATION.md`

A temporary read-only verification script, `_tmp_minimum_score_verify.php`, was created for database verification and removed after the checks.

### Source Basis

```text
FEMA P-154 / FEMA P-155, Third Edition
```

The supplied FEMA P-154 Third Edition `S_MIN` matrix for FEMA Version `P154-3E` was treated as the verified source dataset for this task. Values were used directly and were not inferred, calculated, normalized, or substituted.

### Implementation Completed

- Created `FemaMinimumScoreSeeder`.
- Seeded FEMA P-154 Third Edition Minimum Scores (`S_MIN`) for FEMA Version `P154-3E`.
- Used the existing `FemaVersion`, `FemaBuildingType`, and `FemaMinimumScore` models.
- Seeded exactly 85 records:

```text
17 FEMA building types x 5 seismicity levels
```

- Used the approved seismicity levels:
  - `Low`
  - `Moderate`
  - `Moderately High`
  - `High`
  - `Very High`
- Kept `is_active` set to `true`.
- Added `FemaMinimumScoreSeeder` to the normal database seeding flow after `FemaBuildingTypeSeeder` and `FemaBasicScoreSeeder`.
- Stored `S_MIN` separately from additive score modifiers.
- Did not modify the Basic Score dataset.
- Did not seed Score Modifiers.
- Did not implement scoring logic.

### Idempotent Seeding Approach

- Used `updateOrCreate()` based on:

```text
fema_version_id + fema_building_type_id + seismicity_level
```

- Kept the existing schema, relationships, and unique constraints unchanged.

### Seeding Result

Initial seeder run:

```text
Seeded 85 new and updated 0 existing FEMA P-154 Third Edition minimum score records.
```

Idempotency check run:

```text
Seeded 0 new and updated 85 existing FEMA P-154 Third Edition minimum score records.
```

### Verification Result

Safe database verification confirmed:

```text
version=P154-3E
total=85
Low=17
Moderate=17
Moderately High=17
High=17
Very High=17
mismatch_count=0
```

### Checks / Tests Performed

- `php -l database\seeders\FemaMinimumScoreSeeder.php`
- `php -l database\seeders\DatabaseSeeder.php`
- `php artisan db:seed --class=FemaMinimumScoreSeeder`
- `php artisan db:seed --class=FemaMinimumScoreSeeder`
- Safe database verification for all 85 S_MIN combinations
- `php artisan test`
- `php artisan migrate:status`
- Confirmed the temporary verification script was removed.

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- The sandbox helper intermittently failed to acquire its lock directory during file reads and edits.
- Resolution: used approved elevated command execution for required reads, scoped file writes, syntax checks, seeding, verification, tests, and migration status checks.
- No Laravel application, seeder, test, verification, or migration-status errors were encountered.

### Current Limitations

- No Score Modifier values were seeded yet.
- No scoring engine or score-calculation service has been implemented.
- No Assessment Filament Resource changes were made.
- No GIS, findings, photos, roles, permissions, or package changes were made.

### Next Approved Development Step

```text
FEMA Level 1 Score Modifier data verification/seeding
```

Do not proceed to the next development task until explicitly approved.

---

## FEMA Basic Score Correction Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Modified

- `database/seeders/FemaBasicScoreSeeder.php`
- `DOCUMENTATION.md`

A temporary read-only verification script, `_tmp_basic_score_correction_verify.php`, was created for database verification and removed after the checks.

### Correction Performed

Corrected the FEMA P-154 Third Edition `Moderately High` Basic Score values for FEMA Version `P154-3E`:

- `W1A` / `Moderately High`: `3.20` corrected to `3.70`
- `W2` / `Moderately High`: `3.70` corrected to `3.20`

Kept these values unchanged:

- `W1A` / `Very High`: `1.80`
- `W2` / `Very High`: `1.90`

### Implementation Notes

- Updated only the existing `FemaBasicScoreSeeder` dataset as required.
- Preserved idempotent `updateOrCreate()` behavior keyed by:

```text
fema_version_id + fema_building_type_id + seismicity_level
```

- Ran the seeder to update existing records in place.
- Did not change the database schema.
- Did not touch `fema_minimum_scores`.
- Did not touch `fema_score_modifiers`.
- Did not modify Assessment forms.
- Did not implement scoring logic.

### Seeder Result

```text
Seeded 0 new and updated 85 existing FEMA P-154 Third Edition basic score records.
```

### Verification Result

Safe database verification confirmed all 85 FEMA Basic Score records match the approved corrected matrix:

```text
version=P154-3E
total=85
Low=17
Moderate=17
Moderately High=17
High=17
Very High=17
mismatch_count=0
```

Explicit W1A verification:

```text
W1A|Low=5.90
W1A|Moderate=4.50
W1A|Moderately High=3.70
W1A|High=3.20
W1A|Very High=1.80
```

Explicit W2 verification:

```text
W2|Low=5.70
W2|Moderate=3.80
W2|Moderately High=3.20
W2|High=2.90
W2|Very High=1.90
```

### Checks / Tests Performed

- `php -l database\seeders\FemaBasicScoreSeeder.php`
- `php artisan db:seed --class=FemaBasicScoreSeeder`
- Safe database verification for all 85 Basic Score combinations
- Explicit W1A and W2 value verification
- `php artisan test`
- `php artisan migrate:status`
- Confirmed the temporary verification script was removed.

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- The sandbox helper intermittently failed to acquire its lock directory during file reads and edits.
- Resolution: used approved elevated command execution for required reads, scoped file writes, syntax checks, seeding, verification, tests, and migration status checks.
- No Laravel application, seeder, verification, test, or migration-status errors were encountered.

### Current Limitations

- No Score Modifier values were seeded yet.
- No scoring engine or score-calculation service has been implemented.
- No Assessment form changes were made.
- No GIS, findings, photos, roles, permissions, or package changes were made.

### Next Approved Development Step

```text
FEMA Level 1 Score Modifier verification/seeding
```

Do not proceed to the next development task until explicitly approved.

---
---

## FEMA Level 1 Score Modifier Seed Data Log

### Current Phase

```text
FEMA Reference Data / Level 1 Score Modifiers
```

### Files Created / Modified

- `database/seeders/FemaScoreModifierSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `DOCUMENTATION.md`

A temporary read-only verification script, `_tmp_score_modifier_verify.php`, was created for database verification and removed after the checks.

### Source Basis

```text
FEMA P-154 / FEMA P-155, Third Edition Appendix B
```

Source CSV:

```text
docs/data/FEMA_P154_Level1_Modifiers_Verified.csv
```

The verified CSV was treated as the source of truth for all FEMA P-154 Third Edition Level 1 Score Modifier values. Values were not inferred, calculated, normalized, or substituted.

### Implementation Completed

- Created `FemaScoreModifierSeeder`.
- Read the verified CSV and seeded the existing `fema_score_modifiers` table.
- Used existing `FemaVersion`, `FemaBuildingType`, and `FemaScoreModifier` models.
- Seeded FEMA Version `P154-3E`.
- Seeded assessment level `Level 1` only.
- Seeded exactly 680 combinations:

```text
17 FEMA building types x 5 seismicity levels x 8 modifier codes
```

- Added `FemaScoreModifierSeeder` to `DatabaseSeeder` after prerequisite FEMA reference seeders.
- Kept `is_active = true`.
- Did not modify Basic Scores.
- Did not modify Minimum Scores / `S_MIN`.
- Did not change the database schema.
- Did not modify the Assessment Filament Resource.
- Did not add Level 2 data.
- Did not implement scoring logic.

### Modifier Codes / Categories

- `VERTICAL_SEVERE`: `Vertical Irregularity`
- `VERTICAL_MODERATE`: `Vertical Irregularity`
- `PLAN_IRREGULARITY`: `Plan Irregularity`
- `PRE_CODE`: `Pre-Code`
- `POST_BENCHMARK`: `Post-Benchmark`
- `SOIL_AB`: `Soil`
- `SOIL_E_LOW_RISE`: `Soil`
- `SOIL_E_MID_HIGH_RISE`: `Soil`

### N/A and Zero Handling

- CSV rows with `is_applicable = false` were stored with `modifier_value = null` and `is_applicable = false`.
- N/A values were not converted to zero.
- CSV rows with `modifier_value = 0.00` and `is_applicable = true` were stored as applicable FEMA values.

### Idempotent Seeding Approach

- Used `updateOrCreate()` based on:

```text
fema_version_id + fema_building_type_id + seismicity_level + assessment_level + modifier_code
```

### Seeding Result

Initial seeder run:

```text
Seeded 680 new and updated 0 existing FEMA P-154 Third Edition Level 1 score modifier records.
```

Idempotency check run:

```text
Seeded 0 new and updated 680 existing FEMA P-154 Third Edition Level 1 score modifier records.
```

### Verification Result

Safe database verification confirmed:

```text
total=680
Very High=136
High=136
Moderately High=136
Moderate=136
Low=136
applicable=618
non_applicable=62
false_with_non_null=0
zero_applicable=12
building_types_Very_High=17
building_types_High=17
building_types_Moderately_High=17
building_types_Moderate=17
building_types_Low=17
modifier_count_violations=0
low_pre_code_na_failures=0
mh_vertical_plan_na_failures=0
post_benchmark_na_failures=0
soil_e_mid_high_rise_na_failures=0
w1_sample_failures=0
mismatch_count=0
```

### Checks / Tests Performed

- `php -l database\seeders\FemaScoreModifierSeeder.php`
- `php -l database\seeders\DatabaseSeeder.php`
- `php artisan db:seed --class=FemaScoreModifierSeeder`
- `php artisan db:seed --class=FemaScoreModifierSeeder`
- Safe database verification against the CSV for all 680 combinations
- Verified 17 building types per seismicity level
- Verified 8 modifier rows per building type / seismicity level
- Verified N/A values are stored as `null` with `is_applicable = false`
- Verified `0.00` values remain `is_applicable = true`
- Verified explicit applicability rules and W1 sample values
- `php artisan test`
- `php artisan migrate:status`
- Confirmed the temporary verification script was removed.

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- The sandbox helper intermittently failed to acquire its lock directory during reads and edits.
- Resolution: used approved elevated command execution for required reads, scoped file writes, syntax checks, seeding, verification, tests, and migration status checks.
- `apply_patch` was blocked by the sandbox helper while creating/updating seeder files.
- Resolution: used a narrow PowerShell write for the declared seeder files only.
- The first version of the temporary verification script produced a PostgreSQL ambiguous-column error while joining building types for a count.
- Resolution: qualified the verification query columns and reran the safe verification successfully.
- No Laravel application, seeder, test, or migration-status errors were encountered.

### Current Limitations

- No Level 2 Score Modifier data has been seeded yet.
- No scoring engine or score-calculation service has been implemented.
- No Assessment structural data entry has been designed or implemented yet.
- No Assessment Filament Resource changes were made.
- No GIS, findings, photos, roles, permissions, or package changes were made.

### Next Approved Development Step

```text
Assessment Structural Data design
```

Do not proceed to the next development task until explicitly approved.

---

---

## Assessment Building Snapshot Structure Log

### Current Phase

```text
Assessment Historical Data / Building Snapshot Structure
```

### Files Created / Modified

- `app/Models/AssessmentBuildingSnapshot.php`
- `database/migrations/2026_09_12_010000_create_assessment_building_snapshots_table.php`
- `app/Models/Assessment.php`
- `DOCUMENTATION.md`

### Structure Created

Created the `AssessmentBuildingSnapshot` model and `assessment_building_snapshots` table to preserve building registry details as assessment-specific historical data.

Approved snapshot fields created:

```text
id
assessment_id
building_code_snapshot
building_name_snapshot
owner_or_responsible_office_snapshot
address_snapshot
barangay_snapshot
latitude_snapshot
longitude_snapshot
primary_occupancy_snapshot
number_of_storeys_snapshot
year_built_snapshot
approximate_floor_area_snapshot
building_permit_number_snapshot
occupancy_permit_number_snapshot
property_reference_no_snapshot
created_at
updated_at
```

### Relationship Structure

- `AssessmentBuildingSnapshot` belongs to `Assessment`.
- `Assessment` has one `AssessmentBuildingSnapshot` through `buildingSnapshot()`.
- `assessment_id` is required and constrained to `assessments.id`.
- `assessment_id` has a unique constraint to enforce one snapshot per assessment.
- The child snapshot uses `cascadeOnDelete()` so a deleted assessment does not leave an orphaned snapshot record.

### Field Type Notes

- `latitude_snapshot` mirrors Building Registry latitude: `decimal(10, 7)`.
- `longitude_snapshot` mirrors Building Registry longitude: `decimal(10, 7)`.
- `approximate_floor_area_snapshot` mirrors Building Registry floor area: `decimal(12, 2)`.
- `number_of_storeys_snapshot` and `year_built_snapshot` use nullable unsigned small integers.

### Historical Preservation Rule

The snapshot table is intended to preserve the building details captured for a specific assessment. Future changes to the Building Registry must not automatically overwrite stored assessment snapshots.

### Implementation Boundaries

- No automatic snapshot copying was implemented yet.
- No existing Building records were modified.
- No Assessment Filament Resource changes were made.
- No structural details were implemented.
- No scoring logic was implemented.
- No findings, photos, GIS, roles, permissions, packages, or workflow actions were added.

### Migration Result

```text
2026_09_12_010000_create_assessment_building_snapshots_table ... DONE
```

Migration status confirms the snapshot migration has run:

```text
2026_09_12_010000_create_assessment_building_snapshots_table [9] Ran
```

### Checks / Tests Performed

- `php -l app\Models\AssessmentBuildingSnapshot.php`
- `php -l app\Models\Assessment.php`
- `php -l database\migrations\2026_09_12_010000_create_assessment_building_snapshots_table.php`
- `php artisan migrate`
- `php artisan test`
- `php artisan migrate:status`

Latest test result:

```text
Tests: 2 passed (2 assertions)
```

### Errors Encountered and Resolution

- The sandbox helper intermittently failed to acquire its lock directory during file reads and edits.
- Resolution: used approved elevated command execution for required reads, scoped file writes, syntax checks, migration, tests, and migration status checks.
- `apply_patch` was blocked by the sandbox helper while creating/updating files.
- Resolution: used narrow PowerShell writes for the declared files only.
- The first automated insertion attempt for the `Assessment` relationship had line-ending/formatting issues.
- Resolution: rewrote `Assessment.php` with the same existing behavior plus the declared `buildingSnapshot()` relationship, then verified PHP syntax successfully.
- No Laravel migration, test, or migration-status errors were encountered.

### Current Limitations

- Automatic snapshot creation when a new assessment is created has not been implemented yet.
- Assessment forms do not display or manage snapshot data yet.
- Snapshot records are not backfilled for existing assessments.
- Structural assessment details and scoring are still not implemented.

### Next Approved Development Step

```text
automatic snapshot creation when a new assessment is created
```

Do not proceed to the next development task until explicitly approved.

---

---

## Automatic Assessment Building Snapshot Creation Log

### Current Phase

```text
Assessment Historical Data / Automatic Building Snapshot Creation
```

### Files Created / Modified

- `app/Models/Assessment.php`
- `tests/Feature/AssessmentBuildingSnapshotTest.php`
- `DOCUMENTATION.md`

A temporary rollback verification script, `_tmp_snapshot_verify.php`, was created for safe database verification and removed after the checks.

### Implementation Completed

Automatic building snapshot creation is now implemented when a new `Assessment` is created.

The implementation uses the existing `Assessment` model lifecycle:

- `creating`: preserves existing assessment number generation.
- `created`: calls `createBuildingSnapshot()` after the assessment exists.

The snapshot is created from the `Building` record linked by `assessment.building_id`.

### Fields Copied

The automatic snapshot copies these Building Registry values into the assessment snapshot:

```text
building_code -> building_code_snapshot
building_name -> building_name_snapshot
owner_or_responsible_office -> owner_or_responsible_office_snapshot
address -> address_snapshot
barangay -> barangay_snapshot
latitude -> latitude_snapshot
longitude -> longitude_snapshot
primary_occupancy -> primary_occupancy_snapshot
number_of_storeys -> number_of_storeys_snapshot
year_built -> year_built_snapshot
approximate_floor_area -> approximate_floor_area_snapshot
building_permit_number -> building_permit_number_snapshot
occupancy_permit_number -> occupancy_permit_number_snapshot
property_reference_no -> property_reference_no_snapshot
```

### Historical Preservation Rule

Existing snapshots are not automatically updated when Building Registry data changes, when an assessment changes, or when an assessment is viewed or edited.

A reassessment creates a new Assessment record and therefore receives its own new snapshot. Older assessment snapshots are not reused or overwritten.

### Duplicate Prevention

- The automatic creation path checks for an existing snapshot before creating one.
- The existing unique `assessment_id` constraint remains the final database protection against duplicate snapshots.

### Implementation Boundaries

- No database schema changes were made.
- No existing assessments were backfilled.
- No Building records are modified while creating snapshots.
- No Assessment Filament Resource redesign was made.
- No structural details were implemented.
- No scoring logic was implemented.
- No FEMA reference data was modified.
- No findings, photos, GIS, roles, permissions, packages, or workflow actions were added.

### Automated Test Verification

Added `tests/Feature/AssessmentBuildingSnapshotTest.php` to verify:

- creating Assessment A creates exactly one snapshot;
- snapshot fields match Building values at Assessment A creation time;
- assessment number generation still works;
- calling the snapshot creation helper again does not create a duplicate;
- Building Registry updates after Assessment A do not change Assessment A snapshot;
- creating Assessment B for the same Building creates a new snapshot;
- Assessment B snapshot contains the newer Building values;
- Assessment A snapshot remains unchanged;
- Building update behavior still works inside the verification flow.

Focused test result:

```text
Tests: 1 passed (34 assertions)
```

Full test result:

```text
Tests: 3 passed (36 assertions)
```

### Safe Database Verification

A rollback verification script created temporary Building and Assessment records inside a database transaction, verified the behavior, and rolled the transaction back.

Verification result:

```text
assessment_a_snapshot_count=1
assessment_b_snapshot_count=1
assessment_a_number_generated=yes
assessment_b_number_generated=yes
duplicate_after_manual_ensure_count=1
building_crud_update_visible=Snapshot Verify Updated Building
snapshot_a_original_name=Snapshot Verify Original Building
snapshot_a_original_barangay=Verification Barangay A
snapshot_a_original_latitude=14.1111111
snapshot_a_original_floor_area=2222.25
snapshot_b_new_name=Snapshot Verify Updated Building
snapshot_b_new_barangay=Verification Barangay B
snapshot_b_new_latitude=14.2222222
snapshot_b_new_floor_area=3333.75
snapshot_ids_are_distinct=yes
mismatch_count=0
transaction_rolled_back=yes
```

### Checks / Tests Performed

- `php -l app\Models\Assessment.php`
- `php -l app\Models\AssessmentBuildingSnapshot.php`
- `php -l tests\Feature\AssessmentBuildingSnapshotTest.php`
- `php -l _tmp_snapshot_verify.php`
- `php artisan test tests\Feature\AssessmentBuildingSnapshotTest.php`
- `php artisan test`
- `php artisan migrate:status`
- Safe rollback database verification for snapshot preservation, reassessment behavior, duplicate prevention, assessment number generation, and Building update behavior
- Confirmed `_tmp_snapshot_verify.php` was removed after verification.

### Errors Encountered and Resolution

- The sandbox helper continued to require elevated execution for scoped reads, writes, and checks.
- Resolution: used approved elevated command execution for required reads, scoped file writes, syntax checks, tests, migration status, and rollback verification.
- No Laravel syntax, automated test, migration-status, or snapshot verification errors were encountered.

### Current Limitations

- Existing assessments were not backfilled with snapshots.
- Snapshot data is not displayed in the Assessment Filament Resource yet.
- Structural detail records have not been designed or implemented yet.
- Scoring logic has not been implemented yet.

### Next Approved Development Step

```text
Assessment Structural Detail model + migration design/implementation
```

Do not proceed to the next development task until explicitly approved.

---
## Latest Project Status

### Current Phase

```text
Assessment Historical Data / Automatic Building Snapshot Creation
```

### Next Approved Development Step

```text
Assessment Structural Detail model + migration design/implementation
```

---

## Assessment Structural Detail Data Layer Log

### Current Phase

```text
Assessment Structural Detail Data Layer
```

### Files Created / Modified

- `database/migrations/2026_09_20_020000_create_assessment_structural_details_table.php`
- `app/Models/AssessmentStructuralDetail.php`
- `app/Models/Assessment.php`
- `tests/Feature/AssessmentStructuralDetailTest.php`
- `STATUS.md`
- `DOCUMENTATION.md`

### Implementation Completed

Created the Assessment Structural Detail data layer for assessment-specific structural and FEMA input data.

Implemented the `assessment_structural_details` table with one structural detail record per Assessment.

The table intentionally uses `assessments.fema_version_id` as the single source of truth for FEMA version selection. No independently editable `fema_version_id` was added to `assessment_structural_details`.

The implementation is limited to structural/FEMA input data only. Scoring-result fields were intentionally not implemented in this task.

### Final Migration Schema

```text
id
assessment_id
fema_building_type_id
fema_version_code_snapshot
fema_version_title_snapshot
fema_version_edition_snapshot
fema_building_type_code_snapshot
fema_building_type_name_snapshot
material_category_snapshot
structural_system_snapshot
seismicity_level
soil_type
vertical_irregularity_type
plan_irregularity_type
has_pre_code_condition
has_post_benchmark_condition
site_condition_notes
structural_observation_notes
created_at
updated_at
```

### Foreign Keys / Constraints / Indexes

- `assessment_id` is required, unique, constrained to `assessments.id`, and uses `cascadeOnDelete()`.
- `fema_building_type_id` is nullable, constrained to `fema_building_types.id`, and uses `restrictOnDelete()`.
- Indexes were added for:
  - `fema_building_type_id`
  - `seismicity_level`
  - `soil_type`

### Model Relationships

`Assessment` now has:

```php
public function femaVersion(): BelongsTo
public function structuralDetail(): HasOne
```

`AssessmentStructuralDetail` has:

```php
public function assessment(): BelongsTo
public function femaBuildingType(): BelongsTo
```

### Casts

`AssessmentStructuralDetail` casts:

```php
has_pre_code_condition => boolean
has_post_benchmark_condition => boolean
```

The nullable boolean columns preserve unknown values as `null` for incomplete Draft assessments.

### Structural Input Values

Vertical irregularity supported values are represented in the model as:

```text
none
moderate
severe
```

Plan irregularity supported values are represented in the model as:

```text
none
irregular
```

Soil type supported values are aligned with existing FEMA Level 1 score modifier codes:

```text
SOIL_AB
SOIL_E_LOW_RISE
SOIL_E_MID_HIGH_RISE
```

Seismicity level supported values are represented in the model as:

```text
Low
Moderate
Moderately High
High
Very High
```

### Snapshot Behavior

When an Assessment Structural Detail is saved:

- FEMA version descriptive values are snapshotted from the related Assessment's `fema_version_id`:
  - `code`
  - `title`
  - `edition`
- FEMA Building Type descriptive values are snapshotted from the selected `fema_building_type_id`:
  - `code`
  - `name`
  - `material_category`
  - `structural_system`

This preserves the descriptive reference context used at assessment time without duplicating Building Registry historical fields already stored in `assessment_building_snapshots`.

### Intentional Exclusions

This task did not implement:

- Filament UI for structural details
- FEMA scoring engine
- Basic Score calculation
- Score modifier application
- Minimum-score handling
- Final Score calculation
- Workflow/status actions
- Findings, photos, reports, roles/permissions, audit trail, dashboard, GIS, or recommendations

These scoring-related fields were intentionally excluded:

- `fema_basic_score_id`
- `basic_score_snapshot`
- `fema_minimum_score_id`
- `minimum_score_snapshot`
- modifier totals
- calculated score
- final score
- applied modifier snapshots
- calculation trace

Future calculations must use `assessment_building_snapshots` for historical Building Registry context such as number of storeys.

### Tests Added

Added `tests/Feature/AssessmentStructuralDetailTest.php` covering:

- structural detail creation
- Assessment to structural detail relationship
- structural detail to Assessment relationship
- duplicate structural detail rejection for one Assessment
- FEMA Building Type relationship
- nullable Draft fields
- nullable boolean unknown/null preservation
- Assessment delete cascading to structural detail
- referenced FEMA Building Type delete restriction
- FEMA version and building type snapshot population
- existing Assessment Building Snapshot behavior

### Checks / Tests Performed

- `php -l app\Models\Assessment.php`
- `php -l app\Models\AssessmentStructuralDetail.php`
- `php -l database\migrations\2026_09_20_020000_create_assessment_structural_details_table.php`
- `php -l tests\Feature\AssessmentStructuralDetailTest.php`
- `php artisan migrate`
- `php artisan test tests\Feature\AssessmentStructuralDetailTest.php`
- `php artisan test tests\Feature\AssessmentBuildingSnapshotTest.php`
- `php artisan test`
- `php artisan migrate:status`

Focused structural detail test result:

```text
Tests: 6 passed (26 assertions)
```

Existing building snapshot focused test result:

```text
Tests: 1 passed (34 assertions)
```

Full test result:

```text
Tests: 9 passed (62 assertions)
```

### Migration Result

```text
2026_09_20_020000_create_assessment_structural_details_table [10] Ran
```

### Issues Encountered and Resolution

- The sandbox helper blocked `apply_patch` and intermittently blocked sandboxed command execution.
- Resolution: used narrow elevated PowerShell writes only for the declared files, then verified all changed PHP files with syntax checks and tests.
- The first automated relationship insertion into `Assessment.php` did not land; the file was reread, corrected, and verified before running tests.
- No Laravel migration, relationship, syntax, or PHPUnit failures remained after correction.

### Current Limitations

- Assessment Structural Detail has no Filament UI yet.
- Structural detail data is not displayed on Assessment pages yet.
- FEMA scoring, Basic Score lookup, modifier application, minimum-score handling, and final-score calculation are not implemented yet.
- Structural Detail reference snapshots are synchronized on save, but completed-assessment locking/review workflow is not implemented yet.

### Next Approved Development Step

```text
Assessment Structural Detail Filament UI
```

Do not proceed to the next development task until explicitly approved.

---

## Latest Project Status

### Current Phase

```text
Assessment Structural Detail Data Layer
```

### Next Approved Development Step

```text
Assessment Structural Detail Filament UI
```
---

## Assessment Structural Detail Filament UI Log

### Current Phase

```text
Assessment Structural Detail Filament UI
```

### Files Created / Modified

- `app/Filament/Resources/Assessments/AssessmentResource.php`
- `app/Filament/Resources/Assessments/Pages/EditAssessment.php`
- `tests/Feature/AssessmentStructuralDetailFilamentTest.php`
- `STATUS.md`
- `DOCUMENTATION.md`

### Implementation Completed

Integrated Assessment Structural Detail fields into the existing Assessment Filament Resource.

This implementation is UI-only. It does not implement FEMA scoring, Basic Score calculation, modifier application, final score calculation, workflow/status actions, or completion validation.

### UI Sections Added / Updated

The Assessment form now uses these organized sections:

- Assessment Information
- FEMA Classification
- Site / Soil
- Irregularities
- Code Conditions
- Structural Notes
- Status and Remarks

The Assessment view/infolist now displays:

- Assessment Information
- Structural Detail
- Structural Reference Snapshots
- Status and Remarks

### Relationship-Based Persistence

Structural detail fields are persisted through the existing one-to-one relationship:

```php
Assessment::structuralDetail()
```

Filament `Section::relationship('structuralDetail')` is used for structural detail groups. The relationship section condition creates or updates `assessment_structural_details` only when relevant structural input is supplied, so creating an Assessment with no structural detail still works and does not create an empty structural detail record.

### FEMA Version Behavior

The authoritative FEMA version field remains:

```text
assessments.fema_version_id
```

The Assessment form now includes a FEMA Version selector using existing `FemaVersion` records. Labels display code, title, and edition.

No independently editable FEMA version field was added to `assessment_structural_details`.

When an Assessment is edited and `fema_version_id` changes, `EditAssessment::afterSave()` refreshes the structural detail FEMA version snapshot after the parent Assessment has been saved. This is needed because Filament saves HasOne relationship data before the parent edit record is fully refreshed.

### FEMA Building Type Behavior

The Structural Classification section includes:

- FEMA Building Type
- Seismicity Level

The FEMA Building Type field persists to:

```text
structuralDetail.fema_building_type_id
```

Labels display:

```text
code - name
```

When `fema_version_id` is available in the form state, the building type options are filtered by that FEMA version. Otherwise, active FEMA building types are shown ordered by code.

The `AssessmentStructuralDetail` model remains responsible for snapshotting selected FEMA Building Type descriptive values:

- code
- name
- material category
- structural system

### Site / Soil

The Site / Soil section includes:

- Soil Type
- Site Condition Notes

Soil Type options use existing FEMA Level 1 score modifier codes already present in the seed data:

```text
SOIL_AB
SOIL_E_LOW_RISE
SOIL_E_MID_HIGH_RISE
```

### Irregularities

Vertical Irregularity uses stable stored values:

```text
none
moderate
severe
```

Plan Irregularity uses stable stored values:

```text
none
irregular
```

### Nullable Boolean Code Conditions

The Code Conditions section includes:

- Pre-Code Condition
- Post-Benchmark Condition

Both fields use Filament `Select::boolean()` with an Unknown placeholder so all three states are preserved:

- Yes = `true`
- No = `false`
- Unknown / Not Yet Assessed = `null`

A normal binary toggle was not used.

### Structural Notes

The Structural Notes section includes:

- Structural Observation Notes

Textareas are used for note fields.

### Snapshot Display Behavior

Snapshot fields are displayed as disabled, non-dehydrated form fields and as read-only infolist entries.

The form/view display includes FEMA Version snapshots:

- code
- title
- edition

The form/view display includes FEMA Building Type snapshots:

- code
- name
- material category
- structural system

Snapshot fields are not user-controlled. Tests confirm that user-submitted snapshot values are not persisted and model snapshot synchronization remains authoritative.

### Table Behavior

The main Assessment table was kept readable. Only two optional/toggleable structural summary columns were added:

- FEMA Type
- Seismicity

Both are hidden by default.

### Tests Added

Added `tests/Feature/AssessmentStructuralDetailFilamentTest.php` covering:

- Assessment edit form loads successfully with no structural detail
- existing structural detail loads correctly
- structural detail can be created from the Assessment create form
- structural detail can be updated from the Assessment edit form
- nullable boolean state can remain `null`
- FEMA version is saved on Assessment and is not duplicated as an editable structural-detail foreign key
- FEMA Building Type selection persists
- snapshot fields are not directly user-controlled
- changing Assessment FEMA Version refreshes structural detail FEMA version snapshots on the next save
- existing Assessment creation behavior still works without structural detail

### Checks / Tests Performed

- `php -l app\Filament\Resources\Assessments\AssessmentResource.php`
- `php -l app\Filament\Resources\Assessments\Pages\EditAssessment.php`
- `php -l tests\Feature\AssessmentStructuralDetailFilamentTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailFilamentTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailTest.php`
- `php artisan test tests\Feature\AssessmentBuildingSnapshotTest.php`
- `php artisan test`

Focused Filament UI test result:

```text
Tests: 6 passed (54 assertions)
```

Existing structural detail data-layer test result:

```text
Tests: 6 passed (26 assertions)
```

Existing building snapshot focused test result:

```text
Tests: 1 passed (34 assertions)
```

Full test result:

```text
Tests: 15 passed (116 assertions)
```

### Issues Encountered and Resolution

- The initial test helper destructured an optional alternate FEMA Building Type when it was not requested.
  - Resolution: adjusted the helper to read the optional value safely.
- The first nullable boolean implementation did not reliably show the persisted `false` state in the edit form.
  - Resolution: switched to Filament `Select::boolean('Yes', 'No', 'Unknown / Not Yet Assessed')`, which preserved `true`, `false`, and `null` cleanly.
- Changing Assessment FEMA Version did not refresh existing structural detail version snapshots during the same edit save because of Filament parent/relationship save order.
  - Resolution: added `EditAssessment::afterSave()` to refresh and save existing structural detail reference snapshots after the Assessment record has been saved.

### Current Limitations

- FEMA scoring engine is not implemented.
- Basic Score lookup and Level 1 modifier lookup/application are not implemented.
- Minimum-score handling and final score calculation are not implemented.
- Workflow/completion validation is not implemented; Draft structural detail fields remain optional.
- Findings, photos, recommendations, reports, roles/permissions, audit trail, dashboard, and GIS are not implemented.

### Next Approved Development Step

```text
FEMA Basic Score and Level 1 modifier lookup integration
```

Do not proceed to the next development task until explicitly approved.

---

## Latest Project Status

### Current Phase

```text
Assessment Structural Detail Filament UI
```

### Next Approved Development Step

```text
FEMA Basic Score and Level 1 modifier lookup integration
```

---

## FEMA Basic Score and Level 1 Modifier Lookup Integration Log

### Current Phase

```text
FEMA Basic Score and Level 1 Modifier Lookup Integration
```

### Files Created / Modified

- `app/Services/Fema/FemaLevelOneScoreLookup.php`
- `tests/Feature/FemaLevelOneScoreLookupTest.php`
- `STATUS.md`
- `DOCUMENTATION.md`

### Implementation Completed

Implemented a dedicated FEMA Level 1 lookup service for calculation preparation.

The service is intentionally pure lookup/preparation logic. It does not persist scoring results, does not implement final workflow/status completion logic, does not implement Level 2 modifiers, and does not add reporting or Filament scoring UI.

### Service Design

Created:

```text
App\Services\Fema\FemaLevelOneScoreLookup
```

The service accepts an `Assessment` and returns a structured result containing:

- readiness flag
- missing input list
- reference lookup errors
- resolved Basic Score reference and value
- resolved Minimum Score / S_MIN reference and value
- resolved Level 1 modifier references
- modifier codes
- modifier values
- Level 1 modifier total

### Lookup Inputs

The lookup service uses these existing assessment inputs:

- `Assessment.fema_version_id`
- `AssessmentStructuralDetail.fema_building_type_id`
- `AssessmentStructuralDetail.seismicity_level`
- `AssessmentStructuralDetail.soil_type`
- `AssessmentStructuralDetail.vertical_irregularity_type`
- `AssessmentStructuralDetail.plan_irregularity_type`
- `AssessmentStructuralDetail.has_pre_code_condition`
- `AssessmentStructuralDetail.has_post_benchmark_condition`
- `AssessmentBuildingSnapshot.number_of_storeys_snapshot`

The service does not use the live Building Registry for storey-based soil modifier handling.

### Basic Score Lookup

Basic Score is resolved from `fema_basic_scores` using:

```text
fema_version_id
fema_building_type_id
seismicity_level
is_active = true
```

### Minimum Score Lookup

Minimum Score / `S_MIN` is resolved from `fema_minimum_scores` using:

```text
fema_version_id
fema_building_type_id
seismicity_level
is_active = true
```

### Level 1 Modifier Mappings

The service maps structural detail inputs to the existing FEMA modifier codes:

- `vertical_irregularity_type = severe` -> `VERTICAL_SEVERE`
- `vertical_irregularity_type = moderate` -> `VERTICAL_MODERATE`
- `plan_irregularity_type = irregular` -> `PLAN_IRREGULARITY`
- `has_pre_code_condition === true` -> `PRE_CODE`
- `has_post_benchmark_condition === true` -> `POST_BENCHMARK`
- `soil_type = SOIL_AB` -> `SOIL_AB`
- `soil_type = SOIL_E_LOW_RISE` or `SOIL_E_MID_HIGH_RISE` -> resolved using historical snapshot storeys:
  - 1 to 3 storeys -> `SOIL_E_LOW_RISE`
  - more than 3 storeys -> `SOIL_E_MID_HIGH_RISE`

### Incomplete Draft Handling

Incomplete Draft assessments are handled safely.

When required inputs are missing, the service returns `ready = false` and reports missing inputs instead of throwing unnecessary runtime errors.

Required lookup inputs are:

- `assessment.fema_version_id`
- `assessment.structuralDetail`
- `structuralDetail.fema_building_type_id`
- `structuralDetail.seismicity_level`

Historical storey count is required only when Soil E modifier mapping is needed.

### Reference Data Error Handling

The service detects and reports:

- missing Basic Score rows
- missing Minimum Score rows
- missing selected modifier rows
- ambiguous Basic Score rows
- ambiguous Minimum Score rows
- ambiguous selected modifier rows
- selected modifier rows marked not applicable
- unsupported soil type values

It does not silently choose an arbitrary reference row when multiple matches are found.

### Tests Added

Added `tests/Feature/FemaLevelOneScoreLookupTest.php` covering:

- correct Basic Score lookup
- correct Minimum Score lookup
- no irregularity modifiers
- moderate vertical irregularity modifier lookup
- severe vertical irregularity modifier lookup
- plan irregularity modifier lookup
- pre-code condition modifier lookup
- post-benchmark condition modifier lookup
- combined Level 1 modifiers and modifier total
- soil modifier lookup using historical snapshot storeys
- missing required structural inputs
- missing FEMA reference data
- duplicate/ambiguous reference match handling

### Checks / Tests Performed

- `php -l app\Services\Fema\FemaLevelOneScoreLookup.php`
- `php -l tests\Feature\FemaLevelOneScoreLookupTest.php`
- `php artisan test tests\Feature\FemaLevelOneScoreLookupTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailFilamentTest.php`
- `php artisan test tests\Feature\AssessmentBuildingSnapshotTest.php`
- `php artisan test`

Focused FEMA lookup test result:

```text
Tests: 12 passed (48 assertions)
```

Existing structural detail data-layer test result:

```text
Tests: 6 passed (26 assertions)
```

Existing structural detail Filament test result:

```text
Tests: 6 passed (54 assertions)
```

Existing building snapshot focused test result:

```text
Tests: 1 passed (34 assertions)
```

Full test result:

```text
Tests: 27 passed (164 assertions)
```

### Issues Encountered and Resolution

- The sandbox helper intermittently failed to acquire its Windows lock directory during file reads, syntax checks, and documentation edits.
  - Resolution: used narrow elevated reads/writes/checks for the declared files and verification commands.
- No FEMA reference-data mismatches were found by the new lookup tests.
- No Laravel syntax or PHPUnit failures remained after implementation.

### Current Limitations

- Level 2 modifier lookup/application is not implemented yet.
- Final FEMA score calculation is not implemented yet.
- Minimum-score enforcement against a calculated score is not implemented yet.
- Persisted Basic Score snapshots, Minimum Score snapshots, applied modifier snapshots, modifier totals, final score, and calculation trace are not implemented yet.
- No Filament scoring display was added in this task.
- Workflow/status completion validation remains pending.

### Next Approved Development Step

```text
Persisted scoring snapshot structure and calculation trace design
```

Do not proceed to the next development task until explicitly approved.

---

## Latest Project Status

### Current Phase

```text
FEMA Basic Score and Level 1 Modifier Lookup Integration
```

### Next Approved Development Step

```text
Persisted scoring snapshot structure and calculation trace design
```


---

## Persisted FEMA Level 1 Scoring Snapshots and Calculation Trace Log

### Current Phase

```text
Persisted FEMA Level 1 Scoring Snapshots and Calculation Trace
```

### Files Created / Modified

- `database/migrations/2026_09_21_000000_add_level_one_scoring_snapshots_to_assessment_structural_details_table.php`
- `app/Models/AssessmentStructuralDetail.php`
- `app/Services/Fema/FemaLevelOneScoreSnapshotter.php`
- `tests/Feature/FemaLevelOneScoreSnapshotTest.php`
- `STATUS.md`
- `DOCUMENTATION.md`

### Implementation Completed

Implemented persisted FEMA Level 1 scoring snapshots and a readable calculation trace.

The implementation keeps Level 1 scoring snapshots close to `AssessmentStructuralDetail`, because the existing architecture stores the assessment-specific FEMA structural inputs there and already has a one-to-one relationship with `Assessment`.

No Level 2 scoring, workflow/status completion logic, report generation, or major Filament redesign was implemented in this task.

### Database Changes

Added these nullable columns to `assessment_structural_details`:

```text
fema_basic_score_id
basic_score_snapshot
fema_minimum_score_id
minimum_score_snapshot
level_one_modifier_total_snapshot
calculated_level_one_score
final_level_one_score
applied_level_one_modifiers_snapshot
level_one_calculation_trace
level_one_calculated_at
```

### Foreign Keys / Indexes

- `fema_basic_score_id` references `fema_basic_scores.id` and uses `restrictOnDelete()`.
- `fema_minimum_score_id` references `fema_minimum_scores.id` and uses `restrictOnDelete()`.
- Indexes were added for:
  - `fema_basic_score_id`
  - `fema_minimum_score_id`
  - `level_one_calculated_at`

### Model Updates

Updated `AssessmentStructuralDetail` fillable fields and casts.

New casts:

```text
basic_score_snapshot => decimal:2
minimum_score_snapshot => decimal:2
level_one_modifier_total_snapshot => decimal:2
calculated_level_one_score => decimal:2
final_level_one_score => decimal:2
applied_level_one_modifiers_snapshot => array
level_one_calculation_trace => array
level_one_calculated_at => datetime
```

New relationships:

```php
public function femaBasicScore(): BelongsTo
public function femaMinimumScore(): BelongsTo
```

### Snapshot Service

Created:

```text
App\Services\Fema\FemaLevelOneScoreSnapshotter
```

The service uses the existing `FemaLevelOneScoreLookup` result. It only persists when the lookup result is ready.

If the lookup is incomplete or has reference errors, the service returns `persisted = false` and does not write scoring snapshots.

### Calculation Rule

The Level 1 score is calculated as:

```text
calculated_level_one_score = basic_score + level_one_modifier_total
```

Then the FEMA minimum score is applied as a floor:

```text
final_level_one_score = max(calculated_level_one_score, minimum_score)
```

### Historical Integrity

The persisted result does not depend on live FEMA reference values for future display.

The implementation snapshots:

- Basic Score reference ID
- Basic Score numeric value
- Minimum Score reference ID
- Minimum Score numeric value
- Level 1 modifier total
- calculated Level 1 score before minimum-score handling
- final Level 1 score after minimum-score handling
- applied modifier details
- calculation trace metadata
- calculation timestamp

Applied modifier snapshots include:

```text
reference_id
fema_version_id
fema_building_type_id
seismicity_level
assessment_level
category
code
name
value
is_applicable
```

### Calculation Trace

The JSON calculation trace stores:

- assessment ID
- assessment number
- calculated timestamp
- FEMA version and building type input context
- seismicity, soil, irregularity, and code-condition inputs
- historical storey count from `assessment_building_snapshots`
- Basic Score reference and value
- applied modifier snapshots
- modifier total
- calculated Level 1 score
- Minimum Score reference and value
- whether the minimum-score floor was applied
- final Level 1 score
- formula text

### Tests Added

Added `tests/Feature/FemaLevelOneScoreSnapshotTest.php` covering:

- successful Level 1 calculation persistence
- persisted Basic Score and Minimum Score snapshots
- persisted applied modifier snapshots
- readable calculation trace
- `level_one_calculated_at` timestamp
- historical stability after FEMA reference values are changed later
- minimum-score floor handling
- incomplete lookup handling without persistence

### Checks / Tests Performed

- `php -l app\Models\AssessmentStructuralDetail.php`
- `php -l app\Services\Fema\FemaLevelOneScoreSnapshotter.php`
- `php -l database\migrations\2026_09_21_000000_add_level_one_scoring_snapshots_to_assessment_structural_details_table.php`
- `php -l tests\Feature\FemaLevelOneScoreSnapshotTest.php`
- `php artisan migrate`
- `php artisan test tests\Feature\FemaLevelOneScoreSnapshotTest.php`
- `php artisan test tests\Feature\FemaLevelOneScoreLookupTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailFilamentTest.php`
- `php artisan test tests\Feature\AssessmentBuildingSnapshotTest.php`
- `php artisan test`
- `php artisan migrate:status`

Focused Level 1 scoring snapshot test result:

```text
Tests: 4 passed (21 assertions)
```

Existing FEMA lookup test result:

```text
Tests: 12 passed (48 assertions)
```

Existing structural detail data-layer test result:

```text
Tests: 6 passed (26 assertions)
```

Existing structural detail Filament test result:

```text
Tests: 6 passed (54 assertions)
```

Existing building snapshot focused test result:

```text
Tests: 1 passed (34 assertions)
```

Full test result:

```text
Tests: 31 passed (185 assertions)
```

### Migration Result

```text
2026_09_21_000000_add_level_one_scoring_snapshots_to_assessment_structural_details_table [11] Ran
```

### Issues Encountered and Resolution

- The sandbox helper intermittently failed to acquire its Windows lock directory during reads and edits.
  - Resolution: used narrow elevated reads/writes/checks for declared files and verification commands.
- No FEMA reference-data issues were found by the scoring snapshot tests.
- No Laravel migration, syntax, or PHPUnit failures remained after implementation.

### Current Limitations

- Level 1 scoring snapshots are persisted by service only; no Filament calculate action or read-only scoring display has been added yet.
- Level 2 modifier lookup/application is not implemented yet.
- Final scoring workflow/status completion validation is not implemented yet.
- Report generation is not implemented yet.
- Existing assessments were not backfilled with Level 1 score snapshots.

### Next Approved Development Step

```text
Assessment Filament read-only Level 1 score display and explicit calculate/refresh action
```

Do not proceed to the next development task until explicitly approved.

---

## Latest Project Status

### Current Phase

```text
Persisted FEMA Level 1 Scoring Snapshots and Calculation Trace
```

### Next Approved Development Step

```text
Assessment Filament read-only Level 1 score display and explicit calculate/refresh action
```


---

## Assessment Filament Level 1 Score Summary and Calculate Action Log

### Current Phase

```text
Assessment Filament Level 1 Score Summary and Calculate Action
```

### Files Created / Modified

- `app/Filament/Resources/Assessments/AssessmentResource.php`
- `app/Filament/Resources/Assessments/Pages/EditAssessment.php`
- `app/Filament/Resources/Assessments/Pages/ViewAssessment.php`
- `app/Filament/Resources/Assessments/Pages/Concerns/CalculatesLevelOneScore.php`
- `app/Models/AssessmentStructuralDetail.php`
- `tests/Feature/AssessmentLevelOneScoreFilamentTest.php`
- `STATUS.md`
- `DOCUMENTATION.md`

### Implementation Completed

Implemented the Assessment Filament read-only FEMA Level 1 Score Summary and explicit Calculate / Refresh Score action.

This task is Filament UI integration only. It does not implement Level 2 scoring, workflow/status completion locking, automatic recalculation on every save, or report generation.

### UI Placement

Added a dedicated read-only section:

```text
FEMA Level 1 Score Summary
```

The section appears in the Assessment edit form and Assessment view/infolist after the structural/FEMA sections and before Status and Remarks.

### Score Summary Display

The summary uses persisted score snapshot values from `AssessmentStructuralDetail`, not live FEMA reference values.

Displayed values include:

- Basic Score
- Applied Level 1 Modifiers
- Level 1 Modifier Total
- Calculated Level 1 Score
- Minimum Score
- Final Level 1 Score
- Last Calculated At
- Compact Calculation Details

If no Level 1 score has been calculated, the summary displays:

```text
No Level 1 score has been calculated yet.
```

### Applied Modifier Display

Applied modifiers are rendered from `applied_level_one_modifiers_snapshot` only.

Each applied modifier is shown with:

```text
code - name - signed value
```

Example format:

```text
VERTICAL_MODERATE - Moderate Vertical Irregularity - -0.60
POST_BENCHMARK - Post-Benchmark - +2.40
```

Unused modifiers are not displayed.

### Calculation Details Display

The raw JSON trace is not exposed directly.

The visible calculation details summarize the persisted trace as:

```text
Basic Score + Level 1 Modifier Total = Calculated Score
Final Level 1 Score = max(Calculated Score, Minimum Score)
```

### Explicit Calculate / Refresh Action

Added a shared page action through:

```text
App\Filament\Resources\Assessments\Pages\Concerns\CalculatesLevelOneScore
```

The action is available on both the Assessment edit and view pages.

The label is dynamic:

- `Calculate Level 1 Score` when no persisted Level 1 score exists
- `Refresh Level 1 Score` when a persisted Level 1 score exists

The action delegates calculation and persistence to:

```text
App\Services\Fema\FemaLevelOneScoreSnapshotter
```

No scoring logic was duplicated inside Filament resources or pages.

### Action Behavior

When the action runs:

- if lookup/calculation is ready:
  - persists or updates the Level 1 score snapshot
  - refreshes the page record/form state
  - shows a success notification
- if required inputs are missing:
  - does not persist a completed score
  - shows a warning notification listing missing inputs
- if lookup errors exist:
  - does not persist a completed score
  - shows a warning notification listing lookup errors
- if an unexpected exception occurs:
  - shows a generic danger notification
  - does not expose internal stack traces to the user

### Read-Only Protection

The Filament UI does not expose editable score fields.

Score snapshot columns were removed from `AssessmentStructuralDetail::$fillable`; the snapshotter writes them using `forceFill()`. This keeps user-submitted form state from mass-assigning persisted score snapshots.

### Recalculation

Recalculation is allowed while the Assessment remains editable/Draft.

The action replaces/updates the existing persisted Level 1 score snapshot using the existing snapshotter behavior.

No status-based locking was added.

### Stale Score Note

Complex stale-score change tracking was not added in this task. The UI provides an explicit Refresh Level 1 Score action after a score exists. More detailed stale detection can be added later if the project introduces input-change tracking timestamps or score invalidation metadata.

### Tests Added

Added `tests/Feature/AssessmentLevelOneScoreFilamentTest.php` covering:

- score summary renders when a persisted score exists
- empty state renders when no score exists
- calculate action calls the score snapshotter
- score values refresh after calculation
- applied modifiers display from persisted snapshots
- missing required inputs do not persist a completed score
- lookup errors do not persist a completed score
- recalculation updates the persisted score
- score fields remain read-only on Assessment form save

### Checks / Tests Performed

- `php -l app\Filament\Resources\Assessments\AssessmentResource.php`
- `php -l app\Filament\Resources\Assessments\Pages\Concerns\CalculatesLevelOneScore.php`
- `php -l app\Filament\Resources\Assessments\Pages\EditAssessment.php`
- `php -l app\Filament\Resources\Assessments\Pages\ViewAssessment.php`
- `php -l app\Models\AssessmentStructuralDetail.php`
- `php -l tests\Feature\AssessmentLevelOneScoreFilamentTest.php`
- `php artisan test tests\Feature\AssessmentLevelOneScoreFilamentTest.php`
- `php artisan test tests\Feature\FemaLevelOneScoreLookupTest.php`
- `php artisan test tests\Feature\FemaLevelOneScoreSnapshotTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailFilamentTest.php`
- `php artisan test tests\Feature\AssessmentBuildingSnapshotTest.php`
- `php artisan test`

Focused Filament score summary and Draft Level 1 E2E test result:

```text
Tests: 10 passed (119 assertions)
```

Existing FEMA lookup test result:

```text
Tests: 12 passed (48 assertions)
```

Existing FEMA score snapshot test result:

```text
Tests: 4 passed (21 assertions)
```

Existing structural detail data-layer test result:

```text
Tests: 6 passed (26 assertions)
```

Existing structural detail Filament test result:

```text
Tests: 6 passed (54 assertions)
```

Existing building snapshot focused test result:

```text
Tests: 1 passed (34 assertions)
```

Full test result:

```text
Tests: 41 passed (304 assertions)
```

### Issues Encountered and Resolution

- Filament `TextEntry` attempted to format JSON array leaves directly when entries pointed at JSON columns.
  - Resolution: changed applied modifier and calculation detail display entries to format from the Assessment record while using existing non-null assessment fields as display anchors.
- Arbitrary display-only state names did not render consistently when no state existed.
  - Resolution: anchored computed read-only entries to existing Assessment fields and formatted from the Assessment record.
- The sandbox helper intermittently failed to acquire its Windows lock directory during reads and edits.
  - Resolution: used narrow elevated reads/writes/checks for declared files and verification commands.

### Current Limitations

- Level 2 modifier lookup/application is not implemented yet.
- Workflow/status completion validation and locking are not implemented yet.
- Final report output is implemented as an HTML/Blade browser-print report; PDF export is not implemented yet.
- Complex stale-score detection is not implemented yet.
- Existing assessments were not backfilled with Level 1 score snapshots.

### Next Approved Development Step

```text
Level 2 modifier lookup/application design
```

Do not proceed to the next development task until explicitly approved.

---

## Latest Project Status

### Current Phase

```text
Assessment Filament Level 1 Score Summary and Calculate Action
```

### Next Approved Development Step

```text
Level 2 modifier lookup/application design
```

---

## FEMA Level 1 Workflow Completion Validation and Completed Assessment Locking Log

### Current Phase

```text
FEMA Level 1 Workflow Completion Validation and Completed Assessment Locking
```

### Files Created / Modified

- `database/migrations/2026_09_29_000000_add_completed_at_to_assessments_table.php`
- `app/Services/Assessments/LevelOneCompletionValidator.php`
- `app/Filament/Resources/Assessments/Pages/Concerns/CompletesAssessment.php`
- `app/Filament/Resources/Assessments/AssessmentResource.php`
- `app/Filament/Resources/Assessments/Pages/EditAssessment.php`
- `app/Filament/Resources/Assessments/Pages/ViewAssessment.php`
- `app/Filament/Resources/Assessments/Pages/Concerns/CalculatesLevelOneScore.php`
- `app/Services/Fema/FemaLevelOneScoreSnapshotter.php`
- `app/Models/Assessment.php`
- `app/Models/AssessmentStructuralDetail.php`
- `app/Models/AssessmentBuildingSnapshot.php`
- `tests/Feature/AssessmentLevelOneCompletionWorkflowTest.php`
- `STATUS.md`
- `DOCUMENTATION.md`

### Implementation Completed

Implemented FEMA Level 1 workflow completion validation and completed-assessment locking using the verified Draft Level 1 workflow as the baseline.

No Level 2 scoring, report generation, recommendation engine, review/approval workflow, or reopen/admin correction workflow was implemented in this task.

### Completion Requirements Enforced

An Assessment can transition to `Completed` only when Level 1 completion validation passes.

Required items include:

- linked Building
- Assessment date
- selected FEMA Version
- Assessment Level equal to `Level 1`
- Assessment Building Snapshot
- Assessment Structural Detail
- FEMA Building Type
- seismicity level
- soil type
- vertical irregularity assessment
- plan irregularity assessment
- explicit Pre-Code condition value, Yes or No
- explicit Post-Benchmark condition value, Yes or No
- persisted Basic Score snapshot
- persisted Minimum Score snapshot
- persisted Level 1 calculation trace
- persisted final Level 1 score
- non-stale Level 1 score snapshot

Pre-Code and Post-Benchmark can remain `Unknown / Not Yet Assessed` while the Assessment is Draft, but completion requires explicit Yes/No values so a completed screening does not contain unresolved code-condition inputs.

### Stale Score Protection

Implemented a simple stale-score rule in:

```text
App\Services\Assessments\LevelOneCompletionValidator
```

The validator reloads current Assessment relationships, then checks that persisted trace inputs still match current scoring inputs for FEMA Version, FEMA Building Type, seismicity, soil, irregularities, Pre-Code, Post-Benchmark, and historical storey snapshot used for Soil E modifier logic.

It also checks relevant Structural Detail and Building Snapshot update times against `level_one_calculated_at` with a small tolerance for same-save timestamp ordering.

If the score is stale, completion is prevented and the user is instructed to refresh the Level 1 score.

### Explicit Completion Action

Added a Filament page action:

```text
Complete Assessment
```

The action validates completion requirements and score freshness, sets `status` to `Completed`, stores `completed_at`, refreshes page/form state, and shows clear success or warning notifications. Raw exceptions are not exposed.

### Completion Timestamp

Added nullable column:

```text
assessments.completed_at
```

Migration applied successfully:

```text
2026_09_29_000000_add_completed_at_to_assessments_table ... DONE
```

### Completed Assessment Locking

Completed assessments are locked for historical review.

Locked through Filament UI:

- Calculate / Refresh Level 1 Score action is disabled
- Complete Assessment action is disabled
- normal Edit action is hidden from the view page
- critical Assessment fields are disabled on the edit page
- critical Structural Detail fields are disabled on the edit page
- persisted score summary remains visible and read-only
- status dropdown cannot be used to select `Completed` during Draft editing

Server-side safeguards were added for critical Assessment fields, Structural Detail, Building Snapshot, and Level 1 score recalculation.

Completed records remain viewable with the full score summary and calculation details.

### Direct Status Editing Protection

Directly setting `status = Completed` now runs the same Level 1 completion validation. Incomplete or stale assessments cannot bypass validation through a direct status update.

The normal Assessment form no longer exposes `Completed` as a selectable Draft status. Completion is handled by the explicit workflow action.

### Tests Added

Added `tests/Feature/AssessmentLevelOneCompletionWorkflowTest.php` covering incomplete completion attempts, stale score prevention, valid completion, `completed_at`, completed-record locking, disabled actions, retained score snapshots, retained Building Snapshot, direct status-bypass protection, and completed snapshot locking.

### Checks / Tests Performed

- `php -l app\Services\Assessments\LevelOneCompletionValidator.php`
- `php -l app\Filament\Resources\Assessments\Pages\Concerns\CompletesAssessment.php`
- `php -l app\Models\Assessment.php`
- `php -l app\Models\AssessmentStructuralDetail.php`
- `php -l app\Models\AssessmentBuildingSnapshot.php`
- `php -l app\Filament\Resources\Assessments\AssessmentResource.php`
- `php -l app\Filament\Resources\Assessments\Pages\EditAssessment.php`
- `php -l app\Filament\Resources\Assessments\Pages\ViewAssessment.php`
- `php -l app\Services\Fema\FemaLevelOneScoreSnapshotter.php`
- `php -l tests\Feature\AssessmentLevelOneCompletionWorkflowTest.php`
- `php -l database\migrations\2026_09_29_000000_add_completed_at_to_assessments_table.php`
- `php artisan migrate`
- `php artisan test tests\Feature\AssessmentLevelOneCompletionWorkflowTest.php`
- `php artisan test tests\Feature\AssessmentLevelOneScoreFilamentTest.php`
- `php artisan test tests\Feature\FemaLevelOneScoreLookupTest.php`
- `php artisan test tests\Feature\FemaLevelOneScoreSnapshotTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailFilamentTest.php`
- `php artisan test tests\Feature\AssessmentBuildingSnapshotTest.php`
- `php artisan test`

Focused completion workflow test result:

```text
Tests: 8 passed (83 assertions)
```

Existing Draft Level 1 E2E / score summary test result:

```text
Tests: 10 passed (119 assertions)
```

Existing FEMA lookup test result:

```text
Tests: 12 passed (48 assertions)
```

Existing FEMA score snapshot test result:

```text
Tests: 4 passed (21 assertions)
```

Existing structural detail data-layer test result:

```text
Tests: 6 passed (26 assertions)
```

Existing structural detail Filament test result:

```text
Tests: 6 passed (54 assertions)
```

Existing building snapshot focused test result:

```text
Tests: 1 passed (34 assertions)
```

Full test result:

```text
Tests: 58 passed (420 assertions)
```

### Issues Encountered and Resolution

- Filament relationship form fields evaluate disabled closures with the nested relationship record, not always the parent Assessment record.
  - Resolution: added an Assessment Resource helper that detects either the parent Assessment or a nested record with an Assessment relationship.
- Completion validation initially saw stale loaded relationship state after score calculation in tests.
  - Resolution: the Level 1 completion validator reloads current relationships before evaluating completion and stale-score rules.
- The sandbox helper intermittently failed to acquire its Windows lock directory during reads, edits, linting, and test runs.
  - Resolution: used narrow elevated reads/writes/checks for declared files and verification commands.

### Current Limitations

- Level 2 modifier lookup/application is not implemented yet.
- Screening recommendation logic is implemented for completed Level 1 assessments.
- Final report output is implemented as an HTML/Blade browser-print report; PDF export is not implemented yet.
- Reopen/admin correction workflow for completed assessments is not implemented yet.
- Existing assessments were not backfilled with building snapshots or Level 1 score snapshots.

### Next Approved Development Step

```text
Level 2 modifier lookup/application design
```

Do not proceed to the next development task until explicitly approved.

---

## Latest Project Status

### Current Phase

```text
FEMA Level 1 Workflow Completion Validation and Completed Assessment Locking
```

### Next Approved Development Step

```text
Level 2 modifier lookup/application design
```
---

## FEMA P-154 Level 1 Screening Recommendation Logic Log

### Current Phase

```text
FEMA P-154 Level 1 Screening Recommendation Logic
```

### Files Created / Modified

- `config/cbears.php`
- `database/migrations/2026_09_29_010000_add_level_one_screening_recommendation_to_assessment_structural_details_table.php`
- `app/Services/Fema/FemaLevelOneScreeningRecommendation.php`
- `app/Models/Assessment.php`
- `app/Models/AssessmentStructuralDetail.php`
- `app/Filament/Resources/Assessments/AssessmentResource.php`
- `tests/Feature/FemaLevelOneScreeningRecommendationTest.php`
- `STATUS.md`
- `DOCUMENTATION.md`

### Implementation Completed

Implemented FEMA P-154 Level 1 screening recommendation logic for completed assessments.

This task uses the persisted `final_level_one_score` snapshot. It does not recalculate FEMA scoring and does not create an engineering diagnosis.

No Level 2 scoring, final PDF/report generation, reopen workflow, or detailed recommendation-management module was implemented.

### Screening Cutoff Configuration

The Level 1 screening cutoff is centralized in:

```text
config/cbears.php
```

Default value:

```text
2.00
```

Environment override:

```text
CBEARS_FEMA_LEVEL_ONE_SCREENING_CUTOFF
```

This default follows the common FEMA P-154 screening intent where a score below `2.00` indicates that further/detailed seismic evaluation should be considered.

The cutoff is a screening threshold, not a structural safety certification. Jurisdictions or projects may configure a different cutoff if formally adopted.

### Recommendation Service

Created:

```text
App\Services\Fema\FemaLevelOneScreeningRecommendation
```

The service evaluates persisted Level 1 score context and returns:

- `available`
- `final_level_one_score`
- `screening_cutoff`
- `recommendation_code`
- `recommendation_label`
- `explanation`

Recommendation codes and labels:

```text
detailed_evaluation_recommended
Further Detailed Seismic Evaluation Recommended
```

Used when:

```text
final_level_one_score < screening_cutoff
```

```text
screening_threshold_not_triggered
Detailed Evaluation Not Triggered by Screening Score
```

Used when:

```text
final_level_one_score >= screening_cutoff
```

If no persisted Final Level 1 Score exists, the service returns:

```text
unavailable
Screening Recommendation Unavailable
```

The recommendation wording avoids labels such as Safe, Unsafe, Passed, Failed, Structurally Sound, or Condemned.

### Persistence Structure

Added recommendation snapshot columns to `assessment_structural_details`:

```text
level_one_screening_cutoff_snapshot
level_one_recommendation_code
level_one_recommendation_label
level_one_recommendation_explanation
level_one_recommendation_generated_at
```

A completed assessment snapshots the recommendation during completion, before the completed lock applies.

This preserves historical display even if the configured cutoff changes later.

Existing completed development/test records are not automatically backfilled. If needed later, backfill should be handled as a separate administrative task.

### Completion Lifecycle

When a valid Level 1 Assessment is completed:

1. completion validation confirms the persisted Level 1 score is valid and fresh;
2. recommendation logic reads the persisted `final_level_one_score`;
3. the configured cutoff and recommendation result are snapshotted;
4. the Assessment status changes to `Completed`;
5. completed-assessment locking preserves the score and recommendation history.

The recommendation is not manually editable.

### Filament Display

Added a read-only section:

```text
Screening Recommendation
```

Displayed values include:

- Final Level 1 Score
- Screening Cutoff
- Recommendation
- concise explanation
- recommendation generated timestamp

The section uses persisted recommendation snapshot values. It handles missing historical recommendations gracefully by showing that no recommendation has been generated yet.

The UI avoids Safe/Unsafe and Passed/Failed terminology.

### Tests Added

Added `tests/Feature/FemaLevelOneScreeningRecommendationTest.php` covering:

- Final Score below `2.00` produces `detailed_evaluation_recommended`
- Final Score exactly `2.00` does not trigger detailed-evaluation recommendation
- Final Score above `2.00` does not trigger detailed-evaluation recommendation
- configurable cutoff is respected
- recommendation uses persisted Final Level 1 Score only
- missing score returns unavailable result
- recommendation is persisted/snapshotted at completion
- changing configuration later does not alter historical completed recommendation snapshot
- completed Assessment displays the recommendation in Filament
- UI does not display Safe/Unsafe or Passed/Failed terminology

### Checks / Tests Performed

- `php -l config\cbears.php`
- `php -l app\Services\Fema\FemaLevelOneScreeningRecommendation.php`
- `php -l database\migrations\2026_09_29_010000_add_level_one_screening_recommendation_to_assessment_structural_details_table.php`
- `php -l tests\Feature\FemaLevelOneScreeningRecommendationTest.php`
- `php -l app\Models\Assessment.php`
- `php -l app\Filament\Resources\Assessments\AssessmentResource.php`
- `php -l app\Models\AssessmentStructuralDetail.php`
- `php artisan migrate`
- `php artisan test tests\Feature\FemaLevelOneScreeningRecommendationTest.php`
- `php artisan test tests\Feature\AssessmentLevelOneCompletionWorkflowTest.php`
- `php artisan test tests\Feature\AssessmentLevelOneScoreFilamentTest.php`
- `php artisan test tests\Feature\FemaLevelOneScoreLookupTest.php`
- `php artisan test tests\Feature\FemaLevelOneScoreSnapshotTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailFilamentTest.php`
- `php artisan test tests\Feature\AssessmentStructuralDetailTest.php`
- `php artisan test tests\Feature\AssessmentBuildingSnapshotTest.php`
- `php artisan test`

Focused screening recommendation test result:

```text
Tests: 9 passed (33 assertions)
```

Existing completion workflow test result:

```text
Tests: 8 passed (83 assertions)
```

Existing Draft Level 1 E2E / score summary test result:

```text
Tests: 10 passed (119 assertions)
```

Existing FEMA lookup test result:

```text
Tests: 12 passed (48 assertions)
```

Existing FEMA score snapshot test result:

```text
Tests: 4 passed (21 assertions)
```

Existing structural detail Filament test result:

```text
Tests: 6 passed (54 assertions)
```

Existing structural detail data-layer test result:

```text
Tests: 6 passed (26 assertions)
```

Existing building snapshot focused test result:

```text
Tests: 1 passed (34 assertions)
```

Full test result:

```text
Tests: 58 passed (420 assertions)
```

### Issues Encountered and Resolution

- The recommendation service initially used wording that could be mistaken for safety classification language.
  - Resolution: revised explanation text to describe screening output only and avoid Safe/Unsafe-style terminology.
- Existing completion wiring needed recommendation persistence before completed-assessment locks apply.
  - Resolution: recommendation snapshots are persisted during completion before status changes to `Completed`.
- The sandbox helper intermittently failed to acquire its Windows lock directory during reads, edits, linting, and test runs.
  - Resolution: used narrow elevated reads/writes/checks for declared files and verification commands.

### Current Limitations

- Level 2 modifier lookup/application is not implemented yet.
- Final report output is implemented as an HTML/Blade browser-print report; PDF export is not implemented yet.
- Reopen/admin correction workflow for completed assessments is not implemented yet.
- Existing completed records were not backfilled with recommendation snapshots.

### Next Approved Development Step

```text
Level 2 modifier lookup/application design
```

Do not proceed to the next development task until explicitly approved.

---

## Latest Project Status

### Current Phase

```text
FEMA P-154 Level 1 Screening Recommendation Logic
```

### Next Approved Development Step

```text
Level 2 modifier lookup/application design
```
---

## FEMA P-154 Level 1 Final Assessment Report Log

### Current Phase

```text
FEMA P-154 Level 1 Final Assessment Report
```

### Files Created / Modified

- `app/Filament/Resources/Assessments/Pages/ViewAssessmentReport.php`
- `resources/views/filament/resources/assessments/pages/view-assessment-report.blade.php`
- `app/Filament/Resources/Assessments/AssessmentResource.php`
- `app/Filament/Resources/Assessments/Pages/EditAssessment.php`
- `app/Filament/Resources/Assessments/Pages/ViewAssessment.php`
- `tests/Feature/AssessmentLevelOneReportTest.php`
- `STATUS.md`
- `DOCUMENTATION.md`

### Implementation Completed

Implemented a print-friendly FEMA P-154 Level 1 Final Assessment Report page for completed assessments.

The report is read-only and uses persisted assessment-time data rather than live mutable references. Draft assessments can open the report route as a preview, but the report is clearly marked `DRAFT - This report preview is not finalized.`

No PDF generation, Level 2 scoring, reopen/admin correction workflow, workflow review/approval logic, findings/photos module, or report export engine was implemented in this task.

### Report Route and Actions

Added a Filament report page route:

```text
/admin/assessments/{record}/report
```

Added page actions:

- `View Report` on completed Level 1 Assessment view/edit pages
- `View Assessment` from the report page
- `Print Report` from the report page using browser print

The normal edit action remains hidden for completed assessments.

### Report Contents

The report displays:

- Assessment Information
- Building Information from `AssessmentBuildingSnapshot`
- FEMA Structural Classification snapshot values
- Level 1 observations
- FEMA Level 1 score summary
- applied Level 1 modifier snapshots
- calculated score, minimum score, and final Level 1 score
- persisted calculation timestamp
- Screening Recommendation snapshot
- calculation trace details

Missing values render as `Not recorded`.

### Historical Snapshot Behavior

The report intentionally reads from persisted/snapshot data:

- Building data comes from `AssessmentBuildingSnapshot`, not the current live Building record.
- FEMA version and building type display values come from Structural Detail snapshot fields.
- scoring values come from persisted Level 1 score snapshot fields.
- modifiers come from `applied_level_one_modifiers_snapshot`.
- recommendation values come from persisted recommendation snapshot fields.

Changing live Building fields, FEMA reference values, or the configured screening cutoff after completion does not change the historical report display.

### Print Layout

The Blade report uses a restrained government-style print layout with plain sections, tables, score rows, and print CSS that hides Filament navigation and page chrome during browser printing.

### Tests Added

Added `tests/Feature/AssessmentLevelOneReportTest.php` covering:

- completed Level 1 report opens successfully
- required report sections render
- report uses historical Building Snapshot values after live Building changes
- structural/FEMA snapshot values remain stable after reference changes
- persisted Basic Score, modifiers, final score, recommendation, and cutoff display
- report does not change after FEMA reference or cutoff configuration changes
- report is read-only
- `View Report` action is visible on completed Level 1 assessments
- Draft report preview is clearly marked Draft
- report avoids Safe/Unsafe and Passed/Failed wording

### Checks / Tests Performed

- `php -l app\Filament\Resources\Assessments\Pages\ViewAssessmentReport.php`
- `php -l app\Filament\Resources\Assessments\AssessmentResource.php`
- `php -l app\Filament\Resources\Assessments\Pages\EditAssessment.php`
- `php -l app\Filament\Resources\Assessments\Pages\ViewAssessment.php`
- `php -l tests\Feature\AssessmentLevelOneReportTest.php`
- `php artisan test --filter=AssessmentLevelOneReportTest`
- `php artisan test --filter=AssessmentLevelOneCompletionWorkflowTest`
- `php artisan test --filter=FemaLevelOneScreeningRecommendationTest`
- `php artisan test --filter=AssessmentLevelOneScoreFilamentTest`
- `php artisan test --filter=FemaLevelOneScoreSnapshotTest`
- `php artisan test --filter=FemaLevelOneScoreLookupTest`
- `php artisan test`

Focused report test result:

```text
Tests: 8 passed (63 assertions)
```

Existing completion workflow test result:

```text
Tests: 8 passed (83 assertions)
```

Existing screening recommendation test result:

```text
Tests: 9 passed (33 assertions)
```

Existing Draft Level 1 E2E / score summary test result:

```text
Tests: 10 passed (119 assertions)
```

Existing FEMA lookup test result:

```text
Tests: 12 passed (48 assertions)
```

Existing FEMA score snapshot test result:

```text
Tests: 4 passed (21 assertions)
```

Full test result:

```text
Tests: 66 passed (483 assertions)
```

### Issues Encountered and Resolution

- The sandbox helper intermittently failed to acquire its Windows lock directory during reads, edits, linting, and test runs.
  - Resolution: used narrow elevated reads/writes/checks for declared files and verification commands.
- A report test assertion initially checked for a changed live storey value that could collide with unrelated report digits.
  - Resolution: adjusted the assertion to use a less ambiguous changed value.

### Current Limitations

- The report is HTML/Blade with browser print; no PDF export was implemented.
- Level 2 modifier lookup/application is not implemented yet.
- Reopen/admin correction workflow for completed assessments is not implemented yet.
- Existing completed records were not backfilled with report-specific data; the report displays whatever historical snapshots already exist.
- Findings, photos/documents, detailed recommendations, review/approval workflow, roles/permissions, audit trail, dashboard, and GIS remain future work.

### Next Approved Development Step

```text
Level 2 modifier lookup/application design
```

Do not proceed to the next development task until explicitly approved.

---

## Latest Project Status

### Current Phase

```text
FEMA P-154 Level 1 Final Assessment Report
```

### Next Approved Development Step

```text
Level 2 modifier lookup/application design
```
