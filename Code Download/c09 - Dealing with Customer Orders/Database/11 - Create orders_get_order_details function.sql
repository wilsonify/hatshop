-- Create orders_get_order_details function
CREATE FUNCTION orders_get_order_details(INTEGER)
RETURNS SETOF order_details LANGUAGE plpgsql AS $$
  DECLARE
    inOrderId ALIAS FOR $1;
    outOrderDetailsRow order_details;
  BEGIN
    FOR outOrderDetailsRow IN
      SELECT order_id, product_id, product_name, quantity,
             unit_cost, (quantity * unit_cost) AS subtotal
      FROM   order_detail
      WHERE  order_id = inOrderId
    LOOP
      RETURN NEXT outOrderDetailsRow;
    END LOOP;
  END;
$$;
