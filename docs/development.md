# Develop SimpleMSPDoc

PHP 8.3 and Composer are required. Production runtime libraries are pinned in `app/composer.lock`.

```bash
composer --working-dir=app install
```

The Git checkout and release ZIP share the same website layout: root index.php, app/ for application code and libraries, assets/ and guarded data/. Tests and scripts are separate development folders and are excluded from release ZIPs. The development server can be started with:

```bash
php -S 127.0.0.1:8080 -t .
```

Use the browser installer directly; no setup key or ownership verification is required. `SIMPLEMSPDOC_PRIVATE_ROOT` can point at a separate directory containing data/. This is also used by integration tests. The base path is detected from index.php; the HTTP suite tests a subdirectory mount without a router.

## Tests

Use a disposable database whose name ends with `_test`. Tests drop its tables. Never use a production or development installation database.

```bash
export TEST_DSN='mysql:host=127.0.0.1;port=3306;dbname=simplemspdoc_test;charset=utf8mb4'
export TEST_DB_USER='test_user'
export TEST_DB_PASSWORD='test_password'
composer --working-dir=app test
```

The HTTP tests start PHP's development server through `proc_open` and use curl with cookie jars. They exercise real CSRF forms and redirects. Release tests need Composer and PHP zip. Database integration tests skip if TEST_DSN is absent; skipped tests do not verify database behavior.

CI targets PHP 8.3/8.4/8.5 and both MySQL 8.0/MariaDB 10.11. Compatibility beyond the baseline must be established by passing jobs.

## Build

```bash
php scripts/build-release.php 2.0.0-alpha.7
```

The build installs production dependencies into temporary staging, bundles local assets and license files, and writes `dist/SimpleMSPDoc-2.0.0-alpha.7.zip`. It excludes test tools, legacy scripts, local configuration, and user storage. `COMPOSER_BINARY` can name the Composer executable when it is not on PATH. An existing installed site already has app/vendor. A fresh Git clone needs composer --working-dir=app install --no-dev once, or the production dependencies from the release ZIP.

## Extension boundary

Feature services enforce permissions before reading/writing. Shared records store organization/type/visibility headers and validated type-specific payloads. Article revisions and attachments inherit parent access. Clients receive the current article content only; historical revision pages are staff-only to avoid exposing formerly internal drafts.

The integration module defines Connector, SyncResult, ManagedFields, and organization-scoped connection metadata. The optional Action1 connector uses encrypted credentials, explicit organization mapping and resumable browser sync. New adapters should use the shared configuration fields and preserve manual values; see action1.md.

## Browser acceptance

Install Playwright in a separate development tools environment (`npm install playwright` and `npx playwright install chromium`). Set `NODE_PATH` to that environment's node_modules and `SCREENSHOT_DIR` to an existing writable directory, then run `php tests/Browser/run.php` with TEST_DSN configured. This seeds and destroys a disposable test database, tests the actual rich-text editor in Chromium, and captures desktop/mobile screenshots. No Node dependency is required on the deployed application.

The Apache smoke job installs the built ZIP into an Apache vhost and tests fresh installation, rewrite routing, login, forms and denied private paths. Local Apache config syntax is checked independently; environments that prohibit dropping worker privileges cannot substitute for this runtime job.

See git-workflow.md for using the installed dev site as the Git working directory.
