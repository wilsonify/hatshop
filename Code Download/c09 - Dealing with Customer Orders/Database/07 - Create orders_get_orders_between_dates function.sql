






-- Create orders_get_orders_between_dates function
CREATE FUNCTION orders_get_orders_between_dates(TIMESTAMP, TIMESTAMP)
RETURNS SETOF order_short_details LANGUAGE plpgsql AS $$
  DECLARE
    inStartDate ALIAS FOR $1;
    inEndDate   ALIAS FOR $2;
    outOrderShortDetailsRow order_short_details;
  BEGIN
    FOR outOrderShortDetailsRow IN
      SELECT   order_id, total_amount, created_on,
               shipped_on, status, customer_name
      FROM     orders
      WHERE    created_on >= inStartDate AND created_on <= inEndDate
      ORDER BY created_on DESC
    LOOP
      RETURN NEXT outOrderShortDetailsRow;
    END LOOP;
  END;
$$;