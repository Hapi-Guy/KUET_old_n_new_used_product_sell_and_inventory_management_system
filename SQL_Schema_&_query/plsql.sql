CREATE OR REPLACE TYPE image_path_varray AS VARRAY(10) OF VARCHAR2(255);
/

CREATE OR REPLACE TYPE product_summary_obj AS OBJECT (
   product_id NUMBER,
   title      VARCHAR2(150),
   min_price  NUMBER(10,2),
   MEMBER FUNCTION display_label RETURN VARCHAR2
);
/

CREATE OR REPLACE TYPE BODY product_summary_obj AS
   MEMBER FUNCTION display_label RETURN VARCHAR2 IS
   BEGIN
      RETURN title || ' (min TK ' || TO_CHAR(min_price) || ')';
   END;
END;
/

CREATE OR REPLACE FUNCTION fn_seller_avg_rating(p_seller_id IN NUMBER)
   RETURN NUMBER
IS
   v_avg NUMBER;
BEGIN
   SELECT ROUND(AVG(rating_value), 2)
     INTO v_avg
     FROM ratings
    WHERE rated_user_id = p_seller_id
      AND rating_type = 'SELLER_RATING';
   RETURN NVL(v_avg, 0);
END;
/

CREATE OR REPLACE FUNCTION fn_product_images(p_product_id IN NUMBER)
   RETURN image_path_varray
IS
   v_paths image_path_varray;
BEGIN
   SELECT image_path
     BULK COLLECT INTO v_paths
     FROM (SELECT image_path
             FROM product_images
            WHERE product_id = p_product_id
            ORDER BY id)
    WHERE ROWNUM <= 10;
   RETURN v_paths;
END;
/

CREATE OR REPLACE FUNCTION fn_product_label(p_product_id IN NUMBER)
   RETURN VARCHAR2
IS
   v_obj   product_summary_obj;
   v_title products.title%TYPE;
   v_price products.min_proposed_price%TYPE;
BEGIN
   SELECT title, min_proposed_price
     INTO v_title, v_price
     FROM products
    WHERE id = p_product_id;

   v_obj := product_summary_obj(p_product_id, v_title, v_price);
   RETURN v_obj.display_label();
EXCEPTION
   WHEN NO_DATA_FOUND THEN
      RETURN NULL;
END;
/

CREATE OR REPLACE PROCEDURE sp_finalize_sale(p_product_id IN NUMBER)
IS
   v_bargain_id bargains.id%TYPE;
   v_buyer_id   bargains.buyer_id%TYPE;
   v_amount     bargains.bid_amount%TYPE;
   v_cnt        NUMBER;
BEGIN
   SELECT id, buyer_id, bid_amount
     INTO v_bargain_id, v_buyer_id, v_amount
     FROM bargains
    WHERE product_id = p_product_id
      AND bid_status = 'ACCEPTED'
      AND ROWNUM = 1;

   SELECT COUNT(*) INTO v_cnt FROM transactions WHERE product_id = p_product_id;
   IF v_cnt > 0 THEN
      UPDATE transactions
         SET buyer_id = v_buyer_id, final_price = v_amount
       WHERE product_id = p_product_id;
   ELSE
      INSERT INTO transactions (product_id, buyer_id, final_price)
      VALUES (p_product_id, v_buyer_id, v_amount);
   END IF;

   UPDATE products SET status = 'SOLD' WHERE id = p_product_id;

   FOR r IN (SELECT DISTINCT buyer_id
               FROM bargains
              WHERE product_id = p_product_id
                AND bid_status = 'PENDING'
                AND id <> v_bargain_id) LOOP
      SELECT COUNT(*) INTO v_cnt
        FROM wishlists
       WHERE user_id = r.buyer_id AND product_id = p_product_id;
      IF v_cnt = 0 THEN
         INSERT INTO wishlists (user_id, product_id)
         VALUES (r.buyer_id, p_product_id);
      END IF;
   END LOOP;
EXCEPTION
   WHEN NO_DATA_FOUND THEN
      RAISE_APPLICATION_ERROR(-20010,
         'No chosen (ACCEPTED) bid to finalise for product ' || p_product_id);
END;
/
