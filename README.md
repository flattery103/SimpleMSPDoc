# SimpleMSPDoc 2.0.0-alpha.7

Self-hosted IT documentation for internal IT teams and managed service providers. Designed for quick access to organizations, locations, contacts, assets, and procedures on a standard LAMP server.

## Compatibility notice

**This rebuild is not backward-compatible with legacy SimpleMSPDoc versions. New installations require an empty database; no legacy migration is provided. Installed rebuild alpha.2–alpha.6 systems can update directly to alpha.7 through Database upgrades.**

Keep any legacy SimpleMSPDoc deployment separate. Back up its database and uploaded files. Do not overwrite the previous installation or point this rebuild at its database.

This branch is an **alpha rebuild**, not a production release. Review the validation notes and test it with nonproduction documentation first. Future releases of the rebuilt application use tracked database migrations; that does not provide migration from the legacy application.

## Features

- Multiple organizations with persistent navigation, logos, website, phone, address, and notes
- Configurations (assets), locations, contacts, and documents
- Encrypted organization passwords, optionally linked to configurations; audited reveal without a separate vault login
- Shared asset templates with text, multiline, numeric, date, boolean, URL, and choice fields
- Rich-text articles, categories, tags, revision history, side-by-side comparison, and restore
- Private attachments and related-record links
- Global search scoped to the signed-in user's access
- Administrator, technician, and read-only roles, plus staff/client classification
- Internal-only records by default; explicit client-visible sharing
- Authenticator MFA, recovery codes, login throttling, and administrator MFA requirements
- Administrator audit history and reversible record/organization archiving
- Web installer and administrator-controlled database upgrades

The optional [Action1 add-on](docs/action1.md) imports managed devices into configurations with explicit organization mappings, encrypted credentials, and resumable browser-driven sync. It is disabled by default. An additional vault login and Meraki/Microsoft 365 integrations are not included.

## Requirements

- Apache 2.4 and PHP 8.3 or newer; rewrite rules are not required
- MySQL 8.0 or MariaDB 10.11 or newer
- PHP extensions: PDO MySQL, mbstring, DOM, fileinfo, OpenSSL; zip for Office document attachments
- HTTPS for deployed installations
- A PHP-writable data folder; private files use PHP guards and do not depend on .htaccess

Ubuntu Server 24.04 LTS is the reference deployment. End users install a release ZIP with bundled PHP libraries and frontend assets; Composer, Node.js, Docker, and internet-hosted UI dependencies are unnecessary on the application server.

## Installation

1. Extract the install ZIP directly into the website root, for example `/www/SimpleMSPDoc`.
2. Set DocumentRoot to that directory. There is no public/ directory or version-directory wrapper.
3. Make data/ writable by PHP using your hosting file manager.
4. Open the website to access the database and administrator setup form.
5. Enter the database details and administrator account in the browser installer.

No terminal commands, Composer, Node.js or Apache rewrites are required on the installation server. No setup key or ownership verification is required. Setup locks after success.

See [installation instructions](docs/installation.md), [update instructions](docs/upgrading.md), and [release notes](docs/release-notes.md). Alpha.5 upgrades installed alpha.2/alpha.3/alpha.4 systems through Database upgrades; preserve data/ and the existing database. Alpha.1 requires a fresh installation.

## Validation status

The local suite exercises PHP 8.3 and MariaDB 10.11, including actual HTTP installation, client isolation, MFA gating, CSRF, revisions, uploads, relationships, and upgrade failures. The GitHub Actions workflow targets PHP 8.3/8.4/8.5 against MySQL 8.0 and MariaDB 10.11; additional combinations should be claimed as verified only after those jobs pass. Apache deployment validation is documented separately from the PHP development server tests.

## License

GNU GPL v3 or later, continuing the original project's license. Bundled dependencies retain their respective licenses.

Configuration lists support per-user column choices. Standard configuration details include serial number, type, manufacturer/model, system specifications, MAC address and purchase/warranty dates. Administrators can configure the account password minimum under Users and access.

Configuration details and notes are unified across templates and integrations. Column settings open from the gear at the table’s top right. Existing legacy values and integration source data are retained internally.

The GitHub checkout now uses the same folder layout as the installed website. See [Git workflow](docs/git-workflow.md) for committing from the dev site without source bundles. Tests and packaging scripts remain separate and are excluded from release ZIPs.
