-- Populate shipping table
INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(1, 'Next Day Delivery ($20)', 20.00, 2);

INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(2, '3-4 Days ($10)', 10.00, 2);

INSERT INTO shipping (shipping_id, shipping_type,  shipping_cost, shipping_region_id)
       VALUES(3, '7 Days ($5)', 5.00, 2);

INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(4, 'By air (7 days, $25)', 25.00, 3);

INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(5, 'By sea (28 days, $10)', 10.00, 3);

INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(6, 'By air (10 days, $35)', 35.00, 4);

INSERT INTO shipping (shipping_id, shipping_type, shipping_cost, shipping_region_id)
       VALUES(7, 'By sea (28 days, $30)', 30.00, 4);
