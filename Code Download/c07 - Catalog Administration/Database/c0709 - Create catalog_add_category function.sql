-- Create catalog_add_category function
CREATE FUNCTION catalog_add_category(
                  INTEGER, VARCHAR(50), VARCHAR(1000))
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inDepartmentId ALIAS FOR $1;
    inName         ALIAS FOR $2;
    inDescription  ALIAS FOR $3;
  BEGIN
    INSERT INTO category (department_id, name, description)
           VALUES (inDepartmentId, inName, inDescription);
  END;
$$;