-- Create orders_get_audit_trail function
CREATE FUNCTION orders_get_audit_trail(INTEGER)
RETURNS SETOF audit LANGUAGE plpgsql AS $$
  DECLARE
    inOrderId ALIAS FOR $1;
    outAuditRow audit;
  BEGIN
    FOR outAuditRow IN
      SELECT audit_id, order_id, created_on, message, message_number
      FROM   audit
      WHERE  order_id = inOrderId
    LOOP
      RETURN NEXT outAuditRow;
    END LOOP;
  END;
$$;

