-- Adding a new field named auth_code to orders table
ALTER TABLE orders ADD COLUMN auth_code VARCHAR(50);
