-- Create catalog_get_category_products function
CREATE FUNCTION catalog_get_category_products(INTEGER)
RETURNS SETOF category_product LANGUAGE plpgsql AS $$
  DECLARE
    inCategoryId ALIAS FOR $1;
    outCategoryProductRow category_product;
  BEGIN
    FOR outCategoryProductRow IN
      SELECT     p.product_id, p.name, p.description, p.price,
                 p.discounted_price
      FROM       product p
      INNER JOIN product_category pc
                   ON p.product_id = pc.product_id
      WHERE      pc.category_id = inCategoryId
      ORDER BY   p.product_id
    LOOP
      RETURN NEXT outCategoryProductRow;
    END LOOP;
  END;
$$;
