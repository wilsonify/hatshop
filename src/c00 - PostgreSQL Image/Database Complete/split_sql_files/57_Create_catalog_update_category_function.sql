-- Create catalog_update_category function
CREATE FUNCTION catalog_update_category(
                  INTEGER, VARCHAR(50), VARCHAR(1000))
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCategoryId  ALIAS FOR $1;
    inName        ALIAS FOR $2;
    inDescription ALIAS FOR $3;
  BEGIN
    UPDATE category
    SET    name = inName, description = inDescription
    WHERE  category_id = inCategoryId;
  END;
$$;

