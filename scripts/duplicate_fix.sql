-- Database Constraints & Duplicate Cleanup
-- Execute this in phpMyAdmin

-- ========================================
-- CLEANUP EXISTING DUPLICATES
-- ========================================

-- Remove duplicate clients (keep first created)
DELETE c1 FROM clients c1
INNER JOIN clients c2 
WHERE c1.id > c2.id 
AND (c1.email = c2.email OR (c1.email IS NOT NULL AND c2.email IS NOT NULL AND c1.email = c2.email))
AND c1.email IS NOT NULL;

DELETE c1 FROM clients c1
INNER JOIN clients c2 
WHERE c1.id > c2.id 
AND c1.name = c2.name
AND c1.phone = c2.phone
AND c1.phone IS NOT NULL;

-- Remove duplicate properties (keep first created)
DELETE p1 FROM properties p1
INNER JOIN properties p2 
WHERE p1.id > p2.id 
AND (p1.reference = p2.reference OR (p1.reference IS NOT NULL AND p2.reference IS NOT NULL AND p1.reference = p2.reference));

DELETE p1 FROM properties p1
INNER JOIN properties p2 
WHERE p1.id > p2.id 
AND p1.title = p2.title
AND p1.address = p2.address
AND p1.address IS NOT NULL;

-- ========================================
-- ADD UNIQUE CONSTRAINTS
-- ========================================

-- Clients: Unique email (allow NULL)
ALTER TABLE clients 
ADD UNIQUE INDEX idx_client_email (email);

-- Properties: Unique reference
ALTER TABLE properties 
ADD UNIQUE INDEX idx_property_reference (reference);

-- Users: Email already has UNIQUE, but ensure
ALTER TABLE users 
ADD UNIQUE INDEX idx_user_email (email);

-- ========================================
-- ADD DUPLICATE CHECK COLUMNS
-- ========================================

ALTER TABLE clients ADD COLUMN IF NOT EXISTS duplicate_check VARCHAR(100);
ALTER TABLE properties ADD COLUMN IF NOT EXISTS duplicate_check VARCHAR(100);

UPDATE clients SET duplicate_check = LOWER(COALESCE(email, '')) WHERE duplicate_check IS NULL;
UPDATE properties SET duplicate_check = LOWER(COALESCE(reference, '')) WHERE duplicate_check IS NULL;

ALTER TABLE clients ADD UNIQUE INDEX idx_client_duplicate (duplicate_check);
ALTER TABLE properties ADD UNIQUE INDEX idx_property_duplicate (duplicate_check);