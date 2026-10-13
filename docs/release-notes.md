# 2.0.0-alpha.4

- Organization navigation remains visible on overview, configuration, contact, document, location and password pages.
- Organization profiles support logos (PNG/JPEG/WebP up to 1 MiB), website, phone, address and notes. Logos require authenticated organization access.
- Organization passwords can stand alone or link to a configuration in that organization. Password values are encrypted with AES-256-GCM using a key derived from the application key.
- Passwords default to internal-only. Existing roles, organization assignments and explicit client visibility control access. Linked configurations must also be readable. Authorized users reveal passwords with a click; reveal, creation, update and archive events are audited without password contents. There is no additional vault login.
- Password secrets are excluded from documentation search and revisions, and are not prefilled in edit forms. Leave the replacement password empty to preserve the existing value.
- Fixed the asset-template page crash caused by a renderer variable-name collision.
- Installed alpha.2/alpha.3 systems can apply the additive database update while preserving data. See upgrading.md.

The flat website-root layout and direct browser installer remain. No setup key, ownership verification, command-line access or Apache rewrite rules are required.

This is an alpha release. Local validation uses PHP 8.3 and MariaDB 10.11. Wider CI and actual Apache/browser validation remain pending.
