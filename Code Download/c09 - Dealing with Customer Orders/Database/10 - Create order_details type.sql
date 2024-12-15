
-- Create order_details type
CREATE TYPE order_details AS
(
  order_id     INTEGER,
  product_id   INTEGER,
  product_name VARCHAR(50),
  quantity     INTEGER,
  unit_cost    NUMERIC(10, 2),
  subtotal     NUMERIC(10, 2)
);