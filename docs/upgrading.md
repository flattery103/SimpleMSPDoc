# Updates

Alpha.2 introduces a flat install layout, rewrite-free routes, browser/file-manager setup verification, and guarded private-file storage. Use a fresh directory and database when moving from alpha.1; no alpha.1 data conversion is provided in this test release.

For later releases that preserve this layout, back up the database and `data/`, replace application code during a maintenance window, preserve `data/`, and open **Database upgrades** as an administrator. The migration runner locks concurrent upgrades and tracks applied migrations.

MySQL/MariaDB schema changes can commit implicitly. If a migration fails, maintenance remains active. Review the guarded `data/upgrade.log.php` through your hosting file manager, restore the application and database backup, and resolve the failure before retrying. Do not manually clear maintenance or migration records to force a retry.

Legacy SimpleMSPDoc versions remain incompatible and have no migration path.
