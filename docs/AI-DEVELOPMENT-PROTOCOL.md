# AI-DEVELOPMENT-PROTOCOL.md

# Carmona Building Earthquake Assessment & Risk Screening System (C-BEARS)

## 1. Purpose

This document defines the rules for AI-assisted development of C-BEARS.

The goal is to keep development controlled, traceable, testable, and aligned with the approved requirements and system framework.

AI may assist in planning, coding, testing, documentation, and troubleshooting, but it must not silently change approved requirements, assessment logic, scoring rules, workflows, or database design.

---

## 2. Project Identity

**Project Name:** Carmona Building Earthquake Assessment & Risk Screening System  
**Short Name:** C-BEARS  
**Primary Purpose:** Building earthquake rapid visual screening, assessment documentation, prioritization, monitoring, reassessment, and decision support for City Government of Carmona buildings.

C-BEARS is a screening and decision-support system. It is not a structural engineering certification system and must not automatically declare a building structurally safe or unsafe solely from a screening score.

---

## 3. Approved Technology Direction

Current approved stack:

- Laravel
- Filament 5
- PostgreSQL 18
- PHP
- Composer
- Node.js / Vite
- pgAdmin 4 for local PostgreSQL administration

Current local project directory:

```text
D:\building-assessment
```

Current database:

```text
building_assessment
```

Database connection:

```text
pgsql
```

Any major change in framework, database platform, authentication architecture, GIS provider, or assessment methodology must first be documented and approved.

---

## 4. Source-of-Truth Documents

Development must follow these documents in this order:

1. `docs/AI-DEVELOPMENT-PROTOCOL.md`
2. `docs/FRAMEWORK.md`
3. `docs/SRS.md`
4. `DOCUMENTATION.md`

Their purpose is:

- **AI-DEVELOPMENT-PROTOCOL.md** — development rules and guardrails.
- **FRAMEWORK.md** — system concept, modules, lifecycle, and operating framework.
- **SRS.md** — software requirements.
- **DOCUMENTATION.md** — actual setup, implementation progress, commands, decisions, and test results.

If a coding request conflicts with the SRS or Framework, the conflict must be identified before changing the implementation.

---

## 5. Development Rule

Development must proceed one small verified step at a time.

For each task:

1. Identify the exact requirement.
2. Identify the files that need to change.
3. Make the smallest practical change.
4. Run the appropriate test or command.
5. Fix errors before continuing.
6. Confirm the feature works.
7. Update documentation.
8. Proceed only after the current step is stable.

Do not implement multiple major modules at the same time.

Do not jump ahead because a future feature looks easy.

---

## 6. AI Coding Rules

AI-assisted coding must follow these rules:

- Read the relevant project documentation before making architectural changes.
- Preserve existing working functionality.
- Do not silently rename fields, tables, statuses, modules, or workflows.
- Do not add packages unless needed.
- Explain why a new package is necessary before introducing it.
- Do not change database relationships without documenting the reason.
- Do not hard-code FEMA values that should be configurable or versioned.
- Do not overwrite historical assessments.
- Do not create reassessment logic that edits prior completed assessments.
- Do not create an opaque overall risk score unless explicitly approved.
- Do not silently recalculate historical scores after reference values are updated.
- Do not bypass validation, authorization, audit logging, or review requirements.
- Do not expose confidential or restricted assessment information publicly by default.

---

## 7. Phase-Gate Rule

The project will use phase gates.

A phase is considered complete only when:

- required functionality is implemented;
- migration/database changes run successfully;
- the feature can be tested from the user interface;
- errors are resolved;
- documentation is updated.

Current development sequence:

1. Environment Setup
2. Documentation and System Framework
3. Building Registry
4. Assessment Master Record
5. FEMA Reference Data
6. Structural Classification
7. Score Modifiers
8. Scoring Engine
9. Findings
10. Photos and Documents
11. Recommendations
12. Review and Approval
13. Reports
14. Roles and Permissions
15. Audit Trail
16. Dashboard
17. GIS / Map
18. Reassessment and Monitoring Enhancements

---

## 8. Data Integrity Rules

### 8.1 Permanent Building Identity

Each building must have a permanent system-generated C-BEARS Building ID.

The Building ID must remain stable even when:

- the building is reassessed;
- ownership changes;
- building use changes;
- a new assessment is created.

### 8.2 Assessment History

A building may have many assessments.

A reassessment must create a new assessment event.

Completed historical assessments must remain preserved and readable.

### 8.3 Reference Data Versioning

FEMA-related reference values, formulas, modifiers, thresholds, and methodology versions must be:

- verified;
- adopted;
- documented;
- versioned where necessary;
- linked to the assessment that used them.

Updating a reference value must not silently alter a completed historical assessment.

---

## 9. FEMA P-154 Rules

C-BEARS will use FEMA P-154 Rapid Visual Screening principles as the primary seismic screening reference framework.

The system must distinguish between:

- rapid visual screening;
- professional review;
- detailed engineering evaluation.

The system must not convert an RVS result into an automatic structural safety certification.

Before implementing FEMA scoring:

1. Confirm the adopted FEMA edition/version.
2. Verify building types.
3. Verify basic structural scores.
4. Verify modifiers.
5. Verify screening thresholds.
6. Document local interpretation or adaptation.
7. Obtain appropriate project approval before production use.

---

## 10. Priority and Decision-Support Rule

C-BEARS should use understandable priority categories, filters, queues, and status indicators.

Examples:

- For Review
- Further Engineering Evaluation Recommended
- Incomplete Assessment
- Reassessment Due

Avoid an unexplained composite “risk score” that hides how the result was produced.

Users should be able to trace an assessment outcome back to:

- building classification;
- selected FEMA building type;
- applicable modifiers;
- score calculation;
- findings;
- reviewer action.

---

## 11. Auditability

Important actions should eventually be auditable, including:

- building record creation;
- building record update;
- assessment creation;
- assessment submission;
- review;
- approval;
- reopening where authorized;
- changes to FEMA reference data;
- changes to system settings.

The final system should record, where applicable:

- user;
- action;
- affected record;
- date/time;
- previous value;
- new value.

---

## 12. Security and Access

C-BEARS is intended for authenticated and authorized City Government users.

Initial role concepts:

- Administrator
- Assessor
- Reviewer
- Viewer

Permissions should follow least-privilege principles.

Public access is not part of the initial scope.

---

## 13. GIS Rule

Initial GIS implementation will be point-based.

Each building may store:

- latitude;
- longitude.

Advanced building footprints, spatial analysis, hazard overlays, and external GIS integrations are deferred until the core system is stable.

---

## 14. Deferred Features

Do not implement these during the initial MVP unless specifically approved:

- public portal;
- mobile app;
- offline synchronization;
- QR code workflow;
- AI-generated engineering conclusions;
- predictive structural risk models;
- external API integrations;
- SMS notifications;
- email notifications;
- advanced GIS analysis;
- automated structural safety certification.

---

## 15. Current Project Status

Environment foundation completed:

- PostgreSQL 18 installed
- `building_assessment` database created
- Laravel project created
- PostgreSQL PHP drivers enabled
- Laravel connected to PostgreSQL
- default migrations completed
- Filament installed
- Filament Admin Panel working
- administrator account created

Current phase:

```text
Documentation and System Framework
```

Next implementation phase:

```text
Building Registry
```

No custom Building Registry migration should be created until the required fields are finalized.
