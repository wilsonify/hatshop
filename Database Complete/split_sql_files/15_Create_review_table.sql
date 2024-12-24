-- Create review table
CREATE TABLE review
(
  review_id   SERIAL    NOT NULL,
  customer_id INTEGER   NOT NULL,
  product_id  INTEGER   NOT NULL,
  review      TEXT      NOT NULL,
  rating      SMALLINT  NOT NULL,
  created_on  TIMESTAMP NOT NULL,
  CONSTRAINT pk_review_id PRIMARY KEY (review_id),
  CONSTRAINT fk_customer_id FOREIGN KEY (customer_id)
             REFERENCES customer (customer_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT fk_product_id FOREIGN KEY (product_id)
             REFERENCES product (product_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT
);

--------------------------------------------------------------------------------

