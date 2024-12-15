-- Create orders_set_auth_code function
CREATE FUNCTION orders_set_auth_code(INTEGER, VARCHAR(50), VARCHAR(50))
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inOrderId   ALIAS FOR $1;
    inAuthCode  ALIAS FOR $2;
    inReference ALIAS FOR $3;
  BEGIN
    UPDATE orders
    SET    auth_code = inAuthCode, reference = inReference
    WHERE  order_id = inOrderId;
  END;
$$;

