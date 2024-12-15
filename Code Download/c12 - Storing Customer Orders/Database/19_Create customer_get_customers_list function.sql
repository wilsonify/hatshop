-- Create customer_get_customers_list function
CREATE FUNCTION customer_get_customers_list()
RETURNS SETOF customer_list LANGUAGE plpgsql AS $$
  DECLARE
    outCustomerListRow customer_list;
  BEGIN
    FOR outCustomerListRow IN
      SELECT customer_id, name FROM customer ORDER BY name ASC
    LOOP
      RETURN NEXT outCustomerListRow;
    END LOOP;
  END;
$$;