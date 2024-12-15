-- Create orders table
CREATE TABLE orders
(
  order_id         SERIAL        NOT NULL,
  total_amount     NUMERIC(10,2) NOT NULL DEFAULT 0.00,
  created_on       TIMESTAMP     NOT NULL,
  shipped_on       TIMESTAMP,
  status           INTEGER       NOT NULL DEFAULT 0,
  comments         VARCHAR(255),
  customer_id      INTEGER,
  auth_code        VARCHAR(50),
  reference        VARCHAR(50),
  shipping_id      INTEGER,
  tax_id           INTEGER,
  CONSTRAINT pk_order_id PRIMARY KEY (order_id),
  CONSTRAINT fk_customer_id FOREIGN KEY (customer_id)
             REFERENCES customer (customer_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT fk_shipping_id FOREIGN KEY (shipping_id)
             REFERENCES shipping (shipping_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT fk_tax_id FOREIGN KEY (tax_id)
             REFERENCES tax (tax_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT
);

