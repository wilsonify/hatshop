
-- Create catalog_count_products_on_catalog function
CREATE FUNCTION catalog_count_products_on_catalog()
RETURNS INTEGER LANGUAGE plpgsql AS $$
  DECLARE
    outProductsOnCatalogCount INTEGER;
  BEGIN
      SELECT INTO outProductsOnCatalogCount
             count(*)
      FROM   product
      WHERE  display = 1 OR display = 3;
      RETURN outProductsOnCatalogCount;
  END;
$$;
