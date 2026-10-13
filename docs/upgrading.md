# Update to 2.0.0-alpha.4

## From alpha.2 or alpha.3

1. Back up the database and the entire `data/` directory before uploading the update.
2. Extract the new ZIP over the existing website root. Preserve your existing `data/` directory, especially `data/config.php`, sessions and uploaded files. Do not delete it or replace its contents with an empty folder. The ZIP does not contain a configuration file.
3. Sign in as an administrator. The application directs you to **Database upgrades**.
4. Confirm your backup and click **Apply database updates**. Existing organizations, documentation, users and uploads remain intact. The additive migration creates organization details/logo fields and an encrypted password table. No reinstallation is needed.

Keep the original `data/config.php` application key: it is needed to decrypt MFA secrets and stored passwords. Back up the database and configuration together.

MySQL/MariaDB schema changes can commit implicitly. If an upgrade fails, maintenance remains active. Review `data/upgrade.log.php` through your hosting file manager and restore the database and application backup before retrying. Do not manually clear maintenance or migration records to force a retry.

## From alpha.1 or the legacy application

The old layout and legacy application remain incompatible. Use a clean directory and fresh database; no migration from those versions is provided.
