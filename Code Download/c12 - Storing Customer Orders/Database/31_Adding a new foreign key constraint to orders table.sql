-- Adding a new foreign key constraint to orders table
ALTER TABLE orders
  ADD CONSTRAINT fk_tax_id FOREIGN KEY (tax_id)
                 REFERENCES tax (tax_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT;