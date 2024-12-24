-- Create catalog_set_thumbnail function
CREATE FUNCTION catalog_set_thumbnail(INTEGER, VARCHAR(150))
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inProductId ALIAS FOR $1;
    inThumbnail ALIAS FOR $2;
  BEGIN
    UPDATE product
    SET    thumbnail = inThumbnail
    WHERE  product_id = inProductId;
  END;
$$;

