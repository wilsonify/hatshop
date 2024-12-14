
-- Create catalog_delete_department function
CREATE FUNCTION catalog_delete_department(INTEGER)
RETURNS SMALLINT LANGUAGE plpgsql AS $$
  DECLARE
    inDepartmentId ALIAS FOR $1;
    categoryRowsCount INTEGER;
  BEGIN
    SELECT INTO categoryRowsCount
           count(*)
    FROM   category
    WHERE  department_id = inDepartmentId;
    IF categoryRowsCount = 0 THEN
      DELETE FROM department WHERE department_id = inDepartmentId;
      RETURN 1;
    END IF;
    RETURN -1;
  END;
$$;
