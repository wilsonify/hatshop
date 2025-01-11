-- Create orders_update_status function
CREATE FUNCTION orders_update_status(INTEGER, INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inOrderId ALIAS FOR $1;
    inStatus  ALIAS FOR $2;
  BEGIN
    UPDATE orders SET status = inStatus WHERE order_id = inOrderId;
  END;
$$;