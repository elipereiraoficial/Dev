-- Marketing Automation Enhancement
-- Execute this SQL in phpMyAdmin

-- Add email automation columns to clients
ALTER TABLE clients ADD COLUMN IF NOT EXISTS email_sent_welcome TINYINT(1) DEFAULT 0;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS email_sent_followup TINYINT(1) DEFAULT 0;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS last_email_sent DATETIME;
ALTER TABLE clients ADD COLUMN IF NOT EXISTS email_sequence VARCHAR(50);

-- Add email tracking to deals
ALTER TABLE deals ADD COLUMN IF NOT EXISTS email_sent_client TINYINT(1) DEFAULT 0;
ALTER TABLE deals ADD COLUMN IF NOT EXISTS email_sent_agent TINYINT(1) DEFAULT 0;