-- Create orders_get_order_info function
CREATE FUNCTION orders_get_order_info(INTEGER)
RETURNS order_info LANGUAGE plpgsql AS $$
  DECLARE
    inOrderId ALIAS FOR $1;
    outOrderInfoRow order_info;
  BEGIN
    SELECT INTO outOrderInfoRow
                o.order_id, o.total_amount, o.created_on, o.shipped_on,
                o.status, o.comments, o.customer_id, o.auth_code,
                o.reference, o.shipping_id, s.shipping_type, s.shipping_cost,
                o.tax_id, t.tax_type, t.tax_percentage
    FROM       orders o
    INNER JOIN tax t
                 ON t.tax_id = o.tax_id
    INNER JOIN shipping s
                 ON s.shipping_id = o.shipping_id
    WHERE      o.order_id = inOrderId;
    RETURN outOrderInfoRow;
  END;
$$;