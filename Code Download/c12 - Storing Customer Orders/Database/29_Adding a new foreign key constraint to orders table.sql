-- Adding a new foreign key constraint to orders table
ALTER TABLE orders
  ADD CONSTRAINT fk_shipping_id FOREIGN KEY (shipping_id)
                 REFERENCES shipping (shipping_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT;