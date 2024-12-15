-- Create product_recommendation type
CREATE TYPE product_recommendation AS
(
  product_id  INTEGER,
  name        VARCHAR(50),
  description VARCHAR(1000)
);
