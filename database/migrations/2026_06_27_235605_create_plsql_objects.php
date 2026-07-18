<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * PL/SQL objects: a VARRAY type, an OBJECT type (with a member function),
 * three stored FUNCTIONS, and a stored PROCEDURE. Created last, since they
 * depend on the tables. Each block is one DB::unprepared() call (no trailing
 * slash — that is a SQL*Plus terminator, not part of the statement).
 *
 * The same objects live, slash-terminated, in SQL_Schema_&_query/plsql.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. VARRAY type.
        DB::unprepared('CREATE OR REPLACE TYPE image_path_varray AS VARRAY(10) OF VARCHAR2(255)');

        // 2. OBJECT type + body.
        DB::unprepared(
            "CREATE OR REPLACE TYPE product_summary_obj AS OBJECT (
                product_id NUMBER,
                title      VARCHAR2(150),
                min_price  NUMBER(10,2),
                MEMBER FUNCTION display_label RETURN VARCHAR2
            )"
        );
        DB::unprepared(
            "CREATE OR REPLACE TYPE BODY product_summary_obj AS
                MEMBER FUNCTION display_label RETURN VARCHAR2 IS
                BEGIN
                    RETURN title || ' (min TK ' || TO_CHAR(min_price) || ')';
                END;
            END;"
        );

        // 3. FUNCTION: a seller's average rating.
        DB::unprepared(
            "CREATE OR REPLACE FUNCTION fn_seller_avg_rating(p_seller_id IN NUMBER)
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
            END;"
        );

        // 4. FUNCTION returning a VARRAY: a product's image paths.
        DB::unprepared(
            "CREATE OR REPLACE FUNCTION fn_product_images(p_product_id IN NUMBER)
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
            END;"
        );

        // 5. FUNCTION exercising the OBJECT type + its member function.
        DB::unprepared(
            "CREATE OR REPLACE FUNCTION fn_product_label(p_product_id IN NUMBER)
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
            END;"
        );

        // 6. PROCEDURE: finalise a sale for a product's chosen (ACCEPTED) bid.
        DB::unprepared(
            "CREATE OR REPLACE PROCEDURE sp_finalize_sale(p_product_id IN NUMBER)
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
            END;"
        );
    }

    public function down(): void
    {
        // Drop routines before the types they depend on.
        foreach ([
            'DROP PROCEDURE sp_finalize_sale',
            'DROP FUNCTION fn_product_label',
            'DROP FUNCTION fn_product_images',
            'DROP FUNCTION fn_seller_avg_rating',
            'DROP TYPE product_summary_obj',
            'DROP TYPE image_path_varray',
        ] as $sql) {
            $this->safeDrop($sql);
        }
    }

    private function safeDrop(string $sql): void
    {
        try {
            DB::unprepared($sql);
        } catch (\Throwable $e) {
            // ORA-04043 (object does not exist), ORA-00942, ORA-04080.
            if (! preg_match('/ORA-04043|ORA-00942|ORA-04080/i', $e->getMessage())) {
                throw $e;
            }
        }
    }
};
