-- Update orders_get_order_info function
CREATE OR REPLACE FUNCTION orders_get_order_info(INTEGER)
RETURNS orders LANGUAGE plpgsql AS $$
  DECLARE
    inOrderId ALIAS FOR $1;
    outOrdersRow orders;
  BEGIN
    SELECT INTO outOrdersRow
                order_id, total_amount, created_on, shipped_on, status,
                comments, customer_id, auth_code, reference
    FROM   orders
    WHERE  order_id = inOrderId;
    RETURN outOrdersRow;
  END;
$$;