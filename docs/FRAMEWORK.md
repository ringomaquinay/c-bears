# FRAMEWORK.md

# Carmona Building Earthquake Assessment & Risk Screening System (C-BEARS)

## 1. Framework Purpose

This document defines the conceptual, operational, and system framework of C-BEARS.

It describes how the system is organized, how building records move through the assessment lifecycle, and how screening information is preserved and used for decision support.

---

## 2. System Purpose

C-BEARS will provide the City Government of Carmona with a centralized system for:

- registering City Government buildings;
- maintaining permanent building identities;
- conducting earthquake rapid visual screening;
- documenting structural and site observations;
- preserving assessment history;
- recording findings and supporting photos;
- reviewing and approving assessment records;
- identifying buildings that may require further evaluation;
- monitoring reassessment needs;
- generating reports;
- supporting future GIS visualization and analytics.

C-BEARS is not intended to replace detailed structural engineering evaluation.

---

## 3. Initial Scope

Initial organizational scope:

```text
City Government of Carmona buildings
```

Initial user scope:

```text
Authenticated and authorized City Government users
```

Initial GIS scope:

```text
Point-based building locations using latitude and longitude
```

Initial assessment basis:

```text
FEMA P-154 Rapid Visual Screening principles
```

---

## 4. Core Principles

### 4.1 One Building, One Permanent Identity

Every registered building receives a permanent C-BEARS Building ID.

### 4.2 Many Assessments per Building

A building may be assessed more than once.

Each reassessment creates a new historical event.

### 4.3 Preserve History

Completed historical assessments must never be overwritten by later reassessments.

### 4.4 Screening, Not Certification

A screening result identifies possible need for additional evaluation.

It does not by itself certify structural safety.

### 4.5 Explainable Results

Assessment results must be traceable to the building type, modifiers, findings, and scoring basis used.

### 4.6 Controlled Reference Data

FEMA-related scores, modifiers, thresholds, and methodology versions should be configurable and controlled.

---

## 5. Building Assessment Lifecycle

The operational lifecycle is:

```text
REGISTER
   |
   v
VALIDATE
   |
   v
ASSESS
   |
   v
REVIEW
   |
   v
APPROVE
   |
   v
MONITOR
   |
   v
REASSESS
   |
   v
ARCHIVE
```

### REGISTER

Create the permanent building master record.

### VALIDATE

Verify the building identity and basic registry information.

### ASSESS

Conduct the rapid visual screening and capture supporting evidence.

### REVIEW

A designated reviewer checks completeness, classification, scoring, and findings.

### APPROVE

Finalize the assessment record according to authorized workflow.

### MONITOR

Track status, recommendations, and future reassessment requirements.

### REASSESS

Create a new assessment event without deleting or replacing prior history.

### ARCHIVE

Preserve records that are no longer active while maintaining historical traceability.

---

## 6. High-Level System Framework

```text
Users
  |
  v
Filament Admin Interface
  |
  v
Laravel Application
  |
  +---------------------------+
  |                           |
  v                           v
Building Registry       Assessment Management
  |                           |
  |                           +--> FEMA Reference Data
  |                           +--> Structural Classification
  |                           +--> Score Modifiers
  |                           +--> Scoring Engine
  |                           +--> Findings
  |                           +--> Photos / Documents
  |                           +--> Recommendations
  |                           +--> Review / Approval
  |
  +-------------> PostgreSQL Database
                            |
                            +--> History
                            +--> Audit Trail
                            +--> Reports
                            +--> Dashboard
                            +--> GIS / Map
```

---

## 7. Core Modules

### 7.1 Dashboard

Purpose:

Provide a summary of system activity and screening status.

Possible indicators:

- total registered buildings;
- total assessed buildings;
- assessments by status;
- assessments by barangay;
- buildings recommended for further evaluation;
- buildings due for reassessment;
- assessments by occupancy;
- assessments by structural type.

Dashboard indicators should be derived from authoritative records rather than manually encoded totals.

### 7.2 Building Registry

Purpose:

Maintain the permanent master record of each building.

The Building Registry stores relatively stable building identity and location information.

Possible fields:

- C-BEARS Building ID
- Building Name
- Owner / Responsible Office
- Address
- Barangay
- Latitude
- Longitude
- Primary Occupancy
- Number of Storeys
- Year Built
- Approximate Floor Area
- Building Permit Number
- Occupancy Permit Number
- Remarks
- Record Status

Assessment results must not be stored as permanent building master attributes when they may change over time.

### 7.3 Assessment

Purpose:

Create and manage individual assessment events linked to a building.

Possible fields:

- Assessment Number
- Building
- Assessment Date
- Assessment Time
- Assessor
- Assessment Level
- FEMA Version
- Status
- Remarks

Each assessment must remain historically identifiable.

### 7.4 FEMA P-154 Screening

Purpose:

Capture FEMA P-154 screening information and support scoring.

Information groups may include:

- occupancy;
- number of storeys;
- year built;
- structural system;
- FEMA building type;
- soil type;
- vertical irregularity;
- plan irregularity;
- pre-code condition;
- post-benchmark condition;
- applicable score modifiers;
- basic score;
- final score;
- screening recommendation.

Exact values and formulas must be verified before production implementation.

### 7.5 GIS / Map

Initial purpose:

Show building points geographically.

Initial capabilities may include:

- latitude/longitude;
- map marker;
- building name;
- current assessment status;
- quick link to building record.

Advanced spatial analysis is deferred.

### 7.6 Photos

Purpose:

Attach photographic evidence to an assessment.

Possible categories:

- front elevation;
- rear elevation;
- left side;
- right side;
- site condition;
- structural detail;
- falling hazard;
- other.

### 7.7 Documents

Purpose:

Attach supporting files where necessary.

Possible examples:

- plans;
- permits;
- engineering documents;
- previous assessment documents;
- supporting reports.

### 7.8 History

Purpose:

Provide chronological traceability of building assessments and status changes.

A user should be able to view the building and its assessment history without overwriting older records.

### 7.9 Reports

Possible reports:

- individual assessment report;
- building assessment history;
- buildings by barangay;
- buildings by assessment status;
- buildings recommended for further evaluation;
- reassessment monitoring report;
- summary by occupancy;
- summary by structural type.

### 7.10 Reference Data

Purpose:

Manage controlled lookup values.

Possible reference datasets:

- occupancy types;
- FEMA building types;
- structural systems;
- soil types;
- irregularity types;
- basic scores;
- score modifiers;
- screening thresholds;
- FEMA methodology versions.

### 7.11 Users and Roles

Initial roles:

- Administrator
- Assessor
- Reviewer
- Viewer

### 7.12 Audit Trail

Purpose:

Track significant changes and approvals.

### 7.13 Settings

Purpose:

Store controlled system configuration, not engineering assumptions that should instead live in versioned reference data.

---

## 8. FEMA P-154 Assessment Framework

The assessment workflow will be grouped into:

### A. Building Identification

- building;
- address;
- barangay;
- latitude;
- longitude.

### B. Survey Information

- date;
- time;
- screener;
- assessment number;
- photos;
- status.

### C. Building Characteristics

- occupancy;
- storeys;
- year built;
- approximate area;
- structural system;
- FEMA building type.

### D. Site Information

- soil type;
- site condition;
- relevant observations.

### E. Screening Factors

- basic structural score;
- vertical irregularity;
- plan irregularity;
- pre-code;
- post-benchmark;
- soil modifier;
- other applicable modifiers.

### F. Result

- calculated screening score;
- review status;
- further evaluation recommendation;
- remarks.

### G. Supporting Evidence

- findings;
- photos;
- documents.

---

## 9. Assessment Result Philosophy

C-BEARS should present assessment results using transparent status and recommendation language.

Examples:

- Draft
- For Review
- Reviewed
- Completed
- Incomplete Assessment
- Further Engineering Evaluation Recommended
- Further Evaluation Not Indicated by Screening

Final labels must be approved before implementation.

The system should not create an unexplained “safe/unsafe” result from RVS alone.

---

## 10. Historical Assessment Model

Example:

```text
Building CBEARS-000001
|
|-- Assessment A-2026-000001
|   |-- FEMA version
|   |-- Building type
|   |-- Modifiers
|   |-- Score
|   |-- Findings
|   |-- Photos
|   |-- Review
|
|-- Assessment A-2029-000014
    |-- FEMA version
    |-- Building type
    |-- Modifiers
    |-- Score
    |-- Findings
    |-- Photos
    |-- Review
```

The newer assessment does not replace the older assessment.

---

## 11. Priority Framework

The system should support understandable operational prioritization.

Possible queues:

- For Review
- Missing Information
- Further Engineering Evaluation Recommended
- Reassessment Due
- Recently Completed

Priority categories should be based on documented rules and must remain explainable to users.

---

## 12. Governance

### Product Owner / Authorized Project Owner

Responsible for approving:

- scope;
- workflows;
- major field changes;
- reference methodology;
- scoring adoption;
- major architectural changes.

### Assessor

Responsible for:

- conducting screening;
- entering observations;
- attaching evidence;
- submitting assessments.

### Reviewer

Responsible for:

- checking completeness;
- verifying classification where appropriate;
- reviewing results;
- recording review action.

### Administrator

Responsible for:

- system administration;
- controlled reference data;
- users and roles;
- configuration;
- audit support.

---

## 13. Success Criteria

The system should eventually allow the City to:

- identify every registered building uniquely;
- retrieve a complete assessment history;
- know which buildings are pending assessment or review;
- identify buildings recommended for further engineering evaluation;
- view assessment evidence;
- produce standardized reports;
- monitor reassessment needs;
- map building locations;
- maintain traceable reference data and assessment decisions.

---

## 14. Current Development Phase

Completed:

- local Laravel environment;
- PostgreSQL 18;
- `building_assessment` database;
- Laravel database connection;
- default migrations;
- Filament installation;
- Filament Admin Panel;
- administrator login.

Current phase:

```text
Finalize documentation and Building Registry requirements.
```

Next phase:

```text
Implement Building Registry only after fields are approved.
```
