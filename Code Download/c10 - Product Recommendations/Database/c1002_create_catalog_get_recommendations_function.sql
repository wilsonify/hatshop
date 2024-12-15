
-- Create catalog_get_recommendations function
CREATE FUNCTION catalog_get_recommendations(INTEGER, INTEGER)
RETURNS SETOF product_recommendation LANGUAGE plpgsql AS $$
  DECLARE
    inProductId                     ALIAS FOR $1;
    inShortProductDescriptionLength ALIAS FOR $2;
    outProductRecommendationRow product_recommendation;
  BEGIN
    FOR outProductRecommendationRow IN
      SELECT product_id, name, description
      FROM   product
      WHERE  product_id IN
            (SELECT   od2.product_id
             FROM     order_detail od1
             JOIN     order_detail od2
                        ON od1.order_id = od2.order_id
             WHERE    od1.product_id = inProductId
                      AND od2.product_id != inProductId
             GROUP BY od2.product_id
             ORDER BY COUNT(od2.product_id) DESC
             LIMIT    5)
    LOOP
      IF char_length(outProductRecommendationRow.description) >
         inShortProductDescriptionLength THEN
        outProductRecommendationRow.description :=
          substring(outProductRecommendationRow.description, 1,
                    inShortProductDescriptionLength) || '...';
      END IF;
      RETURN NEXT outProductRecommendationRow;
    END LOOP;
  END;
$$;