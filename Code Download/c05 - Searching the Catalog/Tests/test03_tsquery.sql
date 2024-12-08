

SELECT
  rank(search_vector, to_tsquery('yankee | war')) as rank,
  product_id,
  name
FROM product
WHERE search_vector @@ to_tsquery('yankee | war')
ORDER BY rank DESC;

--Search Results Ordered by Rank
--rank product_id name
--0.34195940 Civil War Union Slouch Hat
--0.3343644 Union Civil War Kepi Cap
--0.3168441 Civil War Leather Kepi Cap
--0.30396430 Confederate Civil War Kepi
--0.037995426 Military Beret
--0.030396438 Confederate Slouch Hat
--0.030396433 Uncle Sam Top Hat