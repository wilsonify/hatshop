-- Create audit table
CREATE TABLE audit
(
  audit_id       SERIAL    NOT NULL,
  order_id       INTEGER   NOT NULL,
  created_on     TIMESTAMP NOT NULL,
  message        TEXT      NOT NULL,
  message_number INTEGER   NOT NULL,
  CONSTRAINT pk_audit_id PRIMARY KEY (audit_id),
  CONSTRAINT fk_order_id FOREIGN KEY (order_id)
             REFERENCES orders (order_id)
             ON UPDATE RESTRICT ON DELETE RESTRICT
);

