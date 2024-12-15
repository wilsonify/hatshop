-- Create customer_update_credit_card function
CREATE FUNCTION customer_update_credit_card(INTEGER, TEXT)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCustomerId ALIAS FOR $1;
    inCreditCard ALIAS FOR $2;
  BEGIN
    UPDATE customer
    SET    credit_card = inCreditCard
    WHERE  customer_id = inCustomerId;
  END;
$$;

