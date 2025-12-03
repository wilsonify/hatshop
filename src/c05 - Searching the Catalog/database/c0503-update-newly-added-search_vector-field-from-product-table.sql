
-- Update newly added search_vector field from product table
UPDATE product
SET    search_vector = setweight(to_tsvector(name), 'A') || to_tsvector(description)
WHERE  search_vector IS NULL;

