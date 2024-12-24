-- Create catalog_get_products_on_department function
CREATE FUNCTION catalog_get_products_on_department(
                  INTEGER, INTEGER, INTEGER, INTEGER)
RETURNS SETOF product_list LANGUAGE plpgsql AS $$
  DECLARE
    inDepartmentId                  ALIAS FOR $1;
    inShortProductDescriptionLength ALIAS FOR $2;
    inProductsPerPage               ALIAS FOR $3;
    inStartItem                     ALIAS FOR $4;
    outProductListRow product_list;
  BEGIN
    FOR outProductListRow IN
      SELECT DISTINCT p.product_id, p.name, p.description, p.price,
                      p.discounted_price, p.thumbnail
      FROM            product p
      INNER JOIN      product_category pc
                        ON p.product_id = pc.product_id
      INNER JOIN      category c
                        ON pc.category_id = c.category_id
      WHERE           (p.display = 2 OR p.display = 3)
                      AND c.department_id = inDepartmentId
      ORDER BY        p.product_id
      LIMIT           inProductsPerPage
      OFFSET          inStartItem
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

