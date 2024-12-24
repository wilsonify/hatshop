-- Create shopping_cart_remove_product function
CREATE FUNCTION shopping_cart_remove_product(CHAR(32), INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCartId    ALIAS FOR $1;
    inProductId ALIAS FOR $2;
  BEGIN
    DELETE FROM shopping_cart
    WHERE  cart_id = inCartId AND product_id = inProductId;
  END;
$$;

