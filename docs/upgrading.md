# Upgrade rebuilt SimpleMSPDoc

These instructions apply only between releases of the new application. No legacy migration or in-place upgrade from previous SimpleMSPDoc versions is provided.

1. Back up the database, configuration, uploaded files, and current application release.
2. During a planned maintenance window, extract the new built release separately. Replace application code and production dependencies while preserving `config/` and `var/`. Avoid serving a mixture of old and new code.
3. Sign in as an administrator and open **Database upgrades**. Review pending migrations and acknowledge your backup.
4. Apply the updates. The runner takes a database lock and uses maintenance mode while applying migrations. Successful migrations are tracked and not repeated.
5. Verify login, articles, assets, permissions and downloads before ending the maintenance window.

MySQL and MariaDB DDL can commit implicitly. Automatic rollback is not guaranteed. A failed migration keeps maintenance active; inspect `var/logs/upgrade.log`, restore the database and old application backup, and resolve the failure before attempting another upgrade. Do not manually clear maintenance or migration records to force a retry.

The first rebuilt alpha has only the initial installation migration. Later rebuilt versions add explicitly numbered migration files. No automatic code downloading/updating is included.
