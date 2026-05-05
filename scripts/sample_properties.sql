-- Insert Sample Properties with Images
-- Execute in phpMyAdmin

-- Property 1: Apartamento T3 Lisboa
INSERT INTO properties (reference, title, description, address, city, region, postal_code, price, type, status, bedrooms, bathrooms, area_m2, featured, created_at, updated_at) 
VALUES ('LIS-002', 'Apartamento T3 Centro Histórico', 'Belíssimo apartamento T3 no coração de Lisboa, com vistas desafogadas para o Rio Tejo. Renovado com materiais de alta qualidade.', 'Rua do Carmo, 45', 'Lisboa', 'Lisboa', '1200-087', 650000, 'apartment', 'available', 3, 2, 120, 1, NOW(), NOW());

-- Property 2: Moradia Cascais
INSERT INTO properties (reference, title, description, address, city, region, postal_code, price, type, status, bedrooms, bathrooms, area_m2, featured, created_at, updated_at) 
VALUES ('CAS-002', 'Moradia V4 Cascais Orla Mar', 'Exclusiva moradia V4 em Cascais, com piscina e jardim privativo. A 100m da praia. Acabamentos premium.', 'Avenida Marginal, 2345', 'Cascais', 'Lisboa', '2750-053', 1850000, 'house', 'available', 4, 3, 280, 1, NOW(), NOW());

-- Property 3: Penthouse Porto
INSERT INTO properties (reference, title, description, address, city, region, postal_code, price, type, status, bedrooms, bathrooms, area_m2, featured, created_at, updated_at) 
VALUES ('POR-002', 'Penthouse Porto Centro', 'Penthouse moderno no centro do Porto, com terraço panorâmico. Acabamentos de designer.', 'Rua de Santa Catarina, 300', 'Porto', 'Porto', '4000-461', 920000, 'apartment', 'available', 2, 2, 95, 0, NOW(), NOW());

-- Insert Images (using placeholder paths)
-- Get the property IDs first and insert images
INSERT INTO property_images (property_id, filename, original_name, file_path, file_size, mime_type, is_primary, sort_order)
SELECT id, 'demo1.jpg', 'demo1.jpg', 'uploads/properties/demo1.jpg', 100000, 'image/jpeg', 1, 0 FROM properties WHERE reference = 'LIS-002';

INSERT INTO property_images (property_id, filename, original_name, file_path, file_size, mime_type, is_primary, sort_order)
SELECT id, 'demo2.jpg', 'demo2.jpg', 'uploads/properties/demo2.jpg', 120000, 'image/jpeg', 1, 0 FROM properties WHERE reference = 'CAS-002';

INSERT INTO property_images (property_id, filename, original_name, file_path, file_size, mime_type, is_primary, sort_order)
SELECT id, 'demo3.jpg', 'demo3.jpg', 'uploads/properties/demo3.jpg', 90000, 'image/jpeg', 1, 0 FROM properties WHERE reference = 'POR-002';