# Develop SimpleMSPDoc

PHP 8.3 and Composer are required. Production runtime libraries are pinned in `composer.lock`.

```bash
composer install
```

The web entry directory is `public/`. Keep private configuration and storage outside that directory. The development server can be started with:

```bash
php -S 127.0.0.1:8080 -t public public/router.php
```

Generate a setup token and complete the web installer against an empty development database. `SIMPLEMSPDOC_PRIVATE_ROOT` can point at a separate directory containing `config/` and `var/`. This is also used by integration tests. `SIMPLEMSPDOC_BASE_PATH` configures the development router's subpath; production Apache resolves the base path from its script name.

## Tests

Use a disposable database whose name ends with `_test`. Tests drop its tables. Never use a production or development installation database.

```bash
export TEST_DSN='mysql:host=127.0.0.1;port=3306;dbname=simplemspdoc_test;charset=utf8mb4'
export TEST_DB_USER='test_user'
export TEST_DB_PASSWORD='test_password'
composer test
```

The HTTP tests start PHP's development server through `proc_open` and use curl with cookie jars. They exercise real CSRF forms and redirects. Release tests need Composer and PHP zip. Database integration tests skip if TEST_DSN is absent; skipped tests do not verify database behavior.

CI targets PHP 8.3/8.4/8.5 and both MySQL 8.0/MariaDB 10.11. Compatibility beyond the baseline must be established by passing jobs.

## Build

```bash
php scripts/build-release.php 2.0.0-alpha.1
```

The build installs production dependencies into temporary staging, bundles local assets and license files, and writes `dist/SimpleMSPDoc-2.0.0-alpha.1.zip`. It excludes test tools, legacy scripts, local configuration, and user storage. `COMPOSER_BINARY` can name the Composer executable when it is not on PATH. A source checkout is not an installable release package until the production vendor dependencies have been built.

## Extension boundary

Feature services enforce permissions before reading/writing. Shared records store organization/type/visibility headers and validated type-specific payloads. Article revisions and attachments inherit parent access. Clients receive the current article content only; historical revision pages are staff-only to avoid exposing formerly internal drafts.

The integration module defines Connector, SyncResult, ManagedFields, and organization-scoped connection metadata. There are no working connectors or external API calls. A later integration design must cover credential storage, permissions, failure/retry handling, and field ownership before enabling scheduled cron syncs.

## Browser acceptance

Install Playwright in a separate development tools environment (`npm install playwright` and `npx playwright install chromium`). Set `NODE_PATH` to that environment's node_modules and `SCREENSHOT_DIR` to an existing writable directory, then run `php tests/Browser/run.php` with TEST_DSN configured. This seeds and destroys a disposable test database, tests the actual rich-text editor in Chromium, and captures desktop/mobile screenshots. No Node dependency is required on the deployed application.

The Apache smoke job installs the built ZIP into an Apache vhost and tests fresh installation, rewrite routing, login, forms and denied private paths. Local Apache config syntax is checked independently; environments that prohibit dropping worker privileges cannot substitute for this runtime job.
