-- Create customer_update_account function
CREATE FUNCTION customer_update_account(INTEGER, VARCHAR(50), VARCHAR(100),
                  VARCHAR(50), VARCHAR(100), VARCHAR(100), VARCHAR(100))
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCustomerId ALIAS FOR $1;
    inName       ALIAS FOR $2;
    inEmail      ALIAS FOR $3;
    inPassword   ALIAS FOR $4;
    inDayPhone   ALIAS FOR $5;
    inEvePhone   ALIAS FOR $6;
    inMobPhone   ALIAS FOR $7;
  BEGIN
    UPDATE customer
    SET    name = inName, email = inEmail,
           password = inPassword, day_phone = inDayPhone,
           eve_phone = inEvePhone, mob_phone = inMobPhone
    WHERE  customer_id = inCustomerId;
  END;
$$;

