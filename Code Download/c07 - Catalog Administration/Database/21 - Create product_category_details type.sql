-- Create product_category_details type
CREATE TYPE product_category_details AS
(
  category_id   INTEGER,
  department_id INTEGER,
  name          VARCHAR(50)
);