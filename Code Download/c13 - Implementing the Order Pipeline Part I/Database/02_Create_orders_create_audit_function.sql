-- Create orders_create_audit function
CREATE FUNCTION orders_create_audit(INTEGER, TEXT, INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inOrderId       ALIAS FOR $1;
    inMessage       ALIAS FOR $2;
    inMessageNumber ALIAS FOR $3;
  BEGIN
    INSERT INTO audit (order_id, created_on, message, message_number)
           VALUES (inOrderId, NOW(), inMessage, inMessageNumber);
  END;
$$;
