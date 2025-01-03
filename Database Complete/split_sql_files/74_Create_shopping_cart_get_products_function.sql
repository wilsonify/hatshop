-- Create shopping_cart_get_products function
CREATE FUNCTION shopping_cart_get_products(CHAR(128))
RETURNS SETOF cart_product LANGUAGE plpgsql AS $$
  DECLARE
    inCartId ALIAS FOR $1;
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
      WHERE      sc.cart_id = inCartId AND buy_now
    LOOP
      RETURN NEXT outCartProductRow;
    END LOOP;
  END;
$$;

