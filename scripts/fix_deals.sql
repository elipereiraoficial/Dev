-- Fix deal 2025-008 to use correct stage (Fechado Ganho)
-- Run this in phpMyAdmin to fix the existing deal

-- First check current state
SELECT id, reference, title, stage_id, status FROM deals WHERE reference = '2025-008';

-- Update to correct stage (Fechado Ganho = stage_id 7)
UPDATE deals SET stage_id = 7, status = 'won', actual_close = NOW() WHERE reference = '2025-008';

-- Verify update
SELECT id, reference, title, stage_id, status FROM deals WHERE reference = '2025-008';