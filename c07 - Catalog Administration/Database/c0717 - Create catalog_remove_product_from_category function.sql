-- Create catalog_remove_product_from_category function
CREATE FUNCTION catalog_remove_product_from_category(INTEGER, INTEGER)
RETURNS SMALLINT LANGUAGE plpgsql AS $$
  DECLARE
    inProductId  ALIAS FOR $1;
    inCategoryId ALIAS FOR $2;
    productCategoryRowsCount INTEGER;
  BEGIN
    SELECT INTO productCategoryRowsCount
           count(*)
    FROM   product_category
    WHERE  product_id = inProductId;
    IF productCategoryRowsCount = 1 THEN
      PERFORM catalog_delete_product(inProductId);
      RETURN 0;
    END IF;
    DELETE FROM product_category
    WHERE  category_id = inCategoryId AND product_id = inProductId;
    RETURN 1;
  END;
$$;
