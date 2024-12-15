-- Create catalog_get_categories_list function
CREATE FUNCTION catalog_get_categories_list(INTEGER)
RETURNS SETOF category_list LANGUAGE plpgsql AS $$
  DECLARE
    inDepartmentId ALIAS FOR $1;
    outCategoryListRow category_list;
  BEGIN
    FOR outCategoryListRow IN
      SELECT   category_id, name
      FROM     category
      WHERE    department_id = inDepartmentId
      ORDER BY category_id
    LOOP
      RETURN NEXT outCategoryListRow;
    END LOOP;
  END;
$$;

