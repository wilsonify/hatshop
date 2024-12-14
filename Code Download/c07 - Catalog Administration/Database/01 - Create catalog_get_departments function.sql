-- Create catalog_get_departments function
CREATE FUNCTION catalog_get_departments()
RETURNS SETOF department LANGUAGE plpgsql AS $$
  DECLARE
    outDepartmentRow department;
  BEGIN
    FOR outDepartmentRow IN
      SELECT   department_id, name, description
      FROM     department
      ORDER BY department_id
    LOOP
      RETURN NEXT outDepartmentRow;
    END LOOP;
  END;
$$;