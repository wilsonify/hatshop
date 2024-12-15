-- Create catalog_update_product function
CREATE FUNCTION catalog_update_product(INTEGER, VARCHAR(50),
                  VARCHAR(1000), NUMERIC(10, 2), NUMERIC(10, 2))
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inProductId       ALIAS FOR $1;
    inName            ALIAS FOR $2;
    inDescription     ALIAS FOR $3;
    inPrice           ALIAS FOR $4;
    inDiscountedPrice ALIAS FOR $5;
  BEGIN
    UPDATE product
    SET    name = inName, description = inDescription, price = inPrice,
           discounted_price = inDiscountedPrice,
           search_vector = (setweight(to_tsvector(inName), 'A')
                            || to_tsvector(inDescription))
    WHERE  product_id = inProductId;
  END;
$$;

