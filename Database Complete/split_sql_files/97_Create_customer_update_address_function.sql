-- Create customer_update_address function
CREATE FUNCTION customer_update_address(INTEGER, VARCHAR(100),
                  VARCHAR(100), VARCHAR(100), VARCHAR(100),
                  VARCHAR(100), VARCHAR(100), INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCustomerId       ALIAS FOR $1;
    inAddress1         ALIAS FOR $2;
    inAddress2         ALIAS FOR $3;
    inCity             ALIAS FOR $4;
    inRegion           ALIAS FOR $5;
    inPostalCode       ALIAS FOR $6;
    inCountry          ALIAS FOR $7;
    inShippingRegionId ALIAS FOR $8;
  BEGIN
    UPDATE customer
    SET    address_1 = inAddress1, address_2 = inAddress2, city = inCity,
           region = inRegion, postal_code = inPostalCode,
           country = inCountry, shipping_region_id = inShippingRegionId
    WHERE  customer_id = inCustomerId;
  END;
$$;

