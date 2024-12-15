-- Create shopping_cart_update function
CREATE FUNCTION shopping_cart_update(CHAR(32), INTEGER[], INTEGER[])
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCartId     ALIAS FOR $1;
    inProductIds ALIAS FOR $2;
    inQuantities ALIAS FOR $3;
  BEGIN
    FOR i IN array_lower(inQuantities, 1)..array_upper(inQuantities, 1)
    LOOP
      IF inQuantities[i] > 0 THEN
        UPDATE shopping_cart
        SET    quantity = inQuantities[i], added_on = NOW()
        WHERE  cart_id = inCartId AND product_id = inProductIds[i];
      ELSE
        PERFORM shopping_cart_remove_product(inCartId, inProductIds[i]);
      END IF;
    END LOOP;
  END;
$$;

