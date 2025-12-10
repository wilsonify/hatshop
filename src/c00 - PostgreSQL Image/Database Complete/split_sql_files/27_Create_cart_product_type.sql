-- Create cart_product type
CREATE TYPE cart_product AS
(
  product_id INTEGER,
  name       VARCHAR(50),
  price      NUMERIC(10, 2),
  quantity   INTEGER,
  subtotal   NUMERIC(10, 2)
);

