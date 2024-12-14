
-- Create catalog_add_product_to_category function
CREATE FUNCTION catalog_add_product_to_category(INTEGER, VARCHAR(50),
                  VARCHAR(1000), NUMERIC(10, 2))
RETURNS VOID LANGUAGE plpgsql AS $$
  DECLARE
    inCategoryId  ALIAS FOR $1;
    inName        ALIAS FOR $2;
    inDescription ALIAS FOR $3;
    inPrice       ALIAS FOR $4;
    productLastInsertId INTEGER;
  BEGIN
    INSERT INTO product (name, description, price, image, thumbnail,
                         search_vector)
           VALUES (inName, inDescription, inPrice, 'generic.jpg',
                   'generic.thumb.jpg',
                   (setweight(to_tsvector(inName), 'A')
                    || to_tsvector(inDescription)));
    SELECT INTO productLastInsertId currval('product_product_id_seq');
    INSERT INTO product_category (product_id, category_id)
           VALUES (productLastInsertId, inCategoryId);
  END;
$$;
