-- Create shopping_cart_save_product_for_later function
CREATE FUNCTION shopping_cart_save_product_for_later(CHAR(32), INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCartId    ALIAS FOR $1;
    inProductId ALIAS FOR $2;
  BEGIN
    UPDATE shopping_cart
    SET    buy_now = false, quantity = 1
    WHERE  cart_id = inCartId AND product_id = inProductId;
  END;
$$;

