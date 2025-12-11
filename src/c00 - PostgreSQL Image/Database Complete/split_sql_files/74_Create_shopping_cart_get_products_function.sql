-- Create shopping_cart_get_products function
-- Updated to accept cart_products_type parameter:
--   1 = active cart products (buy_now = true)
--   2 = saved for later products (buy_now = false)
DROP FUNCTION IF EXISTS shopping_cart_get_products(CHAR(128));
DROP FUNCTION IF EXISTS shopping_cart_get_products(CHAR(128), INTEGER);

CREATE FUNCTION shopping_cart_get_products(CHAR(128), INTEGER)
RETURNS SETOF cart_product LANGUAGE plpgsql AS $$
  DECLARE
    inCartId ALIAS FOR $1;
    inCartProductsType ALIAS FOR $2;
    outCartProductRow cart_product;
  BEGIN
    FOR outCartProductRow IN
      SELECT     p.product_id, p.name,
                 COALESCE(NULLIF(p.discounted_price, 0), p.price) AS price,
                 sc.quantity,
                 COALESCE(NULLIF(p.discounted_price, 0),
                          p.price) * sc.quantity AS subtotal
      FROM       shopping_cart sc
      INNER JOIN product p
                   ON sc.product_id = p.product_id
      WHERE      sc.cart_id = inCartId 
                 AND ((inCartProductsType = 1 AND buy_now) 
                      OR (inCartProductsType = 2 AND NOT buy_now))
    LOOP
      RETURN NEXT outCartProductRow;
    END LOOP;
  END;
$$;
