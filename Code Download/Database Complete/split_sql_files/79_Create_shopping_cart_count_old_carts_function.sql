-- Create shopping_cart_count_old_carts function
CREATE FUNCTION shopping_cart_count_old_carts(INTEGER)
RETURNS INTEGER LANGUAGE plpgsql AS $$
  DECLARE
    inDays ALIAS FOR $1;
    outOldShoppingCartsCount INTEGER;
  BEGIN
    SELECT INTO outOldShoppingCartsCount
           COUNT(cart_id)
    FROM   (SELECT   cart_id
            FROM     shopping_cart
            GROUP BY cart_id
            HAVING   ((NOW() - ('1'||' DAYS')::INTERVAL) >= MAX(added_on)))
           AS old_carts;
    RETURN outOldShoppingCartsCount;
  END;
$$;

