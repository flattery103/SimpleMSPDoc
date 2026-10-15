<?php
return ['sql'=>[
"CREATE TABLE application_settings (id TINYINT PRIMARY KEY, password_minimum TINYINT UNSIGNED NOT NULL DEFAULT 12) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"INSERT INTO application_settings(id) VALUES (1)",
"CREATE TABLE configuration_preferences (user_id BIGINT UNSIGNED NOT NULL, organization_id BIGINT UNSIGNED NOT NULL, columns_json TEXT NOT NULL, PRIMARY KEY(user_id,organization_id), FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY(organization_id) REFERENCES organizations(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
], 'checks'=>["SELECT COUNT(*)=1 FROM application_settings WHERE id=1", "SELECT COUNT(*)=1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='configuration_preferences'"]];
