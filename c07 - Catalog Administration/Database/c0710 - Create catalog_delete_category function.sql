

-- Create catalog_delete_category function
CREATE FUNCTION catalog_delete_category(INTEGER)
RETURNS SMALLINT LANGUAGE plpgsql AS $$
  DECLARE
    inCategoryId ALIAS FOR $1;
    productCategoryRowsCount INTEGER;
  BEGIN
    SELECT      INTO productCategoryRowsCount
                count(*)
    FROM        product p
    INNER JOIN  product_category pc
                  ON p.product_id = pc.product_id
    WHERE       pc.category_id = inCategoryId;
    IF productCategoryRowsCount = 0 THEN
      DELETE FROM category WHERE category_id = inCategoryId;
      RETURN 1;
    END IF;
    RETURN -1;
  END;
$$;