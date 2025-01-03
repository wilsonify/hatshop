-- Create shopping_cart_get_saved_products function
CREATE FUNCTION shopping_cart_get_saved_products(CHAR(128))
RETURNS SETOF cart_saved_product LANGUAGE plpgsql AS $$
  DECLARE
    inCartId ALIAS FOR $1;
    outCartSavedProductRow cart_saved_product;
  BEGIN
    FOR outCartSavedProductRow IN
      SELECT     p.product_id, p.name,
                 COALESCE(NULLIF(p.discounted_price, 0), p.price) AS price
      FROM       shopping_cart sc
      INNER JOIN product p
                   ON sc.product_id = p.product_id
      WHERE      sc.cart_id = inCartId AND NOT buy_now
    LOOP
      RETURN NEXT outCartSavedProductRow;
    END LOOP;
  END;
$$;

