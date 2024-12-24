-- Create order_detail table
CREATE TABLE order_detail
(
  order_id     INTEGER        NOT NULL,
  product_id   INTEGER        NOT NULL,
  product_name VARCHAR(50)    NOT NULL,
  quantity     INTEGER        NOT NULL,
  unit_cost    NUMERIC(10, 2) NOT NULL,
  CONSTRAINT pk_order_id_product_id PRIMARY KEY (order_id, product_id),
  CONSTRAINT fk_order_id            FOREIGN KEY (order_id)
             REFERENCES orders (order_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT
);

