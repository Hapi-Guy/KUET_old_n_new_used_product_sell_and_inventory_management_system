<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Product catalogue: categories, products and their images.
 * Each table gets its own sequence + BEFORE INSERT trigger for the PK.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tables (order matters: products references categories & users).
        DB::unprepared(
            "CREATE TABLE categories (
                id NUMBER(10) PRIMARY KEY,
                category_name VARCHAR2(100) UNIQUE NOT NULL
            )"
        );

        DB::unprepared(
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
            )"
        );

        DB::unprepared(
            "CREATE TABLE product_images (
                id NUMBER(20) PRIMARY KEY,
                product_id NUMBER(20) NOT NULL,
                image_path VARCHAR2(255) NOT NULL,
                CONSTRAINT fk_image_prod FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
            )"
        );

        // Sequences.
        DB::unprepared('CREATE SEQUENCE categories_id_seq START WITH 1 INCREMENT BY 1');
        DB::unprepared('CREATE SEQUENCE products_id_seq START WITH 1 INCREMENT BY 1');
        DB::unprepared('CREATE SEQUENCE product_images_id_seq START WITH 1 INCREMENT BY 1');

        // PK triggers.
        foreach ([
            'categories'     => 'categories_id_seq',
            'products'       => 'products_id_seq',
            'product_images' => 'product_images_id_seq',
        ] as $table => $seq) {
            DB::unprepared(
                "CREATE OR REPLACE TRIGGER {$table}_bir
                    BEFORE INSERT ON {$table}
                    FOR EACH ROW
                    WHEN (NEW.id IS NULL)
                    BEGIN
                        SELECT {$seq}.NEXTVAL INTO :NEW.id FROM dual;
                    END;"
            );
        }
    }

    public function down(): void
    {
        // Children first.
        foreach (['product_images', 'products', 'categories'] as $table) {
            $this->safeDrop("DROP TABLE {$table} CASCADE CONSTRAINTS");
        }
        foreach (['product_images_id_seq', 'products_id_seq', 'categories_id_seq'] as $seq) {
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
};
