




-- Create catalog_get_categories_for_product function
CREATE FUNCTION catalog_get_categories_for_product(INTEGER)
RETURNS SETOF product_category_details LANGUAGE plpgsql AS $$
  DECLARE
    inProductId ALIAS FOR $1;
    outProductCategoryDetailsRow product_category_details;
  BEGIN
    FOR outProductCategoryDetailsRow IN
      SELECT   c.category_id, c.department_id, c.name
      FROM     category c
      JOIN     product_category pc
                 ON c.category_id = pc.category_id
      WHERE    pc.product_id = inProductId
      ORDER BY category_id
    LOOP
      RETURN NEXT outProductCategoryDetailsRow;
    END LOOP;
  END;
$$;