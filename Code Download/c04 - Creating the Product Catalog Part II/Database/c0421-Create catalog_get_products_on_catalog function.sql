

-- Create catalog_get_products_on_catalog function
CREATE FUNCTION catalog_get_products_on_catalog(INTEGER, INTEGER, INTEGER)
RETURNS SETOF product_list LANGUAGE plpgsql AS $$
  DECLARE
    inShortProductDescriptionLength ALIAS FOR $1;
    inProductsPerPage               ALIAS FOR $2;
    inStartItem                     ALIAS FOR $3;
    outProductListRow product_list;
  BEGIN
    FOR outProductListRow IN
      SELECT   product_id, name, description, price,
               discounted_price, thumbnail
      FROM     product
      WHERE    display = 1 OR display = 3
      ORDER BY product_id
      LIMIT    inProductsPerPage
      OFFSET   inStartItem
    LOOP
      IF char_length(outProductListRow.description) >
         inShortProductDescriptionLength THEN
        outProductListRow.description :=
          substring(outProductListRow.description, 1,
                    inShortProductDescriptionLength) || '...';
      END IF;
      RETURN NEXT outProductListRow;
    END LOOP;
  END;
$$;
