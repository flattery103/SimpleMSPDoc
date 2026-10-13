# SimpleMSPDoc 2.0.0-alpha.3

Self-hosted IT documentation for internal IT teams and managed service providers. Designed for quick access to organizations, locations, contacts, assets, and procedures on a standard LAMP server.

## Compatibility notice — fresh installation only

**This rebuild is not backward-compatible with previous versions. It requires a fresh database. No legacy migration or in-place upgrade is provided.**

Keep any previous SimpleMSPDoc deployment separate. Back up its database and uploaded files. Do not overwrite the previous installation or point this rebuild at its database.

This branch is an **alpha rebuild**, not a production release. Review the validation notes and test it with nonproduction documentation first. Future releases of the rebuilt application use tracked database migrations; that does not provide migration from the legacy application.

## Features

- Multiple organizations, locations, contacts, and assets
- Shared asset templates with text, multiline, numeric, date, boolean, URL, and choice fields
- Rich-text articles, categories, tags, revision history, side-by-side comparison, and restore
- Private attachments and related-record links
- Global search scoped to the signed-in user's access
- Administrator, technician, and read-only roles, plus staff/client classification
- Internal-only records by default; explicit client-visible sharing
- Authenticator MFA, recovery codes, login throttling, and administrator MFA requirements
- Administrator audit history and reversible record/organization archiving
- Web installer and administrator-controlled database upgrades

A password vault and active Meraki/Microsoft 365 integrations are not included. Connector contracts are reserved for future development.

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

See [installation instructions](docs/installation.md), [update instructions](docs/upgrading.md), and [release notes](docs/release-notes.md). Alpha.2 requires a fresh installation rather than an overlay on alpha.1.

## Validation status

The local suite exercises PHP 8.3 and MariaDB 10.11, including actual HTTP installation, client isolation, MFA gating, CSRF, revisions, uploads, relationships, and upgrade failures. The GitHub Actions workflow targets PHP 8.3/8.4/8.5 against MySQL 8.0 and MariaDB 10.11; additional combinations should be claimed as verified only after those jobs pass. Apache deployment validation is documented separately from the PHP development server tests.

## License

GNU GPL v3 or later, continuing the original project's license. Bundled dependencies retain their respective licenses.
