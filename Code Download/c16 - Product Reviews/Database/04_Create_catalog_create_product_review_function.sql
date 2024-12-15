
-- Create catalog_create_product_review function
CREATE FUNCTION catalog_create_product_review(INTEGER, INTEGER, TEXT,
                                              SMALLINT)
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCustomerId ALIAS FOR $1;
    inProductId  ALIAS FOR $2;
    inReview     ALIAS FOR $3;
    inRating     ALIAS FOR $4;
  BEGIN
    INSERT INTO review (customer_id, product_id, review, rating, created_on)
           VALUES (inCustomerId, inProductId, inReview, inRating, NOW());
  END;
$$;