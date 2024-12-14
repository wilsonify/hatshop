

-- Create catalog_set_image function
CREATE FUNCTION catalog_set_image(INTEGER, VARCHAR(150))
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inProductId ALIAS FOR $1;
    inImage     ALIAS FOR $2;
  BEGIN
    UPDATE product SET image = inImage WHERE product_id = inProductId;
  END;
$$;