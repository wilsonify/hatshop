-- Create shipping_region table
CREATE TABLE shipping_region
(
  shipping_region_id SERIAL       NOT NULL,
  shipping_region    VARCHAR(100) NOT NULL,
  CONSTRAINT pk_shipping_region_id PRIMARY KEY (shipping_region_id)
);

