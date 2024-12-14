-- Create catalog_update_department function
CREATE FUNCTION catalog_update_department(
                  INTEGER, VARCHAR(50), VARCHAR(1000))
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inDepartmentId ALIAS FOR $1;
    inName         ALIAS FOR $2;
    inDescription  ALIAS FOR $3;
  BEGIN
    UPDATE department
    SET    name = inName, description = inDescription
    WHERE  department_id = inDepartmentId;
  END;
$$;