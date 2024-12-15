-- Create catalog_get_department_categories function
CREATE FUNCTION catalog_get_department_categories(INTEGER)
RETURNS SETOF department_category LANGUAGE plpgsql AS $$
  DECLARE
    inDepartmentId ALIAS FOR $1;
    outDepartmentCategoryRow department_category;
  BEGIN
    FOR outDepartmentCategoryRow IN
      SELECT   category_id, name, description
      FROM     category
      WHERE    department_id = inDepartmentId
      ORDER BY category_id
    LOOP
      RETURN NEXT outDepartmentCategoryRow;
    END LOOP;
  END;
$$;

