-- Create orders_get_order_short_details function
CREATE FUNCTION orders_get_order_short_details(INTEGER)
RETURNS order_short_details LANGUAGE plpgsql AS $$
  DECLARE
    inOrderId ALIAS FOR $1;
    outOrderShortDetailsRow order_short_details;
  BEGIN
    SELECT INTO outOrderShortDetailsRow
                o.order_id, o.total_amount, o.created_on,
                o.shipped_on, o.status, c.name
    FROM        orders o
    INNER JOIN  customer c
                  ON o.customer_id = c.customer_id
    WHERE       o.order_id = inOrderId;
    RETURN outOrderShortDetailsRow;
  END;
$$;