-- Create shopping_cart_delete_old_carts function
CREATE FUNCTION shopping_cart_delete_old_carts(INTEGER)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inDays ALIAS FOR $1;
  BEGIN
    DELETE FROM shopping_cart
    WHERE cart_id IN
         (SELECT    cart_id
           FROM     shopping_cart
           GROUP BY cart_id
           HAVING   ((NOW() - (inDays||' DAYS')::INTERVAL) >= MAX(added_on)));
  END;
$$;