-- Create catalog_assign_product_to_category function
CREATE FUNCTION catalog_assign_product_to_category(INTEGER, INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inProductId  ALIAS FOR $1;
    inCategoryId ALIAS FOR $2;
  BEGIN
    INSERT INTO product_category (product_id, category_id)
           VALUES (inProductId, inCategoryId);
  END;
$$;