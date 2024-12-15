

-- Create shopping_cart_move_product_to_cart function
CREATE FUNCTION shopping_cart_move_product_to_cart(CHAR(32), INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCartId    ALIAS FOR $1;
    inProductId ALIAS FOR $2;
  BEGIN
    UPDATE shopping_cart
    SET    buy_now = true, added_on = NOW()
    WHERE  cart_id = inCartId AND product_id = inProductId;
  END;
$$;