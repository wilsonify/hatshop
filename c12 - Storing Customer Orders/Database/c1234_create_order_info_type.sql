
-- Create order_info type
CREATE TYPE order_info AS
(
  order_id       INTEGER,
  total_amount   NUMERIC(10, 2),
  created_on     TIMESTAMP,
  shipped_on     TIMESTAMP,
  status         VARCHAR(9),
  comments       VARCHAR(255),
  customer_id    INTEGER,
  auth_code      VARCHAR(50),
  reference      VARCHAR(50),
  shipping_id    INTEGER,
  shipping_type  VARCHAR(100),
  shipping_cost  NUMERIC(10, 2),
  tax_id         INTEGER,
  tax_type       VARCHAR(100),
  tax_percentage NUMERIC(10, 2)
);