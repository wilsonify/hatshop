-- Create product_list type
CREATE TYPE product_list AS
(
  product_id       INTEGER,
  name             VARCHAR(50),
  description      VARCHAR(1000),
  price            NUMERIC(10, 2),
  discounted_price NUMERIC(10, 2),
  thumbnail        VARCHAR(150)
);

