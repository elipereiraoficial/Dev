-- 2FA Enhancement
-- Execute this SQL in phpMyAdmin

-- Add 2FA columns to users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_enabled TINYINT(1) DEFAULT 0;
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_secret VARCHAR(255);
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_code VARCHAR(10);
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_expires DATETIME;