-- Create shopping_cart_empty function
CREATE FUNCTION shopping_cart_empty(CHAR(32))
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCartId ALIAS FOR $1;
  BEGIN
    DELETE FROM shopping_cart WHERE cart_id = inCartId;
  END;
$$;

