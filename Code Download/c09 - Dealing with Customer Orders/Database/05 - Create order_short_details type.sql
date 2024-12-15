-- Create order_short_details type
CREATE TYPE order_short_details AS
(
  order_id      INTEGER,
  total_amount  NUMERIC(10, 2),
  created_on    TIMESTAMP,
  shipped_on    TIMESTAMP,
  status        INTEGER,
  customer_name VARCHAR(50)
);