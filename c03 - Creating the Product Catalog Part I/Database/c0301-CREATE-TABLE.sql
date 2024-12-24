CREATE TABLE department
(
  department_id SERIAL        NOT NULL,
  name          VARCHAR(50)   NOT NULL,
  description   VARCHAR(1000),
  CONSTRAINT pk_department_id PRIMARY KEY (department_id)
);

