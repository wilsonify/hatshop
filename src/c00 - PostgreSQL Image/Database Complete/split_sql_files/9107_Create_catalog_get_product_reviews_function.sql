-- Create catalog_get_product_reviews function
CREATE FUNCTION catalog_get_product_reviews(INTEGER)
RETURNS SETOF review_info LANGUAGE plpgsql AS $$
  DECLARE
    inProductId ALIAS FOR $1;
    outReviewInfoRow review_info;
  BEGIN
    FOR outReviewInfoRow IN
      SELECT     c.name, r.review, r.rating, r.created_on
      FROM       review r
      INNER JOIN customer c
                   ON c.customer_id = r.customer_id
      WHERE      r.product_id = inProductId
      ORDER BY   r.created_on DESC
    LOOP
      RETURN NEXT outReviewInfoRow;
    END LOOP;
  END;
$$;

