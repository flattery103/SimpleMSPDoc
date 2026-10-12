# SimpleMSPDoc Rebuild Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox syntax for tracking.

**Goal:** Deliver a fresh-install, organization-aware IT documentation application with staff/client access and ZIP distribution.

**Architecture:** A modular, server-rendered PHP application with PDO persistence and shared authorization. Feature modules own services, repositories, controllers, and templates; security and installation are reusable foundation services.

**Tech Stack:** PHP 8.3+, Apache 2.4, MySQL 8.0 or MariaDB 10.11, Composer for builds, PHPUnit for tests. Select maintained PHP 8.3-compatible routing, HTML sanitization, and TOTP libraries, pin them in composer.lock, and bundle production dependencies. Bundle a self-hosted rich-text editor and frontend assets.

**Spec:** `docs/superpowers/specs/2026-10-12-simplemspdoc-rebuild-design.md`

## Global Constraints

- Minimum baseline: PHP 8.3, Apache 2.4, and MySQL 8.0 or MariaDB 10.11.
- Ubuntu Server 24.04 LTS is the initial reference environment; no Ubuntu-specific application behavior.
- Fresh database only; no legacy migration or in-place upgrade. Keep the README warning prominent.
- End users need no Composer, Node.js, Docker, or frontend build step.
- Configuration, logs, and attachments stay outside the public web directory.
- Three roles: administrator, technician, read-only user; independent staff/client classification; clients are read-only.
- Records default to internal-only; revisions and attachments inherit access restrictions.
- No password vault, active external connectors, anonymous access, SSO, cross-organization relationships, or attachment-content indexing.

## Review Focus

- Removing a populated custom field preserves its values and removes it only from active forms (Task 5).
- A stale article save reports a conflict and preserves the newer revision (Task 6).
- A hidden relationship never leaks the target's title or identifier to a client (Task 7).
- A partly applied DDL migration cannot masquerade as successful or reopen normal access (Task 9).
- An installation under a URL subpath still routes assets, downloads, and redirects correctly (Tasks 1 and 10).

## Layout and shared contracts

Use PSR-4 namespace `SimpleMSPDoc`. Place shared services in `src/Core/`; feature classes in `src/Modules/{Installation,Auth,Organizations,Assets,Articles,Files,Relationships,Search,Audit,Integrations}/`. Public entrypoint/assets are `public/index.php` and `public/assets/`; templates are `templates/`; migrations are `database/migrations/`; tests are `tests/{Unit,Integration,Browser}/`; build tooling is `scripts/`. Keep legacy scripts out of the release package, then remove them from the branch once replacement routes pass acceptance.

Shared contracts: `Database::connection(): PDO`; `Actor` has `id:int`, `role:string`, `audience:string`; `AccessPolicy::can(Actor $actor, string $action, RecordScope $scope): bool`; `RecordScope` has `organizationId:int`, `visibility:string`, `deleted:bool`; `AuditService::record(Actor|null $actor, string $action, string $targetType, ?int $targetId, ?int $organizationId, array $metadata): void`. HTTP responses use 403 for forbidden writes and 404 for inaccessible record reads; validation returns 422 and edit conflicts 409. Services throw typed exceptions mapped by one error handler.

Run task tests with `vendor/bin/phpunit tests/Integration/<Name>Test.php`; each task adds tests before implementation, verifies the new test fails for the intended reason, implements, reruns to PASS, then commits focused files. Unit tests use the corresponding Unit path. DB tests must use a disposable test database, never an installation database.

### Task 1: Runnable application and installation prerequisite checks

**Files:** `composer.json`, `composer.lock`, `phpunit.xml`, `public/index.php`, `src/Core/{Application,Database,Config,ErrorHandler}.php`, `src/Modules/Installation/Requirements.php`, `templates/layout.php`, `tests/Unit/RequirementsTest.php`, `tests/Integration/RoutingTest.php`, `.github/workflows/tests.yml`.

**Interfaces:** `Application::handle(): void`; `Requirements::check(array $runtime): array` returns named pass/fail results; `Config::load(string $path): Config`; shared database and errors as above.

- [ ] Provision PHP 8.3 with required extensions, Composer, Apache, and disposable MySQL/MariaDB test services; verify executable versions. Current scratch environment has no PHP/Composer, so tests cannot yet run. If provisioning is blocked, document it and use CI evidence before claiming completion.
- [ ] Add failing tests: PHP 8.2 rejected, 8.3 accepted; missing PDO MySQL rejected; absent config routes to setup; `/docs/` base path preserves generated links; production errors hide DB credentials.
- [ ] Run both named test files and confirm intended failures.
- [ ] Implement bootstrap, request routing, base-URL helper, config loading, shared PDO, safe error handling, requirements checks, and test/CI bootstrap. Install/pin maintained compatible libraries after inspecting their official documentation. Define supported extensions from the locked dependency set.
- [ ] Run tests and PHP lint; commit `feat: establish application foundation`.

### Task 2: Fresh installation and schema

**Files:** `src/Modules/Installation/{Installer,SetupToken,InstallController}.php`, `database/migrations/001_initial.php`, `templates/install.php`, `scripts/setup-token.php`, `tests/Integration/InstallerTest.php`.

**Interfaces:** `Installer::install(array $databaseSettings, array $adminInput, string $setupToken): void`; `SetupToken::verify(string $candidate): bool`. Schema defines users, organization assignments, organizations, locations, contacts, asset types/fields/values, articles/revisions, attachments, relationships, audit events, auth attempts, migrations, integration connections, and settings.

- [ ] Add failing tests: invalid setup token cannot write config/schema; valid installation creates admin and locked setup; existing legacy/nonempty schema rejected; retry cannot create a second administrator; generated key/config not publicly reachable.
- [ ] Run InstallerTest and confirm intended failures.
- [ ] Implement token-protected setup, escaped forms/CSRF, database connectivity checks, schema creation, password hashing, private writable paths, atomic config/lock publication, and safe partial-install failure instructions. Use foreign keys/indexes and utf8mb4-compatible SQL on both database engines.
- [ ] Run InstallerTest on MySQL and MariaDB; commit `feat: add protected fresh-install wizard`.

### Task 3: Authentication, MFA, authorization, and auditing

**Files:** `src/Modules/Auth/{AuthService,MfaService,AuthController,UserController,UserRepository}.php`, `src/Core/{Actor,RecordScope,AccessPolicy,Csrf,Session}.php`, `src/Modules/Audit/{AuditService,AuditRepository,AuditController}.php`, `templates/auth/`, `templates/users/`, `templates/audit.php`, `tests/Integration/{Auth,AccessPolicy,Audit}Test.php`.

**Interfaces:** `AuthService::login(string $username, string $password): Actor|null`; MFA completion establishes authenticated session separately; `MfaService::enroll(int $userId): array`, `verify(int $userId,string $code): bool`, `consumeRecoveryCode(int $userId,string $code): bool`; policy/audit contracts above.

- [ ] Add failing tests for password/session handling, throttled login/MFA, replay rejection, encrypted seed storage, single-use recovery, required enrollment, CSRF rejection, staff/client read-only differences, admin-only user/audit management, and secret-free audit metadata. Use injected clock for throttle/TOTP tests.
- [ ] Run the three test files and confirm intended failures.
- [ ] Implement authentication, secure sessions, administrator-managed users/assignments/MFA reset, TOTP via library, encrypted seeds using private key, hashed recovery codes, policy, and audit UI. Set a documented throttle of five failed attempts per account/IP per 15 minutes and audit resets; normalize account identifiers consistently.
- [ ] Run tests; commit `feat: enforce authentication and scoped access`.

### Task 4: Organizations, locations, contacts, and navigation

**Files:** `src/Modules/Organizations/{OrganizationService,OrganizationRepository,OrganizationController,LocationService,LocationController,ContactService,ContactController}.php`, `templates/organizations/`, `templates/locations/`, `templates/contacts/`, `public/assets/app.css`, `tests/Integration/OrganizationTest.php`.

**Interfaces:** `OrganizationService::listVisible(Actor $actor): array`; location/contact services expose `create(Actor $actor,int $organizationId,array $input): int`, `update(Actor $actor,int $id,array $input): void`, `get(Actor $actor,int $id): array`, `archive(Actor $actor,int $id): void`, `restore(Actor $actor,int $id): void`.

- [ ] Add failing tests: technician/client lists reflect assignments, clients see only client-visible records, internal staff viewers can read internal records, guessed IDs return no data, read-only writes fail, archive/restore audit correctly, organization reassignment cannot transfer records through form tampering.
- [ ] Run OrganizationTest and confirm intended failures.
- [ ] Implement forms and repositories through policy, paginated organization overview, persistent sidebar/selector, soft deletion, validation, and audit. Only admins create/archive organizations and manage assignments.
- [ ] Run tests and manually inspect navigation at desktop/mobile widths; commit `feat: document organizations locations and contacts`.

### Task 5: Asset templates and records

**Files:** `src/Modules/Assets/{TemplateService,FieldValidator,AssetService,AssetRepository,AssetController,TemplateController}.php`, `templates/assets/`, `templates/templates/`, `tests/Integration/AssetTest.php`.

**Interfaces:** `TemplateService::create(Actor $actor,array $definition): int`, `archiveField(Actor $actor,int $fieldId): void`; `AssetService::{create,update,get,archive,restore}` matches Task 4 signatures; `FieldValidator::validate(array $definitions,array $values): array` returns validated values or throws validation error.

- [ ] Add failing tests for all seven specified field types, required values, invalid URLs/dates, zero/false preservation, admin-only template edits, internal default, record isolation, and populated field archival retaining values without rendering an editable field.
- [ ] Run AssetTest and confirm intended failures.
- [ ] Implement shared templates, seeded server/workstation/network types, validated typed values, assignment-checked references to contacts/locations, asset CRUD and restore UI.
- [ ] Run tests on both engines; commit `feat: add reusable asset templates and records`.

### Task 6: Articles and revision history

**Files:** `src/Modules/Articles/{ArticleService,ArticleRepository,ArticleController,HtmlSanitizer}.php`, `templates/articles/`, `public/assets/editor/`, `tests/Integration/ArticleTest.php`.

**Interfaces:** `ArticleService::create(Actor $actor,int $organizationId,array $input): int`, `update(Actor $actor,int $id,array $input,int $expectedRevision): void`, `restoreRevision(Actor $actor,int $articleId,int $revisionId,int $expectedRevision): void`, plus scoped get/archive/restore.

- [ ] Add failing tests for sanitized scripts/event attributes/unsafe links, categories/tags, internal defaults, hidden revision access, stale save returning 409 and preserving newer content, revision restore producing a new revision/audit event, and revision ID belonging to another article rejection.
- [ ] Run ArticleTest and confirm intended failures.
- [ ] Implement self-hosted editor, sanitizer allowlist, article CRUD, transactional version checks, categories/tags, history comparison and restore forms.
- [ ] Run tests and visually inspect editor/history; commit `feat: add rich-text articles and revision recovery`.

### Task 7: Attachments and relationships

**Files:** `src/Modules/Files/{AttachmentService,AttachmentController,UploadValidator}.php`, `src/Modules/Relationships/{RelationshipService,RelationshipController}.php`, `templates/partials/{attachments,relationships}.php`, `tests/Integration/{Attachment,Relationship}Test.php`.

**Interfaces:** `AttachmentService::upload(Actor $actor,string $parentType,int $parentId,array $upload): int`, `download(Actor $actor,int $attachmentId): array`; `RelationshipService::link(Actor $actor,string $sourceType,int $sourceId,string $targetType,int $targetId): int`, `listVisible(Actor $actor,string $sourceType,int $sourceId): array`.

- [ ] Add failing tests for parent visibility inheritance, direct-download ID guessing, traversal/double-extension uploads, byte-size limit, cross-organization link rejection, soft-deleted parents, and hidden target omission without leaking name/ID.
- [ ] Run both test files and confirm intended failures.
- [ ] Implement private randomized storage, safe names/headers and streaming downloads, 20 MiB default configurable limit, explicit document/image/text allowlist excluding executable/HTML/SVG files, and scoped relationships among assets/articles/contacts/locations. Stage files then atomically associate metadata; clean failed uploads.
- [ ] Run tests; commit `feat: attach files and relate authorized records`.

### Task 8: Search and connector boundary

**Files:** `src/Modules/Search/{SearchService,SearchController}.php`, `src/Modules/Integrations/{Connector,SyncResult,ConnectionRepository}.php`, `templates/search.php`, `tests/Integration/SearchTest.php`, `tests/Unit/ConnectorContractTest.php`.

**Interfaces:** `SearchService::search(Actor $actor,string $query,?int $organizationId,?string $recordType,int $page): array` returns items/total/page; `Connector::sync(array $connection,array $cursor): SyncResult`; result specifies normalized external IDs, managed fields, cursor, and status. No shipped connector or credential-entry UI.

- [ ] Add failing search tests for assignment/visibility filtering, hidden result totals, safe snippets, title/article/asset-field matching, filters, pagination, SQL wildcard handling, and archived records excluded. Add contract test accepting fake provider output without updating manually owned fields.
- [ ] Run tests and confirm intended failures.
- [ ] Implement indexed permission-scoped DB search with bounded query/page inputs; connector interface and scoped connection persistence reserve managed fields/external IDs, without contacting providers.
- [ ] Run tests; commit `feat: add scoped search and connector contracts`.

### Task 9: Safe upgrades

**Files:** `src/Modules/Installation/{MigrationRunner,UpgradeController,Maintenance}.php`, `templates/upgrade.php`, `tests/Integration/UpgradeTest.php`.

**Interfaces:** `MigrationRunner::pending(): array`, `apply(Actor $actor,bool $backupAcknowledged): void`; `Maintenance::active(): bool`. Acquire a shared database advisory lock before migrations; record successful migration only after its postconditions pass.

- [ ] Add failing tests: nonadmin/CSRF/absent backup acknowledgment rejected; concurrent attempt rejected; config/uploads preserved; tracked migrations do not repeat; injected partially applied DDL failure leaves maintenance active and migration unsuccessful, with redacted error and actionable restore instructions.
- [ ] Run UpgradeTest and confirm intended failures.
- [ ] Implement migration tracking, preflight, lock, maintenance middleware, admin upgrade flow and failure logging. Require operators to restore backup after partial failure; never auto-clear failure maintenance or claim transactional DDL rollback.
- [ ] Run tests on both engines; commit `feat: add controlled database upgrades`.

### Task 10: Release package and complete acceptance

**Files:** `scripts/build-release.php`, `README.md`, `docs/{installation,upgrading,development}.md`, `.github/workflows/{tests,release}.yml`, `tests/Browser/AcceptanceTest.php`, `tests/Integration/ReleaseTest.php`; remove legacy root PHP scripts/css/js after replacement verification.

**Interfaces:** `php scripts/build-release.php <version>` creates ZIP containing public/, src/, templates/, migrations/, production vendor/, version manifest, and installation instructions; excludes dev dependencies, tests, secrets, legacy code, uploads, and local configuration.

- [ ] Add failing package tests for bundled dependencies/assets, no legacy/config/secrets in ZIP, version manifest, private storage separation, and exact README incompatibility warning. Add browser acceptance creating two organizations, staff/client accounts, related assets/articles/attachments, searching and restoring a revision, then confirming client restrictions and cross-organization isolation. Exercise root and `/docs/` deployments.
- [ ] Run tests and confirm intended failures.
- [ ] Implement reproducible packaging, installation/upgrade/development documentation, compatibility matrix, CI artifacts, responsive final UI, and removal of replaced legacy application files. Keep existing GPL license. Publish no release or merge until the finished branch is reviewable and the user's integration preference is established.
- [ ] Run full PHPUnit suite, PHP lint, fresh Apache ZIP installation, browser acceptance, and DB matrix. Test PHP 8.3/8.4/8.5; claim only verified combinations. Check browser network requests with external access disabled to confirm all UI assets are bundled.
- [ ] Review branch against spec, report actual evidence and any blockers, and commit `feat: package fresh-install documentation application`.

## Self-review

Coverage: deployment/navigation Tasks 1/4/10; schema/install Task 2; authentication/access/audit Task 3; documentation Tasks 4–7; search/connectors Task 8; upgrades Task 9; distribution/acceptance Task 10. Review-focus conditions are assigned explicit tests. All shared role/visibility contracts remain consistent. Technical dependency versions require resolution before coding and must support PHP 8.3; do not claim runtime verification until available locally or proven through CI.
