# SimpleMSPDoc rebuild design

Status: proposed specification for user review. Implementation has not started.

## Purpose and scope

Rebuild SimpleMSPDoc as simple, self-hosted IT documentation software for both internal IT teams and MSPs. Preserve the original emphasis on quick access and few clicks. A single installation supports multiple organizations and locations. The first release delivers the documentation core; Meraki, Microsoft 365, and other integrations follow later.

This is a fresh application and database design. It is not backward-compatible with previous SimpleMSPDoc versions. No legacy database or file migration is provided. Installation over an existing deployment is unsupported. The README and release instructions must prominently explain this and direct existing users to retain their old deployment and backups, then install the rebuild separately with a new database.

## Deployment and architecture

Minimum baseline: PHP 8.3, Apache 2.4, and MySQL 8.0 or MariaDB 10.11. Ubuntu Server 24.04 LTS is the initial reference environment; the application must not depend on Ubuntu-specific behavior. Newer runtimes are supported only after compatibility testing.

Use a modular PHP application with server-rendered pages. Separate routing/controllers, authorization, domain services, persistence, and templates. Use maintained libraries for security-sensitive functions rather than implementing cryptography or HTML sanitization from scratch. Composer is a development/build tool; release ZIPs contain production dependencies and all frontend assets. End users need no Composer, Node.js, Docker, or frontend build step.

Keep application configuration, logs, and attachments outside the public web directory. The package provides an explicit public entry directory and Apache deployment instructions. Installer requirements include PDO MySQL, mbstring, DOM, fileinfo, OpenSSL, and the extensions required by selected dependencies. Final dependency selection and extension checks belong in the implementation plan.

## Data and documentation

Organizations own locations, contacts, assets, and articles. Every organization-owned record carries an organization identifier. Assets can link to locations and contacts. Articles use a rich-text editor, sanitized HTML, and attachments. Provide categories and tags for organization and retrieval.

Administrators manage shared asset types and field definitions. Ship templates for servers, workstations, and network devices. Support text, multiline text, numbers, dates, booleans, URLs, and single-choice fields, with required-field validation. Template changes preserve existing values; removing a field with stored values requires an explicit archival behavior rather than silent data loss.

Relationships connect records within the same organization. Cross-organization relationships are excluded from the first release. Hidden related records must not leak names or identifiers. Articles retain revisions with author and timestamp; restoring an earlier revision creates a new revision and audit event. Concurrent article edits detect stale versions and do not silently overwrite newer changes.

## Accounts and access

Local accounts use three roles: administrator, technician, and read-only user. Administrators manage the installation and access all organizations. Technicians edit documentation in assigned organizations. Read-only users view authorized documentation in assigned organizations. Only administrators manage users, assignments, and shared templates.

An explicit staff/client account classification is independent of role. Client users are read-only in the first release. Staff read-only users may view internal documentation. Records default to internal-only; staff can explicitly mark records client-visible. This visibility rule applies to assets, articles, locations, and contacts. Attachments and article revisions inherit the parent record's access restrictions.

Enforce organization assignments and visibility in every server-side read and write operation, including search, downloads, revision access, and relationship lookup. Client-visible records must not expose internal related records. There is no anonymous access.

## User interface and search

Provide a persistent sidebar, organization selector, and global search. Organization overview pages offer direct access to assets, locations, contacts, and documentation. Search covers authorized record titles, article content, and searchable asset fields, with organization and record-type filters. Attachment-content indexing is excluded initially. Pagination and concise result summaries keep navigation usable as data grows.

## Security and auditing

Use password hashing through PHP's supported password APIs, secure session cookies, session rotation at login, CSRF protection, parameterized database queries, output escaping, and server-side input validation. Sanitize rich-text content and validate uploaded file size/type. Serve attachments through authorized download handlers with safe response headers; uploaded files cannot execute as application code.

Provide authenticator-app TOTP MFA and single-use hashed recovery codes. Administrators can require MFA for users; recovery/reset actions are audited. Throttle login and MFA attempts. Store TOTP seeds encrypted using an application key outside the webroot. There is no password vault; encryption for authentication material is still necessary.

Audit authentication events, record creation/change/deletion/restoration, permission and visibility changes, user administration, and upgrades. Logs include actor, organization when applicable, target, action, and timestamp, without passwords, tokens, MFA seeds, recovery codes, or attachment contents. Administrators can view audit history; ordinary users cannot modify it. Use soft deletion for documentation records so authorized staff can restore mistakes.

## Installer and upgrades

Release ZIPs include bundled dependencies. A web installer checks runtime requirements and directory permissions, accepts database settings, creates the schema and application key, and creates the first administrator. Initial setup is protected by a one-time setup token generated outside the webroot before browser setup, documented in the installation instructions. Installation is permanently locked after success; unauthenticated visitors cannot reopen it.

Configuration and attachments survive application upgrades. Provide numbered, tracked database migrations and an administrator-only web upgrade flow. Require an explicit backup acknowledgment, enable maintenance mode, and prevent concurrent upgrade attempts. Validate compatibility before changes. Because MySQL/MariaDB DDL may commit implicitly, do not promise automatic rollback; failures keep the application in maintenance mode with a useful administrator error and a documented backup-restore path. Application logs receive diagnostic details without exposing secrets to visitors.

## Future integrations

Define a connector contract and organization-scoped connection model, but ship no active external connectors in the first release. Future connectors use stable provider IDs, track last sync/status, and distinguish integration-managed fields from manual fields. Manual documentation must not be overwritten accidentally. Cron provides the scheduling entry point; no always-running worker is required for the initial documentation core. Connector credential storage and external API behavior receive their own design before implementation.

## Validation and completion criteria

Verify fresh web installation and installer lockout on the reference LAMP environment. Exercise MySQL and MariaDB separately, and test PHP 8.3 plus each newer version claimed as supported. Verify the packaged ZIP works without developer tools or internet-hosted frontend assets.

Automated authorization tests must cover cross-organization reads/writes, guessed record identifiers, client versus staff visibility, search, attachments, relationships, and revisions. Verify role restrictions, CSRF protections, upload restrictions, MFA enrollment/recovery/throttling, article revision restore and edit conflicts, and soft-delete restore.

Test tracked migrations, preservation of configuration/uploads, concurrent upgrade prevention, and failed-upgrade maintenance behavior. A first-release acceptance walkthrough creates two organizations, assigns staff/client accounts, documents related assets and articles, shares selected records, and confirms isolation and visibility.

## Non-goals

No legacy migration, password vault, external integration implementations, public sharing, SSO, granular per-field permissions, cross-organization relationships, or attachment-content search in the first release. Keep these outside the first implementation plan.
