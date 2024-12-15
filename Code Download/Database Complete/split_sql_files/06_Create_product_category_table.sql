-- Create product_category table
CREATE TABLE product_category
(
  product_id  INTEGER NOT NULL,
  category_id INTEGER NOT NULL,
  CONSTRAINT pk_product_id_category_id PRIMARY KEY (product_id, category_id),
  CONSTRAINT fk_product_id             FOREIGN KEY (product_id)
             REFERENCES product (product_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT fk_category_id            FOREIGN KEY (category_id)
             REFERENCES category (category_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT
);

