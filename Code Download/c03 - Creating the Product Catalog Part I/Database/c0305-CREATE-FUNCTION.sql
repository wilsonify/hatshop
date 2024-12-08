
CREATE FUNCTION catalog_get_departments_list()
RETURNS SETOF department_list LANGUAGE plpgsql AS $$
  DECLARE
    outDepartmentListRow department_list;
  BEGIN
    FOR outDepartmentListRow IN
      SELECT department_id, name 
      FROM department 
      ORDER BY department_id
    LOOP
      RETURN NEXT outDepartmentListRow;
    END LOOP;
  END;
$$;
