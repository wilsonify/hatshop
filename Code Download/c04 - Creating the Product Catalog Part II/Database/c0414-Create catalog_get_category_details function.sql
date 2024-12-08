-- Create catalog_get_category_details function
CREATE FUNCTION catalog_get_category_details(INTEGER)
RETURNS category_details LANGUAGE plpgsql AS $$
  DECLARE
    inCategoryId ALIAS FOR $1;
    outCategoryDetailsRow category_details;
  BEGIN
    SELECT INTO outCategoryDetailsRow
           name, description
    FROM   category
    WHERE  category_id = inCategoryId;
    RETURN outCategoryDetailsRow;
  END;
$$;
