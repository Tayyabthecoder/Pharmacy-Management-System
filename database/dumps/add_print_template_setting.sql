-- Migration: Add print_template system setting key
-- Default value is 'simple'

INSERT INTO system_settings (meta_key, meta_value) 
VALUES ('print_template', 'simple')
ON DUPLICATE KEY UPDATE meta_value = VALUES(meta_value);
