# PostgreSQL Functions for HatShop Database - Catalog Functions
# Uses cyrilgdn/postgresql provider

# Get departments list function
resource "postgresql_script" "func_catalog_get_departments_list" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_departments_list"
  depends_on = [
    postgresql_script.table_department,
    postgresql_script.type_department_list
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_departments_list()
    RETURNS SETOF department_list LANGUAGE plpgsql AS $$
      DECLARE
        outDepartmentListRow department_list;
      BEGIN
        FOR outDepartmentListRow IN
          SELECT department_id, name 
          FROM department 
          ORDER BY department_id
        LOOP
          RETURN NEXT outDepartmentListRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_departments_list() CASCADE;"
}

# Get department details function
resource "postgresql_script" "func_catalog_get_department_details" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_department_details"
  depends_on = [
    postgresql_script.table_department,
    postgresql_script.type_department_details
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_department_details(INTEGER)
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
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_department_details(INTEGER) CASCADE;"
}

# Get categories list function
resource "postgresql_script" "func_catalog_get_categories_list" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_categories_list"
  depends_on = [
    postgresql_script.table_category,
    postgresql_script.type_category_list
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_categories_list(INTEGER)
    RETURNS SETOF category_list LANGUAGE plpgsql AS $$
      DECLARE
        inDepartmentId ALIAS FOR $1;
        outCategoryListRow category_list;
      BEGIN
        FOR outCategoryListRow IN
          SELECT category_id, name 
          FROM   category 
          WHERE  department_id = inDepartmentId
          ORDER BY category_id
        LOOP
          RETURN NEXT outCategoryListRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_categories_list(INTEGER) CASCADE;"
}

# Get category details function
resource "postgresql_script" "func_catalog_get_category_details" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_category_details"
  depends_on = [
    postgresql_script.table_category,
    postgresql_script.type_category_details
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_category_details(INTEGER)
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
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_category_details(INTEGER) CASCADE;"
}

# Count products in category function
resource "postgresql_script" "func_catalog_count_products_in_category" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_count_products_in_category"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.table_product_category
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_count_products_in_category(INTEGER)
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
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_count_products_in_category(INTEGER) CASCADE;"
}

# Get products in category function
resource "postgresql_script" "func_catalog_get_products_in_category" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_products_in_category"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.table_product_category,
    postgresql_script.type_product_list
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_products_in_category(INTEGER, INTEGER, INTEGER, INTEGER)
    RETURNS SETOF product_list LANGUAGE plpgsql AS $$
      DECLARE
        inCategoryId                    ALIAS FOR $1;
        inShortProductDescriptionLength ALIAS FOR $2;
        inProductsPerPage               ALIAS FOR $3;
        inStartPage                     ALIAS FOR $4;
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
          OFFSET     inStartPage
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
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_products_in_category(INTEGER, INTEGER, INTEGER, INTEGER) CASCADE;"
}

# Count products on department function
resource "postgresql_script" "func_catalog_count_products_on_department" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_count_products_on_department"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.table_product_category,
    postgresql_script.table_category
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_count_products_on_department(INTEGER)
    RETURNS INTEGER LANGUAGE plpgsql AS $$
      DECLARE
        inDepartmentId ALIAS FOR $1;
        outProductsOnDepartmentCount INTEGER;
      BEGIN
        SELECT     DISTINCT INTO outProductsOnDepartmentCount
                   count(*)
        FROM       product p
        INNER JOIN product_category pc
                     ON p.product_id = pc.product_id
        INNER JOIN category c
                     ON pc.category_id = c.category_id
        WHERE      c.department_id = inDepartmentId;
        RETURN outProductsOnDepartmentCount;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_count_products_on_department(INTEGER) CASCADE;"
}

# Get products on department function
resource "postgresql_script" "func_catalog_get_products_on_department" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_products_on_department"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.table_product_category,
    postgresql_script.table_category,
    postgresql_script.type_product_list
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_products_on_department(INTEGER, INTEGER, INTEGER, INTEGER)
    RETURNS SETOF product_list LANGUAGE plpgsql AS $$
      DECLARE
        inDepartmentId                  ALIAS FOR $1;
        inShortProductDescriptionLength ALIAS FOR $2;
        inProductsPerPage               ALIAS FOR $3;
        inStartPage                     ALIAS FOR $4;
        outProductListRow product_list;
      BEGIN
        FOR outProductListRow IN
          SELECT     DISTINCT p.product_id, p.name, p.description,
                     p.price, p.discounted_price, p.thumbnail
          FROM       product p
          INNER JOIN product_category pc
                       ON p.product_id = pc.product_id
          INNER JOIN category c
                       ON pc.category_id = c.category_id
          WHERE      c.department_id = inDepartmentId
          ORDER BY   p.product_id
          LIMIT      inProductsPerPage
          OFFSET     inStartPage
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
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_products_on_department(INTEGER, INTEGER, INTEGER, INTEGER) CASCADE;"
}

# Count products on catalog function
resource "postgresql_script" "func_catalog_count_products_on_catalog" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_count_products_on_catalog"
  depends_on = [postgresql_script.table_product]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_count_products_on_catalog()
    RETURNS INTEGER LANGUAGE plpgsql AS $$
      DECLARE
        outCatalogCount INTEGER;
      BEGIN
        SELECT INTO outCatalogCount
                    count(*)
        FROM   product;
        RETURN outCatalogCount;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_count_products_on_catalog() CASCADE;"
}

# Get products on catalog function
resource "postgresql_script" "func_catalog_get_products_on_catalog" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_products_on_catalog"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.type_product_list
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_products_on_catalog(INTEGER, INTEGER, INTEGER)
    RETURNS SETOF product_list LANGUAGE plpgsql AS $$
      DECLARE
        inShortProductDescriptionLength ALIAS FOR $1;
        inProductsPerPage               ALIAS FOR $2;
        inStartPage                     ALIAS FOR $3;
        outProductListRow product_list;
      BEGIN
        FOR outProductListRow IN
          SELECT   product_id, name, description, price,
                   discounted_price, thumbnail
          FROM     product
          ORDER BY product_id
          LIMIT    inProductsPerPage
          OFFSET   inStartPage
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
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_products_on_catalog(INTEGER, INTEGER, INTEGER) CASCADE;"
}

# Get product details function
resource "postgresql_script" "func_catalog_get_product_details" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_product_details"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.type_product_details
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_product_details(INTEGER)
    RETURNS product_details LANGUAGE plpgsql AS $$
      DECLARE
        inProductId ALIAS FOR $1;
        outProductDetailsRow product_details;
      BEGIN
        SELECT INTO outProductDetailsRow
                    product_id, name, description, price,
                    discounted_price, image
        FROM   product
        WHERE  product_id = inProductId;
        RETURN outProductDetailsRow;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_product_details(INTEGER) CASCADE;"
}

# Catalog search function
resource "postgresql_script" "func_catalog_search" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_search"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.type_product_list,
    postgresql_script.index_search_vector
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_search(TEXT[], VARCHAR(3), INTEGER, INTEGER, INTEGER)
    RETURNS SETOF product_list LANGUAGE plpgsql AS $$
      DECLARE
        inWords                         ALIAS FOR $1;
        inAllWords                      ALIAS FOR $2;
        inShortProductDescriptionLength ALIAS FOR $3;
        inProductsPerPage               ALIAS FOR $4;
        inStartPage                     ALIAS FOR $5;
        outProductListRow product_list;
        query             TEXT;
        search_operator   VARCHAR(1);
        query_string      TSQUERY;
      BEGIN
        query := '';
        IF inAllWords = 'on' THEN
          search_operator := '&';
        ELSE
          search_operator := '|';
        END IF;

        FOR i IN array_lower(inWords, 1)..array_upper(inWords, 1) LOOP
          IF i = array_upper(inWords, 1) THEN
            query := query||inWords[i];
          ELSE
            query := query||inWords[i]||search_operator;
          END IF;
        END LOOP;
        query_string := to_tsquery(query);

        FOR outProductListRow IN
          SELECT   product_id, name, description, price,
                   discounted_price, thumbnail
          FROM     product
          WHERE    search_vector @@ query_string
          ORDER BY ts_rank(search_vector, query_string) DESC
          LIMIT    inProductsPerPage
          OFFSET   inStartPage
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
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_search(TEXT[], VARCHAR(3), INTEGER, INTEGER, INTEGER) CASCADE;"
}

# Get departments function (for admin)
resource "postgresql_script" "func_catalog_get_departments" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_departments"
  depends_on = [
    postgresql_script.table_department,
    postgresql_script.type_department_category
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_departments()
    RETURNS SETOF department LANGUAGE plpgsql AS $$
      DECLARE
        outDepartmentRow department;
      BEGIN
        FOR outDepartmentRow IN
          SELECT department_id, name, description
          FROM   department
          ORDER BY department_id
        LOOP
          RETURN NEXT outDepartmentRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_departments() CASCADE;"
}

# Add department function
resource "postgresql_script" "func_catalog_add_department" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_add_department"
  depends_on = [postgresql_script.table_department]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_add_department(VARCHAR(50), VARCHAR(1000))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inName        ALIAS FOR $1;
        inDescription ALIAS FOR $2;
      BEGIN
        INSERT INTO department (name, description)
               VALUES (inName, inDescription);
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_add_department(VARCHAR(50), VARCHAR(1000)) CASCADE;"
}

# Update department function
resource "postgresql_script" "func_catalog_update_department" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_update_department"
  depends_on = [postgresql_script.table_department]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_update_department(INTEGER, VARCHAR(50), VARCHAR(1000))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inDepartmentId ALIAS FOR $1;
        inName         ALIAS FOR $2;
        inDescription  ALIAS FOR $3;
      BEGIN
        UPDATE department
        SET    name = inName, description = inDescription
        WHERE  department_id = inDepartmentId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_update_department(INTEGER, VARCHAR(50), VARCHAR(1000)) CASCADE;"
}

# Delete department function
resource "postgresql_script" "func_catalog_delete_department" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_delete_department"
  depends_on = [postgresql_script.table_department]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_delete_department(INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inDepartmentId ALIAS FOR $1;
      BEGIN
        DELETE FROM department WHERE department_id = inDepartmentId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_delete_department(INTEGER) CASCADE;"
}

# Get department categories function
resource "postgresql_script" "func_catalog_get_department_categories" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_department_categories"
  depends_on = [
    postgresql_script.table_category,
    postgresql_script.type_department_category
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_department_categories(INTEGER)
    RETURNS SETOF department_category LANGUAGE plpgsql AS $$
      DECLARE
        inDepartmentId ALIAS FOR $1;
        outDepartmentCategoryRow department_category;
      BEGIN
        FOR outDepartmentCategoryRow IN
          SELECT category_id, name, description
          FROM   category
          WHERE  department_id = inDepartmentId
          ORDER BY category_id
        LOOP
          RETURN NEXT outDepartmentCategoryRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_department_categories(INTEGER) CASCADE;"
}

# Add category function
resource "postgresql_script" "func_catalog_add_category" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_add_category"
  depends_on = [postgresql_script.table_category]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_add_category(INTEGER, VARCHAR(50), VARCHAR(1000))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inDepartmentId ALIAS FOR $1;
        inName         ALIAS FOR $2;
        inDescription  ALIAS FOR $3;
      BEGIN
        INSERT INTO category (department_id, name, description)
               VALUES (inDepartmentId, inName, inDescription);
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_add_category(INTEGER, VARCHAR(50), VARCHAR(1000)) CASCADE;"
}

# Update category function
resource "postgresql_script" "func_catalog_update_category" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_update_category"
  depends_on = [postgresql_script.table_category]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_update_category(INTEGER, VARCHAR(50), VARCHAR(1000))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCategoryId  ALIAS FOR $1;
        inName        ALIAS FOR $2;
        inDescription ALIAS FOR $3;
      BEGIN
        UPDATE category
        SET    name = inName, description = inDescription
        WHERE  category_id = inCategoryId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_update_category(INTEGER, VARCHAR(50), VARCHAR(1000)) CASCADE;"
}

# Delete category function
resource "postgresql_script" "func_catalog_delete_category" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_delete_category"
  depends_on = [postgresql_script.table_category]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_delete_category(INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCategoryId ALIAS FOR $1;
      BEGIN
        DELETE FROM category WHERE category_id = inCategoryId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_delete_category(INTEGER) CASCADE;"
}

# Get category products function
resource "postgresql_script" "func_catalog_get_category_products" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_category_products"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.table_product_category,
    postgresql_script.type_category_product
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_category_products(INTEGER)
    RETURNS SETOF category_product LANGUAGE plpgsql AS $$
      DECLARE
        inCategoryId ALIAS FOR $1;
        outCategoryProductRow category_product;
      BEGIN
        FOR outCategoryProductRow IN
          SELECT     p.product_id, p.name, p.description,
                     p.price, p.discounted_price
          FROM       product p
          INNER JOIN product_category pc
                       ON p.product_id = pc.product_id
          WHERE      pc.category_id = inCategoryId
          ORDER BY   p.product_id
        LOOP
          RETURN NEXT outCategoryProductRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_category_products(INTEGER) CASCADE;"
}

# Add product to category function
resource "postgresql_script" "func_catalog_add_product_to_category" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_add_product_to_category"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.table_product_category
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_add_product_to_category(INTEGER, VARCHAR(50), VARCHAR(1000), NUMERIC(10, 2))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inCategoryId  ALIAS FOR $1;
        inName        ALIAS FOR $2;
        inDescription ALIAS FOR $3;
        inPrice       ALIAS FOR $4;
        productLastInsertId INTEGER;
      BEGIN
        INSERT INTO product (name, description, price, search_vector)
               VALUES (inName, inDescription, inPrice,
                       to_tsvector(inName || ' ' || inDescription));
        SELECT INTO productLastInsertId currval('product_product_id_seq');
        INSERT INTO product_category (product_id, category_id)
               VALUES (productLastInsertId, inCategoryId);
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_add_product_to_category(INTEGER, VARCHAR(50), VARCHAR(1000), NUMERIC(10, 2)) CASCADE;"
}

# Update product function
resource "postgresql_script" "func_catalog_update_product" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_update_product"
  depends_on = [postgresql_script.table_product]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_update_product(INTEGER, VARCHAR(50), VARCHAR(1000), NUMERIC(10, 2), NUMERIC(10, 2))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inProductId       ALIAS FOR $1;
        inName            ALIAS FOR $2;
        inDescription     ALIAS FOR $3;
        inPrice           ALIAS FOR $4;
        inDiscountedPrice ALIAS FOR $5;
      BEGIN
        UPDATE product
        SET    name = inName, description = inDescription,
               price = inPrice, discounted_price = inDiscountedPrice,
               search_vector = to_tsvector(inName || ' ' || inDescription)
        WHERE  product_id = inProductId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_update_product(INTEGER, VARCHAR(50), VARCHAR(1000), NUMERIC(10, 2), NUMERIC(10, 2)) CASCADE;"
}

# Delete product function
resource "postgresql_script" "func_catalog_delete_product" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_delete_product"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.table_product_category
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_delete_product(INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inProductId ALIAS FOR $1;
      BEGIN
        DELETE FROM product_category WHERE product_id = inProductId;
        DELETE FROM shopping_cart WHERE product_id = inProductId;
        DELETE FROM product WHERE product_id = inProductId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_delete_product(INTEGER) CASCADE;"
}

# Get product info function
resource "postgresql_script" "func_catalog_get_product_info" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_product_info"
  depends_on = [
    postgresql_script.table_product,
    postgresql_script.type_product_info
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_product_info(INTEGER)
    RETURNS product_info LANGUAGE plpgsql AS $$
      DECLARE
        inProductId ALIAS FOR $1;
        outProductInfoRow product_info;
      BEGIN
        SELECT INTO outProductInfoRow
                    name, image, thumbnail, display
        FROM   product
        WHERE  product_id = inProductId;
        RETURN outProductInfoRow;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_product_info(INTEGER) CASCADE;"
}

# Get categories for product function
resource "postgresql_script" "func_catalog_get_categories_for_product" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_categories_for_product"
  depends_on = [
    postgresql_script.table_category,
    postgresql_script.table_product_category,
    postgresql_script.type_product_category_details
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_categories_for_product(INTEGER)
    RETURNS SETOF product_category_details LANGUAGE plpgsql AS $$
      DECLARE
        inProductId ALIAS FOR $1;
        outProductCategoryDetailsRow product_category_details;
      BEGIN
        FOR outProductCategoryDetailsRow IN
          SELECT     c.category_id, c.department_id, c.name
          FROM       category c
          INNER JOIN product_category pc
                       ON c.category_id = pc.category_id
          WHERE      pc.product_id = inProductId
          ORDER BY   c.category_id
        LOOP
          RETURN NEXT outProductCategoryDetailsRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_categories_for_product(INTEGER) CASCADE;"
}

# Set product display option function
resource "postgresql_script" "func_catalog_set_product_display_option" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_set_product_display_option"
  depends_on = [postgresql_script.table_product]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_set_product_display_option(INTEGER, SMALLINT)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inProductId ALIAS FOR $1;
        inDisplay   ALIAS FOR $2;
      BEGIN
        UPDATE product
        SET    display = inDisplay
        WHERE  product_id = inProductId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_set_product_display_option(INTEGER, SMALLINT) CASCADE;"
}

# Assign product to category function
resource "postgresql_script" "func_catalog_assign_product_to_category" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_assign_product_to_category"
  depends_on = [postgresql_script.table_product_category]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_assign_product_to_category(INTEGER, INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inProductId  ALIAS FOR $1;
        inCategoryId ALIAS FOR $2;
      BEGIN
        INSERT INTO product_category (product_id, category_id)
               VALUES (inProductId, inCategoryId);
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_assign_product_to_category(INTEGER, INTEGER) CASCADE;"
}

# Remove product from category function
resource "postgresql_script" "func_catalog_remove_product_from_category" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_remove_product_from_category"
  depends_on = [postgresql_script.table_product_category]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_remove_product_from_category(INTEGER, INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inProductId  ALIAS FOR $1;
        inCategoryId ALIAS FOR $2;
      BEGIN
        DELETE FROM product_category
        WHERE  product_id = inProductId AND category_id = inCategoryId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_remove_product_from_category(INTEGER, INTEGER) CASCADE;"
}

# Move product to category function
resource "postgresql_script" "func_catalog_move_product_to_category" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_move_product_to_category"
  depends_on = [postgresql_script.table_product_category]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_move_product_to_category(INTEGER, INTEGER, INTEGER)
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inProductId      ALIAS FOR $1;
        inOldCategoryId  ALIAS FOR $2;
        inNewCategoryId  ALIAS FOR $3;
      BEGIN
        UPDATE product_category
        SET    category_id = inNewCategoryId
        WHERE  product_id = inProductId AND category_id = inOldCategoryId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_move_product_to_category(INTEGER, INTEGER, INTEGER) CASCADE;"
}

# Set image function
resource "postgresql_script" "func_catalog_set_image" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_set_image"
  depends_on = [postgresql_script.table_product]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_set_image(INTEGER, VARCHAR(150))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inProductId ALIAS FOR $1;
        inImage     ALIAS FOR $2;
      BEGIN
        UPDATE product
        SET    image = inImage
        WHERE  product_id = inProductId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_set_image(INTEGER, VARCHAR(150)) CASCADE;"
}

# Set thumbnail function
resource "postgresql_script" "func_catalog_set_thumbnail" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_set_thumbnail"
  depends_on = [postgresql_script.table_product]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_set_thumbnail(INTEGER, VARCHAR(150))
    RETURNS VOID LANGUAGE plpgsql AS $$
      DECLARE
        inProductId  ALIAS FOR $1;
        inThumbnail  ALIAS FOR $2;
      BEGIN
        UPDATE product
        SET    thumbnail = inThumbnail
        WHERE  product_id = inProductId;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_set_thumbnail(INTEGER, VARCHAR(150)) CASCADE;"
}

# Get categories function (all categories)
resource "postgresql_script" "func_catalog_get_categories" {
  count = var.create_functions ? 1 : 0
  name  = "func_catalog_get_categories"
  depends_on = [
    postgresql_script.table_category,
    postgresql_script.type_product_category_details
  ]

  create_script = <<-SQL
    CREATE OR REPLACE FUNCTION catalog_get_categories()
    RETURNS SETOF product_category_details LANGUAGE plpgsql AS $$
      DECLARE
        outProductCategoryDetailsRow product_category_details;
      BEGIN
        FOR outProductCategoryDetailsRow IN
          SELECT category_id, department_id, name
          FROM   category
          ORDER BY category_id
        LOOP
          RETURN NEXT outProductCategoryDetailsRow;
        END LOOP;
      END;
    $$;
  SQL

  drop_script = "DROP FUNCTION IF EXISTS catalog_get_categories() CASCADE;"
}
