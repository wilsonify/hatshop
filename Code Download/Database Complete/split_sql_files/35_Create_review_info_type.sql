-- Create review_info type
CREATE TYPE review_info AS
(
  customer_name VARCHAR(50),
  review        TEXT,
  rating        SMALLINT,
  created_on    TIMESTAMP
);

--------------------------------------------------------------------------------

