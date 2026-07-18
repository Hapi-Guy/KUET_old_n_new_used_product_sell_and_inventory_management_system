<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Dashboard views. Created last, since they read from every base table.
 *   - view_seller_ratings : each seller's average rating and review count.
 *   - view_all_products   : AVAILABLE products with category and highest bid.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE OR REPLACE VIEW view_seller_ratings AS
                SELECT rated_user_id AS seller_id,
                       ROUND(AVG(rating_value), 2) AS avg_seller_rating,
                       COUNT(id) AS total_reviews
                FROM ratings
                WHERE rating_type = 'SELLER_RATING'
                GROUP BY rated_user_id"
        );

        DB::unprepared(
            "CREATE OR REPLACE VIEW view_all_products AS
                SELECT p.id AS product_id, p.title, c.category_name, p.product_condition,
                       p.status, p.min_proposed_price, COALESCE(MAX(b.bid_amount), 0) AS max_current_bid
                FROM products p
                JOIN categories c ON p.category_id = c.id
                LEFT JOIN bargains b ON p.id = b.product_id AND b.bid_status != 'REJECTED'
                WHERE p.status = 'AVAILABLE'
                GROUP BY p.id, p.title, c.category_name, p.product_condition, p.status, p.min_proposed_price"
        );
    }

    public function down(): void
    {
        foreach (['view_all_products', 'view_seller_ratings'] as $view) {
            $this->safeDrop("DROP VIEW {$view}");
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
