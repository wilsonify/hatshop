-- Create orders_set_date_shipped function
CREATE FUNCTION orders_set_date_shipped(INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inOrderId ALIAS FOR $1;
  BEGIN
    UPDATE orders SET shipped_on = NOW() WHERE order_id = inOrderId;
  END;
$$;

