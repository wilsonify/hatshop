

-- Create customer_get_shipping_regions function
CREATE FUNCTION customer_get_shipping_regions()
RETURNS SETOF shipping_region LANGUAGE plpgsql AS $$
  DECLARE
    outShippingRegion shipping_region;
  BEGIN
    FOR outShippingRegion IN
      SELECT shipping_region_id, shipping_region
      FROM   shipping_region
    LOOP
      RETURN NEXT outShippingRegion;
    END LOOP;
    RETURN;
  END;
$$;