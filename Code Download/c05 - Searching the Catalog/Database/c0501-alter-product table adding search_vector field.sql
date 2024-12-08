-- Alter product table adding search_vector field
ALTER TABLE product ADD COLUMN search_vector tsvector;

