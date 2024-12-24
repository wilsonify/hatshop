
-- Create orders_get_shipping_info function
CREATE FUNCTION orders_get_shipping_info(INTEGER)
RETURNS SETOF shipping LANGUAGE plpgsql AS $$
  DECLARE
    inShippingRegionId ALIAS FOR $1;
    outShippingRow shipping;
  BEGIN
    FOR outShippingRow IN
      SELECT shipping_id, shipping_type, shipping_cost, shipping_region_id
      FROM   shipping
      WHERE  shipping_region_id = inShippingRegionId
    LOOP
      RETURN NEXT outShippingRow;
    END LOOP;
  END;
$$;