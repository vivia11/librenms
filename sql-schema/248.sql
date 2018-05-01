CREATE TABLE IF NOT EXISTS alert_contacts (contact_id INT(11) NOT NULL AUTO_INCREMENT, transport_id INT(11) DEFAULT NULL, contact_group_id INT(11) DEFAULT NULL, transport_config VARCHAR(16) NOT NULL DEFAULT 'default', contact_name VARCHAR(30) NOT NULL COLLATE utf8_unicode_ci, transport_type VARCHAR(20) NOT NULL DEFAULT 'email', PRIMARY KEY(contact_id));
CREATE TABLE IF NOT EXISTS alert_configs (config_id INT(11) NOT NULL AUTO_INCREMENT, contact_or_transport_id INT(11) NOT NULL, config_type VARCHAR(16) NOT NULL, config_name VARCHAR(255) COLLATE utf8_unicode_ci NOT NULL, config_value VARCHAR(512) COLLATE utf8_unicode_ci NOT NULL, PRIMARY KEY(config_id));
CREATE TABLE IF NOT EXISTS alert_contact_map (id INT(11) NOT NULL AUTO_INCREMENT, rule_id INT(11) NOT NULL, contact_or_group_id INT(11) NOT NULL, contact_type VARCHAR(16) NOT NULL, PRIMARY KEY(id));
