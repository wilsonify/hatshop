
-- Populate product_category table
INSERT INTO product_category VALUES (1, 1);
INSERT INTO product_category VALUES (2, 1);
INSERT INTO product_category VALUES (3, 1);
INSERT INTO product_category VALUES (4, 1);
INSERT INTO product_category VALUES (5, 1);
INSERT INTO product_category VALUES (6, 1);
INSERT INTO product_category VALUES (7, 1);
INSERT INTO product_category VALUES (8, 2);
INSERT INTO product_category VALUES (9, 2);
INSERT INTO product_category VALUES (10, 2);
INSERT INTO product_category VALUES (11, 2);
INSERT INTO product_category VALUES (12, 2);
INSERT INTO product_category VALUES (13, 2);
INSERT INTO product_category VALUES (14, 2);
INSERT INTO product_category VALUES (15, 2);
INSERT INTO product_category VALUES (16, 3);
INSERT INTO product_category VALUES (17, 3);
INSERT INTO product_category VALUES (18, 3);
INSERT INTO product_category VALUES (19, 3);
INSERT INTO product_category VALUES (20, 3);
INSERT INTO product_category VALUES (21, 3);
INSERT INTO product_category VALUES (22, 3);
INSERT INTO product_category VALUES (23, 4);
INSERT INTO product_category VALUES (24, 4);
INSERT INTO product_category VALUES (25, 4);
INSERT INTO product_category VALUES (26, 4);
INSERT INTO product_category VALUES (8, 5);
INSERT INTO product_category VALUES (27, 5);
INSERT INTO product_category VALUES (28, 5);
INSERT INTO product_category VALUES (29, 5);
INSERT INTO product_category VALUES (30, 6);
INSERT INTO product_category VALUES (31, 6);
INSERT INTO product_category VALUES (32, 6);
INSERT INTO product_category VALUES (33, 6);
INSERT INTO product_category VALUES (34, 6);
INSERT INTO product_category VALUES (35, 6);
INSERT INTO product_category VALUES (26, 7);
INSERT INTO product_category VALUES (36, 7);
INSERT INTO product_category VALUES (37, 7);
INSERT INTO product_category VALUES (38, 7);
INSERT INTO product_category VALUES (39, 7);
INSERT INTO product_category VALUES (40, 7);
INSERT INTO product_category VALUES (41, 7);
INSERT INTO product_category VALUES (42, 7);
INSERT INTO product_category VALUES (43, 7);
INSERT INTO product_category VALUES (44, 7);
INSERT INTO product_category VALUES (45, 7);

-- Create department_details type
CREATE TYPE department_details AS
(
  name        VARCHAR(50),
  description VARCHAR(1000)
);

-- Create catalog_get_department_details function
CREATE FUNCTION catalog_get_department_details(INTEGER)
RETURNS department_details LANGUAGE plpgsql AS $$
  DECLARE
    inDepartmentId ALIAS FOR $1;
    outDepartmentDetailsRow department_details;
  BEGIN
    SELECT INTO outDepartmentDetailsRow
           name, description
    FROM   department
    WHERE  department_id = inDepartmentId;
    RETURN outDepartmentDetailsRow;
  END;
$$;

-- Create category_list type
CREATE TYPE category_list AS
(
  category_id INTEGER,
  name        VARCHAR(50)
);

-- Create catalog_get_categories_list function
CREATE FUNCTION catalog_get_categories_list(INTEGER)
RETURNS SETOF category_list LANGUAGE plpgsql AS $$
  DECLARE
    inDepartmentId ALIAS FOR $1;
    outCategoryListRow category_list;
  BEGIN
    FOR outCategoryListRow IN
      SELECT   category_id, name
      FROM     category
      WHERE    department_id = inDepartmentId
      ORDER BY category_id
    LOOP
      RETURN NEXT outCategoryListRow;
    END LOOP;
  END;
$$;

-- Create category_details type
CREATE TYPE category_details AS
(
  name        VARCHAR(50),
  description VARCHAR(1000)
);

-- Create catalog_get_category_details function
CREATE FUNCTION catalog_get_category_details(INTEGER)
RETURNS category_details LANGUAGE plpgsql AS $$
  DECLARE
    inCategoryId ALIAS FOR $1;
    outCategoryDetailsRow category_details;
  BEGIN
    SELECT INTO outCategoryDetailsRow
           name, description
    FROM   category
    WHERE  category_id = inCategoryId;
    RETURN outCategoryDetailsRow;
  END;
$$;

-- Create catalog_count_products_in_category function
CREATE FUNCTION catalog_count_products_in_category(INTEGER)
RETURNS INTEGER LANGUAGE plpgsql AS $$
  DECLARE
    inCategoryId ALIAS FOR $1;
    outCategoriesCount INTEGER;
  BEGIN
    SELECT     INTO outCategoriesCount
               count(*)
    FROM       product p
    INNER JOIN product_category pc
                 ON p.product_id = pc.product_id
    WHERE      pc.category_id = inCategoryId;
    RETURN outCategoriesCount;
  END;
$$;

-- Create product_list type
CREATE TYPE product_list AS
(
  product_id       INTEGER,
  name             VARCHAR(50),
  description      VARCHAR(1000),
  price            NUMERIC(10, 2),
  discounted_price NUMERIC(10, 2),
  thumbnail        VARCHAR(150)
);

-- Create catalog_get_products_in_category function
CREATE FUNCTION catalog_get_products_in_category(
                  INTEGER, INTEGER, INTEGER, INTEGER)
RETURNS SETOF product_list LANGUAGE plpgsql AS $$
  DECLARE
    inCategoryId                    ALIAS FOR $1;
    inShortProductDescriptionLength ALIAS FOR $2;
    inProductsPerPage               ALIAS FOR $3;
    inStartItem                     ALIAS FOR $4;
    outProductListRow product_list;
  BEGIN
    FOR outProductListRow IN
      SELECT     p.product_id, p.name, p.description, p.price,
                 p.discounted_price, p.thumbnail
      FROM       product p
      INNER JOIN product_category pc
                   ON p.product_id = pc.product_id
      WHERE      pc.category_id = inCategoryId
      ORDER BY   p.product_id
      LIMIT      inProductsPerPage
      OFFSET     inStartItem
    LOOP
      IF char_length(outProductListRow.description) >
         inShortProductDescriptionLength THEN
        outProductListRow.description :=
          substring(outProductListRow.description, 1,
                    inShortProductDescriptionLength) || '...';
      END IF;
      RETURN NEXT outProductListRow;
    END LOOP;
  END;
$$;

-- Create catalog_count_products_on_department function
CREATE FUNCTION catalog_count_products_on_department(INTEGER)
RETURNS INTEGER LANGUAGE plpgsql AS $$
  DECLARE
    inDepartmentId ALIAS FOR $1;
    outProductsOnDepartmentCount INTEGER;
  BEGIN
    SELECT DISTINCT INTO outProductsOnDepartmentCount
                    count(*)
    FROM            product p
    INNER JOIN      product_category pc
                      ON p.product_id = pc.product_id
    INNER JOIN      category c
                      ON pc.category_id = c.category_id
    WHERE           (p.display = 2 OR p.display = 3)
                    AND c.department_id = inDepartmentId;
    RETURN outProductsOnDepartmentCount;
  END;
$$;

-- Create catalog_get_products_on_department function
CREATE FUNCTION catalog_get_products_on_department(
                  INTEGER, INTEGER, INTEGER, INTEGER)
RETURNS SETOF product_list LANGUAGE plpgsql AS $$
  DECLARE
    inDepartmentId                  ALIAS FOR $1;
    inShortProductDescriptionLength ALIAS FOR $2;
    inProductsPerPage               ALIAS FOR $3;
    inStartItem                     ALIAS FOR $4;
    outProductListRow product_list;
  BEGIN
    FOR outProductListRow IN
      SELECT DISTINCT p.product_id, p.name, p.description, p.price,
                      p.discounted_price, p.thumbnail
      FROM            product p
      INNER JOIN      product_category pc
                        ON p.product_id = pc.product_id
      INNER JOIN      category c
                        ON pc.category_id = c.category_id
      WHERE           (p.display = 2 OR p.display = 3)
                      AND c.department_id = inDepartmentId
      ORDER BY        p.product_id
      LIMIT           inProductsPerPage
      OFFSET          inStartItem
    LOOP
      IF char_length(outProductListRow.description) >
         inShortProductDescriptionLength THEN
        outProductListRow.description :=
          substring(outProductListRow.description, 1,
                    inShortProductDescriptionLength) || '...';
      END IF;
      RETURN NEXT outProductListRow;
    END LOOP;
  END;
$$;

-- Create catalog_count_products_on_catalog function
CREATE FUNCTION catalog_count_products_on_catalog()
RETURNS INTEGER LANGUAGE plpgsql AS $$
  DECLARE
    outProductsOnCatalogCount INTEGER;
  BEGIN
      SELECT INTO outProductsOnCatalogCount
             count(*)
      FROM   product
      WHERE  display = 1 OR display = 3;
      RETURN outProductsOnCatalogCount;
  END;
$$;

-- Create catalog_get_products_on_catalog function
CREATE FUNCTION catalog_get_products_on_catalog(INTEGER, INTEGER, INTEGER)
RETURNS SETOF product_list LANGUAGE plpgsql AS $$
  DECLARE
    inShortProductDescriptionLength ALIAS FOR $1;
    inProductsPerPage               ALIAS FOR $2;
    inStartItem                     ALIAS FOR $3;
    outProductListRow product_list;
  BEGIN
    FOR outProductListRow IN
      SELECT   product_id, name, description, price,
               discounted_price, thumbnail
      FROM     product
      WHERE    display = 1 OR display = 3
      ORDER BY product_id
      LIMIT    inProductsPerPage
      OFFSET   inStartItem
    LOOP
      IF char_length(outProductListRow.description) >
         inShortProductDescriptionLength THEN
        outProductListRow.description :=
          substring(outProductListRow.description, 1,
                    inShortProductDescriptionLength) || '...';
      END IF;
      RETURN NEXT outProductListRow;
    END LOOP;
  END;
$$;

-- Create product_details type
CREATE TYPE product_details AS
(
  product_id       INTEGER,
  name             VARCHAR(50),
  description      VARCHAR(1000),
  price            NUMERIC(10, 2),
  discounted_price NUMERIC(10, 2),
  image            VARCHAR(150)
);

-- Create catalog_get_product_details function
CREATE FUNCTION catalog_get_product_details(INTEGER)
RETURNS product_details LANGUAGE plpgsql AS $$
  DECLARE
    inProductId ALIAS FOR $1;
    outProductDetailsRow product_details;
  BEGIN
    SELECT INTO outProductDetailsRow
           product_id, name, description,
           price, discounted_price, image
    FROM   product
    WHERE  product_id = inProductId;
    RETURN outProductDetailsRow;
  END;
$$;
