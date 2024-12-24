-- Create cart_saved_product type
CREATE TYPE cart_saved_product AS
(
  product_id INTEGER,
  name       VARCHAR(50),
  price      NUMERIC(10, 2)
);

