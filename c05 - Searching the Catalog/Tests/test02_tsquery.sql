
SELECT product_id, name
FROM product
WHERE search_vector @@ to_tsquery('yankee | war')
ORDER BY product_id;

--Hats That Match “yankee | war”
--product_id name
--26 Military Beret
--30 Confederate Civil War Kepi
--33 Uncle Sam Top Hat
--38 Confederate Slouch Hat
--40 Civil War Union Slouch Hat
--41 Civil War Leather Kepi Cap
--44 Union Civil War Kepi Cap

