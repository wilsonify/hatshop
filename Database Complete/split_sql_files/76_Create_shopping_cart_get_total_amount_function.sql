-- Create shopping_cart_get_total_amount function
CREATE FUNCTION shopping_cart_get_total_amount(CHAR(128))
RETURNS NUMERIC(10, 2) LANGUAGE plpgsql AS $$
  DECLARE
    inCartId ALIAS FOR $1;
    outTotalAmount NUMERIC(10, 2);
  BEGIN
    SELECT     INTO outTotalAmount
               SUM(COALESCE(NULLIF(p.discounted_price, 0), p.price)
                   * sc.quantity)
    FROM       shopping_cart sc
    INNER JOIN product p
                 ON sc.product_id = p.product_id
    WHERE      sc.cart_id = inCartId AND sc.buy_now;
    RETURN outTotalAmount;
  END;
$$;

