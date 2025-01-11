-- Create catalog_get_product_info function
CREATE FUNCTION catalog_get_product_info(INTEGER)
RETURNS product_info LANGUAGE plpgsql AS $$
  DECLARE
    inProductId ALIAS FOR $1;
    outProductInfoRow product_info;
  BEGIN
    SELECT INTO outProductInfoRow
           name, image, thumbnail, display
    FROM   product
    WHERE  product_id = inProductId;
    RETURN outProductInfoRow;
  END;
$$;
