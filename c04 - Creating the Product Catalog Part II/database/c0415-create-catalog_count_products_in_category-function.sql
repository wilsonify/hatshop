
-- Create catalog_count_products_in_category function
CREATE FUNCTION catalog_count_products_in_category(INTEGER)
RETURNS INTEGER LANGUAGE plpgsql AS $$
  DECLARE
    inCategoryId ALIAS FOR $1;
    outCategoriesCount INTEGER;
  BEGIN
    SELECT     INTO outCategoriesCount
               count(*)
    FROM       product p
    INNER JOIN product_category pc
                 ON p.product_id = pc.product_id
    WHERE      pc.category_id = inCategoryId;
    RETURN outCategoriesCount;
  END;
$$;

