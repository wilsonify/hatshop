-- Create tax table
CREATE TABLE tax
(
  tax_id         SERIAL         NOT NULL,
  tax_type       VARCHAR(100)   NOT NULL,
  tax_percentage NUMERIC(10, 2) NOT NULL,
  CONSTRAINT pk_tax_id PRIMARY KEY (tax_id)
);