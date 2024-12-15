-- Create catalog_set_product_display_option function
CREATE FUNCTION catalog_set_product_display_option(INTEGER, SMALLINT)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inProductId ALIAS FOR $1;
    inDisplay   ALIAS FOR $2;
  BEGIN
    UPDATE product SET display = inDisplay WHERE product_id = inProductId;
  END;
$$;

