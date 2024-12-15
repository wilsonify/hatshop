

-- Update orders_get_most_recent_orders function
CREATE OR REPLACE FUNCTION orders_get_most_recent_orders(INTEGER)
RETURNS SETOF order_short_details LANGUAGE plpgsql AS $$
  DECLARE
    inHowMany ALIAS FOR $1;
    outOrderShortDetailsRow order_short_details;
  BEGIN
    FOR outOrderShortDetailsRow IN
      SELECT     o.order_id, o.total_amount, o.created_on,
                 o.shipped_on, o.status, c.name
      FROM       orders o
      INNER JOIN customer c
                   ON o.customer_id = c.customer_id
      ORDER BY   o.created_on DESC
      LIMIT      inHowMany
    LOOP
      RETURN NEXT outOrderShortDetailsRow;
    END LOOP;
  END;
$$;