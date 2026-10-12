Rebuild SimpleMSPDoc for fresh installation on LAMP, supporting internal IT and MSP organizations with scoped documentation and client visibility. Includes typed assets, rich-text articles/revisions, attachments, relationships, search, local authentication/MFA, auditing, a token-protected installer, tracked database upgrades, and installable ZIP builds.

This replaces the legacy application and requires a new database. No migration or in-place upgrade from previous versions is supported. The README explains this prominently. A password vault and active integrations are excluded; future connector contracts are reserved.

Validation: 33 tests /190 assertions pass locally on PHP8.3/MariaDB10.11, including real HTTP flows, client isolation, concurrent MFA confirmation, search disclosure regression, upgrade failure maintenance and package contents/permissions. PHP lint and Apache config syntax pass. Apache runtime, Chromium visual acceptance, MySQL8 and PHP8.4/8.5 remain pending CI; the supplied workflow runs these checks.

Independent review found two Important defects, corrected with regression tests. See docs/implementation-status.md for verification limits, decisions and three deferred minor findings. Keep this PR draft until the remaining checks pass.
