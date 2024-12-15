-- Create catalog_get_department_details function
CREATE FUNCTION catalog_get_department_details(INTEGER)
RETURNS department_details LANGUAGE plpgsql AS $$
  DECLARE
    inDepartmentId ALIAS FOR $1;
    outDepartmentDetailsRow department_details;
  BEGIN
    SELECT INTO outDepartmentDetailsRow
           name, description
    FROM   department
    WHERE  department_id = inDepartmentId;
    RETURN outDepartmentDetailsRow;
  END;
$$;

