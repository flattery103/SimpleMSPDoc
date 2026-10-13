# Alpha.2 installation changes

The ZIP root is the website root. It contains index.php, assets/, app/, data/, and docs/. Routes use index.php?route=... without rewrites. The installer opens directly to the database and administrator form, without a setup key or ownership verification.

Private configuration, session records, uploads, and logs are PHP-guarded; direct HTTP requests return404 even without .htaccess. The installer file includes a hash, not the raw browser secret. Downloading it does not authorize installation until an owner uploads it into data/.

Use a fresh installation for alpha.2; alpha.1 storage/layout conversion is not included. GitHub publishing and actual Apache/browser/wider version CI remain blocked/pending as previously documented. This package is for a separate test LAMP installation.

The prior alpha review corrected concurrent MFA setup and archived-field search disclosure. Three minor items remain: the HTTP layer owns staff-only article-history access, revision restoration audits as an update, and direct malformed asset-service values may return500 rather than422. No changes to those unrelated items were made in alpha.2.

Alpha.4 adds persistent organization navigation, details and authenticated logos, encrypted passwords with configuration links and audit-controlled reveal, and the renderer variable fix. Additive alpha.3 upgrade and actual packaged fresh installation are tested. Final local validation: 42 tests, 325 assertions, PHP 8.3/MariaDB 10.11. PHP and JavaScript syntax checks pass; native Apache/browser matrix remains pending.
