-- Create category table
CREATE TABLE category
(
  category_id   SERIAL      NOT NULL,
  department_id INTEGER     NOT NULL,
  name          VARCHAR(50) NOT NULL,
  description   VARCHAR(1000),
  CONSTRAINT pk_category_id   PRIMARY KEY (category_id),
  CONSTRAINT fk_department_id FOREIGN KEY (department_id)
             REFERENCES department (department_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT
);
