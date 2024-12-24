-- Create catalog_move_product_to_category function
CREATE FUNCTION catalog_move_product_to_category(
                  INTEGER, INTEGER, INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inProductId        ALIAS FOR $1;
    inSourceCategoryId ALIAS FOR $2;
    inTargetCategoryId ALIAS FOR $3;
  BEGIN
    UPDATE product_category
    SET    category_id = inTargetCategoryId
    WHERE  product_id = inProductId
           AND category_id = inSourceCategoryId;
  END;
$$;

