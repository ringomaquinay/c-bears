# C-BEARS Agent Rules

Before doing ANY development work, read in this exact order:

1. STATUS.md
2. DOCUMENTATION.md
3. docs/AI-DEVELOPMENT-PROTOCOL.md
4. docs/FRAMEWORK.md
5. docs/SRS.md

Do not modify project code before determining:

- current development phase
- last completed task
- exact next task
- known limitations
- applicable architectural rules

Work on ONE narrowly scoped task at a time.

Before making changes:

- state the task
- list files expected to change
- confirm no unrelated modules will be modified

After changes:

- run relevant syntax checks
- run migrations/tests as applicable
- report errors and limitations
- update DOCUMENTATION.md
- update STATUS.md

Do not:

- skip phases
- invent FEMA engineering values
- hard-code verified engineering reference values in Filament forms
- overwrite historical assessments
- alter FRAMEWORK.md, SRS.md, or AI-DEVELOPMENT-PROTOCOL.md unless an approved architecture/requirement change is required
- start the next task automatically

If documentation conflicts with implementation:
STOP and report the conflict before proceeding.

C-BEARS is a screening and decision-support system.
It must not automatically certify a building as structurally safe or unsafe based only on FEMA RVS scoring.

Any AI/model/provider used for development must treat STATUS.md and DOCUMENTATION.md as mandatory continuity files. Model switching must not change the development sequence or project architecture.
