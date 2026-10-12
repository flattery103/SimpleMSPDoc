# SimpleMSPDoc 2.0 rebuild

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
- Token-protected web installer and administrator-controlled database upgrades

A password vault and active Meraki/Microsoft 365 integrations are not included. Connector contracts are reserved for future development.

## Requirements

- Apache 2.4 with rewrite support and PHP 8.3 or newer
- MySQL 8.0 or MariaDB 10.11 or newer
- PHP extensions: PDO MySQL, mbstring, DOM, fileinfo, OpenSSL; zip for Office document attachments
- HTTPS for deployed installations
- Private writable configuration, session, cache, log, and upload directories outside the public webroot

Ubuntu Server 24.04 LTS is the reference deployment. End users install a release ZIP with bundled PHP libraries and frontend assets; Composer, Node.js, Docker, and internet-hosted UI dependencies are unnecessary on the application server.

## Installation

1. Download and extract a built release ZIP into a new application directory.
2. Set Apache's DocumentRoot to its `public/` subdirectory. Never serve the application root.
3. Create an empty database and an application database user.
4. Give the PHP service user write access to `config/` and `var/` only.
5. Generate a setup token with `sudo -u www-data php scripts/setup-token.php` from the application directory.
6. Open `/install` over HTTPS and enter the token, database settings, and first administrator.
7. Sign in, configure MFA, create organizations, and assign users.

The installer locks itself automatically. See [installation instructions](docs/installation.md), [upgrade instructions](docs/upgrading.md), and [developer instructions](docs/development.md).

## Validation status

The local suite exercises PHP 8.3 and MariaDB 10.11, including actual HTTP installation, client isolation, MFA gating, CSRF, revisions, uploads, relationships, and upgrade failures. The GitHub Actions workflow targets PHP 8.3/8.4/8.5 against MySQL 8.0 and MariaDB 10.11; additional combinations should be claimed as verified only after those jobs pass. Apache deployment validation is documented separately from the PHP development server tests.

## License

GNU GPL v3 or later, continuing the original project's license. Bundled dependencies retain their respective licenses.
