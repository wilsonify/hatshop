-- Create shipping table
CREATE TABLE shipping
(
  shipping_id        SERIAL         NOT NULL,
  shipping_type      VARCHAR(100)   NOT NULL,
  shipping_cost      NUMERIC(10, 2) NOT NULL,
  shipping_region_id INTEGER        NOT NULL,
  CONSTRAINT pk_shipping_id        PRIMARY KEY (shipping_id),
  CONSTRAINT fk_shipping_region_id FOREIGN KEY (shipping_region_id)
             REFERENCES shipping_region (shipping_region_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT
);

