

-- Create customer_get_customer function
CREATE FUNCTION customer_get_customer(INTEGER)
RETURNS customer LANGUAGE plpgsql AS $$
  DECLARE
    inCustomerId ALIAS FOR $1;
    outCustomerRow customer;
  BEGIN
    SELECT INTO outCustomerRow
                customer_id, name, email, password, credit_card,
                address_1, address_2, city, region, postal_code, country,
                shipping_region_id, day_phone, eve_phone, mob_phone
    FROM   customer
    WHERE  customer_id = inCustomerId;
    RETURN outCustomerRow;
  END;
$$;