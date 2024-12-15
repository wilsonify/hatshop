
-- Create customer_add function
CREATE FUNCTION customer_add(
                  VARCHAR(50), VARCHAR(100), VARCHAR(50))
RETURNS INTEGER LANGUAGE plpgsql AS $$
  DECLARE
    inName     ALIAS FOR $1;
    inEmail    ALIAS FOR $2;
    inPassword ALIAS FOR $3;
    outCustomerId INTEGER;
  BEGIN
    INSERT INTO customer (name, email, password)
           VALUES (inName, inEmail, inPassword);
    SELECT INTO outCustomerId
           currval('customer_customer_id_seq');
    RETURN outCustomerId;
  END;
$$;