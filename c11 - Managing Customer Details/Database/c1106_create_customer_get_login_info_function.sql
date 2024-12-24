
-- Create customer_get_login_info function
CREATE FUNCTION customer_get_login_info(VARCHAR(100))
RETURNS customer_login_info LANGUAGE plpgsql AS $$
  DECLARE
    inEmail ALIAS FOR $1;
    outCustomerLoginInfoRow customer_login_info;
  BEGIN
    SELECT INTO outCustomerLoginInfoRow
                customer_id, password
    FROM   customer
    WHERE  email = inEmail;
    RETURN outCustomerLoginInfoRow;
  END;
$$;
