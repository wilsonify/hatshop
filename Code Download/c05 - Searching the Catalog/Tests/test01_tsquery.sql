-- Let’s see how this would be applied in practice. The following query performs an all-
-- words search on the “yankee war” search string:
SELECT product_id, name
FROM product
WHERE search_vector @@ to_tsquery('yankee & war')
ORDER BY product_id;
-- With the sample products database,
-- Hats That Match “yankee & war” :
-- product_id name
-- 40 Civil War Union Slouch Hat
-- 44 Union Civil War Kepi Cap

