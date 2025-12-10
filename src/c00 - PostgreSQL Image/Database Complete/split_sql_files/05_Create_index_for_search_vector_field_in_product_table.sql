-- Create index for search_vector field in product table
CREATE INDEX idx_search_vector ON product USING gist(search_vector);

