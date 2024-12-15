-- Create product table
CREATE TABLE product
(
  product_id       SERIAL         NOT NULL,
  name             VARCHAR(50)    NOT NULL,
  description      VARCHAR(1000)  NOT NULL,
  price            NUMERIC(10, 2) NOT NULL,
  discounted_price NUMERIC(10, 2) NOT NULL DEFAULT 0.00,
  image            VARCHAR(150),
  thumbnail        VARCHAR(150),
  display          SMALLINT       NOT NULL DEFAULT 0,
  search_vector    TSVECTOR,
  CONSTRAINT pk_product PRIMARY KEY (product_id)
);

