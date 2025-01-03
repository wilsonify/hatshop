-- Create shopping_cart_add_product function
CREATE FUNCTION shopping_cart_add_product(CHAR(128), INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCartId        ALIAS FOR $1;
    inProductId     ALIAS FOR $2;
    productQuantity INTEGER;
  BEGIN
    SELECT INTO productQuantity
                quantity
    FROM   shopping_cart
    WHERE  cart_id = inCartId AND product_id = inProductId;
    IF productQuantity IS NULL THEN
      INSERT INTO shopping_cart(cart_id, product_id, quantity, added_on)
             VALUES (inCartId, inProductId , 1, NOW());
    ELSE
      UPDATE shopping_cart
      SET    quantity = quantity + 1, buy_now = true
      WHERE  cart_id = inCartId AND product_id = inProductId;
    END IF;
  END;
$$;

