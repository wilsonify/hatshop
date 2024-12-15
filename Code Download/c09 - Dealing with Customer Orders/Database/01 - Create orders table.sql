-- Create orders table
CREATE TABLE orders
(
  order_id         SERIAL        NOT NULL,
  total_amount     NUMERIC(10,2) NOT NULL DEFAULT 0.00,
  created_on       TIMESTAMP     NOT NULL,
  shipped_on       TIMESTAMP,
  status           INTEGER       NOT NULL DEFAULT 0,
  comments         VARCHAR(255),
  customer_name    VARCHAR(50),
  shipping_address VARCHAR(255),
  customer_email   VARCHAR(50),
  CONSTRAINT pk_order_id PRIMARY KEY (order_id)
);