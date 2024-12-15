-- Adding a new foreign key constraint to orders table
ALTER TABLE orders
  ADD CONSTRAINT fk_customer_id FOREIGN KEY (customer_id)
                 REFERENCES customer (customer_id)
                 ON UPDATE RESTRICT ON DELETE RESTRICT;