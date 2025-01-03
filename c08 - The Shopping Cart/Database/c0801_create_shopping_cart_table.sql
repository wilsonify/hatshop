-- Create shopping_cart table
CREATE TABLE shopping_cart
(
  cart_id     CHAR(128)  NOT NULL,
  product_id  INTEGER   NOT NULL,
  quantity    INTEGER   NOT NULL,
  buy_now     BOOLEAN   NOT NULL DEFAULT true,
  added_on    TIMESTAMP NOT NULL,
  CONSTRAINT pk_cart_id_product_id PRIMARY KEY (cart_id, product_id),
  CONSTRAINT fk_product_id         FOREIGN KEY (product_id)
             REFERENCES product (product_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT
);