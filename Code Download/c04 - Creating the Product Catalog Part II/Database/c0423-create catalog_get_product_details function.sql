
-- Create catalog_get_product_details function
CREATE FUNCTION catalog_get_product_details(INTEGER)
RETURNS product_details LANGUAGE plpgsql AS $$
  DECLARE
    inProductId ALIAS FOR $1;
    outProductDetailsRow product_details;
  BEGIN
    SELECT INTO outProductDetailsRow
           product_id, name, description,
           price, discounted_price, image
    FROM   product
    WHERE  product_id = inProductId;
    RETURN outProductDetailsRow;
  END;
$$;
