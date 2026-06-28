<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach ($this->tables() as $sql) {
            DB::unprepared($sql);
        }
        foreach ($this->sequences() as $sql) {
            DB::unprepared($sql);
        }
        foreach ($this->triggers() as $sql) {
            DB::unprepared($sql);
        }
        foreach ($this->views() as $sql) {
            DB::unprepared($sql);
        }
    }

    public function down(): void
    {
        foreach (['view_all_products', 'view_seller_ratings'] as $view) {
            $this->safeDrop("DROP VIEW {$view}");
        }

        $tables = [
            'reports', 'wishlists', 'transactions', 'ratings',
            'bargains', 'product_images', 'products', 'categories', 'users',
        ];
        foreach ($tables as $table) {
            $this->safeDrop("DROP TABLE {$table} CASCADE CONSTRAINTS");
        }

        foreach ([
            'users_id_seq', 'categories_id_seq', 'products_id_seq', 'product_images_id_seq',
            'bargains_id_seq', 'ratings_id_seq', 'transactions_id_seq', 'wishlists_id_seq', 'reports_id_seq',
        ] as $seq) {
            $this->safeDrop("DROP SEQUENCE {$seq}");
        }
    }

    private function safeDrop(string $sql): void
    {
        try {
            DB::unprepared($sql);
        } catch (\Throwable $e) {
            if (! preg_match('/ORA-00942|ORA-02289|ORA-04080|ORA-04043/i', $e->getMessage())) {
                throw $e;
            }
        }
    }

    private function tables(): array
    {
        return [
            "CREATE TABLE users (
                id NUMBER(20) PRIMARY KEY,
                name VARCHAR2(100) NOT NULL,
                email VARCHAR2(150) UNIQUE NOT NULL,
                password_hash VARCHAR2(255) NOT NULL,
                mobile_no VARCHAR2(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT chk_kuet_email CHECK (email LIKE '%@stud.kuet.ac.bd')
            )",

            "CREATE TABLE categories (
                id NUMBER(10) PRIMARY KEY,
                category_name VARCHAR2(100) UNIQUE NOT NULL
            )",

            "CREATE TABLE products (
                id NUMBER(20) PRIMARY KEY,
                seller_id NUMBER(20) NOT NULL,
                category_id NUMBER(10) NOT NULL,
                title VARCHAR2(150) NOT NULL,
                description VARCHAR2(1000),
                product_condition VARCHAR2(20) CHECK (product_condition IN ('NEW', 'OLD')),
                min_proposed_price NUMBER(10,2) NOT NULL,
                status VARCHAR2(20) DEFAULT 'AVAILABLE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_prod_seller FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_prod_category FOREIGN KEY (category_id) REFERENCES categories(id),
                CONSTRAINT chk_prod_status CHECK (status IN ('AVAILABLE', 'SOLD', 'UNAVAILABLE'))
            )",

            "CREATE TABLE product_images (
                id NUMBER(20) PRIMARY KEY,
                product_id NUMBER(20) NOT NULL,
                image_path VARCHAR2(255) NOT NULL,
                CONSTRAINT fk_image_prod FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )",

            "CREATE TABLE bargains (
                id NUMBER(20) PRIMARY KEY,
                product_id NUMBER(20) NOT NULL,
                buyer_id NUMBER(20) NOT NULL,
                bid_amount NUMBER(10,2) NOT NULL,
                bid_status VARCHAR2(20) DEFAULT 'PENDING',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_barg_prod FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                CONSTRAINT fk_barg_buyer FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT chk_bid_status CHECK (bid_status IN ('PENDING', 'ACCEPTED', 'REJECTED'))
            )",

            "CREATE TABLE ratings (
                id NUMBER(20) PRIMARY KEY,
                product_id NUMBER(20) NOT NULL,
                rater_id NUMBER(20) NOT NULL,
                rated_user_id NUMBER(20) NOT NULL,
                rating_type VARCHAR2(20) NOT NULL,
                rating_value NUMBER(2,1) NOT NULL,
                review_text VARCHAR2(500),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_rating_prod FOREIGN KEY (product_id) REFERENCES products(id),
                CONSTRAINT fk_rating_rater FOREIGN KEY (rater_id) REFERENCES users(id),
                CONSTRAINT fk_rating_rated FOREIGN KEY (rated_user_id) REFERENCES users(id),
                CONSTRAINT chk_rating_type CHECK (rating_type IN ('BUYER_RATING', 'SELLER_RATING')),
                CONSTRAINT chk_rating_value CHECK (rating_value BETWEEN 1 AND 5),
                CONSTRAINT chk_not_self_rating CHECK (rater_id != rated_user_id)
            )",

            "CREATE TABLE transactions (
                id NUMBER(20) PRIMARY KEY,
                product_id NUMBER(20) UNIQUE NOT NULL,
                buyer_id NUMBER(20) NOT NULL,
                final_price NUMBER(10,2) NOT NULL,
                transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_tran_prod FOREIGN KEY (product_id) REFERENCES products(id),
                CONSTRAINT fk_tran_buyer FOREIGN KEY (buyer_id) REFERENCES users(id)
            )",

            "CREATE TABLE wishlists (
                id NUMBER(20) PRIMARY KEY,
                user_id NUMBER(20) NOT NULL,
                product_id NUMBER(20) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_wish_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_wish_prod FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                CONSTRAINT uq_user_product UNIQUE (user_id, product_id)
            )",

            "CREATE TABLE reports (
                id NUMBER(20) PRIMARY KEY,
                reporter_id NUMBER(20) NOT NULL,
                reported_id NUMBER(20) NOT NULL,
                product_id NUMBER(20) NOT NULL,
                report_type VARCHAR2(20) NOT NULL,
                reason VARCHAR2(500) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_rep_reporter FOREIGN KEY (reporter_id) REFERENCES users(id),
                CONSTRAINT fk_rep_reported FOREIGN KEY (reported_id) REFERENCES users(id),
                CONSTRAINT fk_rep_prod FOREIGN KEY (product_id) REFERENCES products(id),
                CONSTRAINT chk_rep_type CHECK (report_type IN ('BUYER', 'SELLER'))
            )",
        ];
    }

    private function sequences(): array
    {
        return [
            'CREATE SEQUENCE users_id_seq START WITH 1 INCREMENT BY 1',
            'CREATE SEQUENCE categories_id_seq START WITH 1 INCREMENT BY 1',
            'CREATE SEQUENCE products_id_seq START WITH 1 INCREMENT BY 1',
            'CREATE SEQUENCE product_images_id_seq START WITH 1 INCREMENT BY 1',
            'CREATE SEQUENCE bargains_id_seq START WITH 1 INCREMENT BY 1',
            'CREATE SEQUENCE ratings_id_seq START WITH 1 INCREMENT BY 1',
            'CREATE SEQUENCE transactions_id_seq START WITH 1 INCREMENT BY 1',
            'CREATE SEQUENCE wishlists_id_seq START WITH 1 INCREMENT BY 1',
            'CREATE SEQUENCE reports_id_seq START WITH 1 INCREMENT BY 1',
        ];
    }

    private function triggers(): array
    {
        $map = [
            'users'          => 'users_id_seq',
            'categories'     => 'categories_id_seq',
            'products'       => 'products_id_seq',
            'product_images' => 'product_images_id_seq',
            'bargains'       => 'bargains_id_seq',
            'ratings'        => 'ratings_id_seq',
            'transactions'   => 'transactions_id_seq',
            'wishlists'      => 'wishlists_id_seq',
            'reports'        => 'reports_id_seq',
        ];

        $triggers = [];
        foreach ($map as $table => $seq) {
            $triggers[] = "CREATE OR REPLACE TRIGGER {$table}_bir
                BEFORE INSERT ON {$table}
                FOR EACH ROW
                WHEN (NEW.id IS NULL)
                BEGIN
                    SELECT {$seq}.NEXTVAL INTO :NEW.id FROM dual;
                END;";
        }

        return $triggers;
    }

    private function views(): array
    {
        return [
            "CREATE OR REPLACE VIEW view_seller_ratings AS
                SELECT rated_user_id AS seller_id,
                       ROUND(AVG(rating_value), 2) AS avg_seller_rating,
                       COUNT(id) AS total_reviews
                FROM ratings
                WHERE rating_type = 'SELLER_RATING'
                GROUP BY rated_user_id",

            "CREATE OR REPLACE VIEW view_all_products AS
                SELECT p.id AS product_id, p.title, c.category_name, p.product_condition,
                       p.status, p.min_proposed_price, COALESCE(MAX(b.bid_amount), 0) AS max_current_bid
                FROM products p
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN bargains b ON p.id = b.product_id AND b.bid_status != 'REJECTED'
                WHERE p.status = 'AVAILABLE'
                GROUP BY p.id, p.title, c.category_name, p.product_condition, p.status, p.min_proposed_price",
        ];
    }
};