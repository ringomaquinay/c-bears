# SRS.md

# Software Requirements Specification
## Carmona Building Earthquake Assessment & Risk Screening System (C-BEARS)

**Document Version:** 0.2  
**Status:** Draft for Product Owner Review  
**Platform:** Laravel + Filament + PostgreSQL  
**Primary Reference Framework:** FEMA P-154 Rapid Visual Screening principles

---

# 1. Document Control

## 1.1 Purpose

This Software Requirements Specification defines the functional, non-functional, data, workflow, security, reporting, and traceability requirements of C-BEARS.

This document describes **what the system must do**.

It does not define final database schema, Laravel migrations, Filament resource code, API design, or deployment design.

---

# 2. Product Purpose

C-BEARS shall provide the City Government of Carmona with a centralized system for earthquake-related rapid visual screening and building assessment management.

The system shall support:

- building registration;
- building identification;
- rapid visual screening;
- assessment documentation;
- FEMA P-154-based screening data;
- findings and photos;
- assessment review;
- recommendations;
- historical assessment preservation;
- monitoring;
- reporting;
- future GIS visualization.

C-BEARS shall operate as a screening and decision-support system and shall not by itself constitute structural engineering certification.

---

# 3. Scope

## 3.1 Initial In-Scope Buildings

The initial scope shall cover City Government of Carmona buildings.

Future expansion may be considered through formal project approval.

## 3.2 Users

The initial system shall be restricted to authenticated and authorized City Government users.

## 3.3 GIS

Initial GIS capability shall be point-based using latitude and longitude.

## 3.4 Assessment Basis

The system shall use verified and adopted FEMA P-154 Rapid Visual Screening principles.

---

# 4. Terminology

**Building Registry** — permanent master record of a building.

**Assessment** — a dated screening event linked to one building.

**Reassessment** — a new assessment event conducted after an earlier assessment.

**RVS** — Rapid Visual Screening.

**FEMA P-154** — reference methodology for rapid visual screening of buildings for potential seismic hazards.

**Assessor / Screener** — authorized user who performs the screening.

**Reviewer** — authorized user who reviews submitted assessments.

**Reference Data** — controlled values used by the system, including building types, score values, modifiers, and thresholds.

---

# 5. User Roles

## 5.1 Administrator

The system shall allow an Administrator to:

- manage users;
- manage roles and permissions;
- manage reference data;
- manage settings;
- view audit information;
- access system reports.

## 5.2 Assessor

The system shall allow an Assessor to:

- view authorized building records;
- create building records where permitted;
- create assessments;
- enter screening information;
- upload photos;
- record findings;
- submit an assessment for review.

## 5.3 Reviewer

The system shall allow a Reviewer to:

- view submitted assessments;
- review screening information;
- review findings and photos;
- record review actions;
- approve or return assessments where authorized.

## 5.4 Viewer

The system shall allow a Viewer to access read-only information according to assigned permissions.

---

# 6. Functional Requirements

## FR-001 Authentication

The system shall require authentication before access to protected C-BEARS modules.

## FR-002 Authorization

The system shall restrict actions according to assigned user roles and permissions.

## FR-003 Building Registration

The system shall allow authorized users to create a Building Registry record.

## FR-004 Permanent Building ID

The system shall assign each building a permanent system-generated C-BEARS Building ID.

## FR-005 Building Record Update

The system shall allow authorized users to update building master information without deleting historical assessments.

## FR-006 Building Search

The system shall allow users to search buildings using available identifiers and descriptive fields.

## FR-007 Building Location

The system shall support latitude and longitude for registered buildings.

## FR-008 Building Assessment Creation

The system shall allow an authorized user to create an assessment linked to an existing building.

## FR-009 Multiple Assessments

The system shall support multiple assessments for the same building.

## FR-010 Historical Preservation

The system shall preserve completed historical assessments.

## FR-011 Reassessment

The system shall create a new assessment event when a building is reassessed.

## FR-012 Assessment Number

The system shall assign each assessment a unique assessment number.

## FR-013 Assessment Status

The system shall maintain an assessment status.

Possible initial states may include:

- Draft
- For Review
- Reviewed
- Completed
- Cancelled

Final workflow labels are subject to approval.

## FR-014 Assessment Date and Time

The system shall record the assessment date and, where required, assessment time.

## FR-015 Assessor

The system shall identify the assessor responsible for an assessment.

## FR-016 FEMA Methodology Version

The system shall be capable of identifying the FEMA methodology/version used for an assessment.

## FR-017 Occupancy

The system shall support recording the building occupancy classification used during assessment.

## FR-018 Storeys

The system shall support recording the number of building storeys.

## FR-019 Year Built

The system shall support recording the year built or best available construction period information.

## FR-020 Structural Classification

The system shall support recording the structural system and FEMA building type used for screening.

## FR-021 Site Information

The system shall support relevant site information including soil type where required by the adopted screening methodology.

## FR-022 Irregularities

The system shall support recording applicable vertical and plan irregularities.

## FR-023 Code-Related Modifiers

The system shall support applicable pre-code, post-benchmark, or equivalent adopted modifiers.

## FR-024 Basic Structural Score

The system shall retrieve the applicable basic structural score from controlled reference data.

## FR-025 Score Modifiers

The system shall retrieve and apply applicable score modifiers from controlled reference data.

## FR-026 Final Screening Score

The system shall calculate the screening score using the approved scoring rules.

## FR-027 Traceable Calculation

The system shall retain sufficient data to explain how a calculated assessment score was obtained.

## FR-028 Historical Scoring Integrity

A later update to reference data shall not silently change the score of a completed historical assessment.

## FR-029 Screening Recommendation

The system shall support a screening recommendation based on approved rules.

## FR-030 No Automatic Safety Certification

The system shall not automatically declare a building structurally safe or unsafe solely from the rapid visual screening score.

## FR-031 Findings

The system shall allow authorized users to record findings linked to an assessment.

## FR-032 Finding Categories

The system shall support categorized findings such as:

- Structural
- Non-Structural
- Falling Hazard
- Site Condition
- Other

## FR-033 Photos

The system shall allow authorized users to attach photos to an assessment.

## FR-034 Photo Classification

The system should support photo categories such as:

- Front Elevation
- Rear Elevation
- Left Side
- Right Side
- Structural Detail
- Site Condition
- Hazard
- Other

## FR-035 Supporting Documents

The system may allow authorized users to attach supporting documents to an assessment.

## FR-036 Recommendations

The system shall allow assessment recommendations to be recorded.

## FR-037 Submission for Review

The system shall allow an assessor to submit a draft assessment for review.

## FR-038 Review

The system shall allow an authorized reviewer to inspect submitted assessment information.

## FR-039 Review Action

The system shall support recording review actions, comments, or return instructions.

## FR-040 Completion

The system shall support marking an assessment completed according to the approved workflow.

## FR-041 Building Assessment History

The system shall display the chronological assessment history of a building.

## FR-042 Monitoring

The system shall support monitoring of assessment status and future reassessment requirements.

## FR-043 Reports

The system shall support generation of standardized assessment reports.

## FR-044 Summary Reports

The system should support summary reports by:

- barangay;
- status;
- occupancy;
- structural type;
- recommendation;
- assessment year.

## FR-045 Dashboard

The system shall provide dashboard indicators based on stored system records.

## FR-046 GIS Map

The system should provide a map showing building point locations after the core assessment workflow is stable.

## FR-047 Audit Trail

The system shall eventually maintain audit information for significant actions.

## FR-048 Reference Data Management

The system shall allow authorized administrators to manage controlled reference values.

## FR-049 Reference Data Version Control

Where scoring behavior may change over time, the system shall preserve the relevant version or effective values used by each completed assessment.

## FR-050 Explainable Priority Queues

The system should support understandable operational queues such as:

- For Review
- Missing Information
- Further Engineering Evaluation Recommended
- Reassessment Due

The system shall avoid unexplained opaque risk rankings unless explicitly approved.

---

# 7. Building Registry Data Requirements

The Building Registry is the permanent master record.

Initial candidate data elements:

- system ID;
- C-BEARS Building ID;
- building name;
- owner / responsible office;
- address;
- barangay;
- latitude;
- longitude;
- primary occupancy;
- number of storeys;
- year built;
- approximate floor area;
- building permit number;
- occupancy permit number;
- remarks;
- record status;
- created date;
- updated date.

Final field definitions shall be approved before implementation.

---

# 8. Assessment Data Requirements

An assessment should be capable of storing:

- assessment ID;
- assessment number;
- building reference;
- assessment date;
- assessment time;
- assessor;
- assessment level/type;
- FEMA version;
- status;
- structural classification;
- site information;
- applicable modifiers;
- basic score;
- final score;
- recommendation;
- remarks;
- review data;
- timestamps.

Assessment data may be separated into related logical entities rather than one large record.

---

# 9. Non-Functional Requirements

## NFR-001 Usability

The interface shall be suitable for routine LGU administrative and field-assessment workflows.

## NFR-002 Maintainability

The application shall follow maintainable Laravel conventions and separate business logic from UI code.

## NFR-003 Data Integrity

The system shall use relational constraints and application validation to protect record integrity.

## NFR-004 Historical Integrity

Completed assessment history shall be preserved.

## NFR-005 Security

Protected system functions shall require authentication and authorization.

## NFR-006 Auditability

Important decisions and changes should be traceable to the responsible user and date/time.

## NFR-007 Performance

Routine record listing, search, and assessment operations should respond within a practical time under normal LGU usage.

## NFR-008 Backup Compatibility

The PostgreSQL database and stored files shall support an administrative backup process.

## NFR-009 Extensibility

The architecture should allow future GIS, integration, and monitoring features without redesigning the core building/assessment relationship.

## NFR-010 Explainability

Assessment outcomes produced by automated calculations shall be explainable from stored inputs and controlled rules.

---

# 10. Workflow Requirements

## 10.1 Building Lifecycle

```text
REGISTER → VALIDATE → ACTIVE → MONITOR → ARCHIVE
```

## 10.2 Assessment Lifecycle

```text
DRAFT → FOR REVIEW → REVIEWED → COMPLETED
```

Alternative/exception states may include:

```text
RETURNED / CANCELLED / INCOMPLETE
```

Final workflow shall be approved before implementation.

## 10.3 Reassessment Workflow

```text
Existing Building
      |
      v
Create New Assessment
      |
      v
Perform Screening
      |
      v
Review
      |
      v
Complete
      |
      v
Preserve Previous + New Assessment History
```

---

# 11. Security, Privacy, and Audit

The system shall:

- restrict protected modules to authorized users;
- apply role-based permissions;
- protect assessment records from unauthorized modification;
- preserve relevant audit information;
- avoid exposing internal assessment records publicly by default.

---

# 12. Reporting Requirements

The system shall eventually support:

### Individual Building Assessment Report

Possible contents:

- building information;
- assessment information;
- structural classification;
- FEMA screening data;
- score calculation;
- findings;
- photos;
- recommendations;
- assessor;
- reviewer;
- approval information.

### Management Reports

Possible outputs:

- registered buildings;
- assessed vs. unassessed buildings;
- buildings for review;
- buildings recommended for further evaluation;
- reassessment monitoring;
- barangay summary;
- occupancy summary;
- structural type summary.

---

# 13. Integration Requirements

No external integration is required for the initial MVP.

Potential future integrations may include:

- City GIS;
- other LGU information systems;
- document management;
- notifications;
- external hazard datasets.

Any integration must be separately approved and documented.

---

# 14. Traceability

Each implemented feature should be traceable to:

- one or more SRS requirements;
- related framework section;
- implementation documentation;
- relevant test result.

Example:

```text
FR-003 Building Registration
    ↓
Building Registry Module
    ↓
Laravel Model / Migration / Filament Resource
    ↓
Tested Create / Edit / View / Search
    ↓
DOCUMENTATION.md update
```

---

# 15. Requirement Priorities

## Priority 1 — MVP

- authentication;
- Building Registry;
- assessment master record;
- FEMA reference data;
- structural classification;
- scoring;
- findings;
- photos;
- recommendations;
- assessment history;
- printable assessment report.

## Priority 2

- roles and permissions;
- audit trail;
- dashboard;
- review workflow enhancements.

## Priority 3

- GIS;
- reassessment monitoring enhancements;
- integrations;
- advanced analytics.

---

# 16. Deferred Decisions

The following shall remain open until formally decided:

- final Building Registry fields;
- final C-BEARS Building ID format;
- final assessment number format;
- final assessment status workflow;
- exact FEMA P-154 edition/version adopted;
- verified FEMA scores and modifiers;
- screening threshold wording;
- final review/approval authority;
- final report format;
- GIS provider and map implementation;
- document storage policy;
- backup and deployment design.

---

# 17. Explicit Technical Exclusions from this SRS

This SRS intentionally does not define:

- PostgreSQL physical schema;
- Laravel migrations;
- Laravel model implementation;
- Filament resource code;
- controllers;
- APIs;
- SQL statements;
- deployment infrastructure;
- production server configuration.

Those belong to implementation/design documentation.

---

# 18. Current Status

Environment setup is operational:

- Laravel application runs locally.
- PostgreSQL 18 is installed and connected.
- Database `building_assessment` exists.
- Laravel migrations run successfully.
- Filament 5 is installed.
- Filament Admin Panel is accessible.
- Administrator login works.

Current project activity:

```text
Finalize documentation and Building Registry requirements.
```

Next approved implementation target:

```text
Building Registry
```
