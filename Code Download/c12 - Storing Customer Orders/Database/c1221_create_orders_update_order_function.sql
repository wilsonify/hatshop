-- Create orders_update_order function
CREATE FUNCTION orders_update_order(INTEGER, INTEGER, VARCHAR(255),
                                     VARCHAR(50), VARCHAR(50))
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inOrderId    ALIAS FOR $1;
    inStatus     ALIAS FOR $2;
    inComments   ALIAS FOR $3;
    inAuthCode   ALIAS FOR $4;
    inReference  ALIAS FOR $5;
    currentStatus INTEGER;
  BEGIN
    SELECT INTO currentStatus
           status
    FROM   orders
    WHERE  order_id = inOrderId;
    IF  inStatus != currentStatus AND (inStatus = 0 OR inStatus = 1) THEN
      UPDATE orders SET shipped_on = NULL WHERE order_id = inOrderId;
    ELSEIF inStatus != currentStatus AND inStatus = 2 THEN
      UPDATE orders SET shipped_on = NOW() WHERE order_id = inOrderId;
    END IF;
    UPDATE orders
    SET    status = inStatus, comments = inComments,
           auth_code = inAuthCode, reference = inReference
    WHERE  order_id = inOrderId;
  END;
$$;