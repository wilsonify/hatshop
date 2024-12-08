
-- Create product_details type
CREATE TYPE product_details AS
(
  product_id       INTEGER,
  name             VARCHAR(50),
  description      VARCHAR(1000),
  price            NUMERIC(10, 2),
  discounted_price NUMERIC(10, 2),
  image            VARCHAR(150)
);

