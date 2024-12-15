

-- Create customer table
CREATE TABLE customer
(
  customer_id        SERIAL        NOT NULL,
  name               VARCHAR(50)   NOT NULL,
  email              VARCHAR(100)  NOT NULL,
  password           VARCHAR(50)   NOT NULL,
  credit_card        TEXT,
  address_1          VARCHAR(100),
  address_2          VARCHAR(100),
  city               VARCHAR(100),
  region             VARCHAR(100),
  postal_code        VARCHAR(100),
  country            VARCHAR(100),
  shipping_region_id INTEGER       NOT NULL  DEFAULT 1,
  day_phone          VARCHAR(100),
  eve_phone          VARCHAR(100),
  mob_phone          VARCHAR(100),
  CONSTRAINT pk_customer_id        PRIMARY KEY (customer_id),
  CONSTRAINT fk_shipping_region_id FOREIGN KEY (shipping_region_id)
             REFERENCES shipping_region (shipping_region_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT uk_email              UNIQUE (email)
);