<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Marketplace activity: bargains (bids), ratings, transactions, wishlists and
 * reports. All reference users / products created in the earlier migrations.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
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
            )"
        );

        DB::unprepared(
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
            )"
        );

        DB::unprepared(
            "CREATE TABLE transactions (
                id NUMBER(20) PRIMARY KEY,
                product_id NUMBER(20) UNIQUE NOT NULL,
                buyer_id NUMBER(20) NOT NULL,
                final_price NUMBER(10,2) NOT NULL,
                transaction_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_tran_prod FOREIGN KEY (product_id) REFERENCES products(id),
                CONSTRAINT fk_tran_buyer FOREIGN KEY (buyer_id) REFERENCES users(id)
            )"
        );

        DB::unprepared(
            "CREATE TABLE wishlists (
                id NUMBER(20) PRIMARY KEY,
                user_id NUMBER(20) NOT NULL,
                product_id NUMBER(20) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT fk_wish_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
                CONSTRAINT fk_wish_prod FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
                CONSTRAINT uq_user_product UNIQUE (user_id, product_id)
            )"
        );

        DB::unprepared(
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
            )"
        );

        foreach ([
            'bargains_id_seq', 'ratings_id_seq', 'transactions_id_seq',
            'wishlists_id_seq', 'reports_id_seq',
        ] as $seq) {
            DB::unprepared("CREATE SEQUENCE {$seq} START WITH 1 INCREMENT BY 1");
        }

        foreach ([
            'bargains'     => 'bargains_id_seq',
            'ratings'      => 'ratings_id_seq',
            'transactions' => 'transactions_id_seq',
            'wishlists'    => 'wishlists_id_seq',
            'reports'      => 'reports_id_seq',
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
        foreach (['reports', 'wishlists', 'transactions', 'ratings', 'bargains'] as $table) {
            $this->safeDrop("DROP TABLE {$table} CASCADE CONSTRAINTS");
        }
        foreach ([
            'reports_id_seq', 'wishlists_id_seq', 'transactions_id_seq',
            'ratings_id_seq', 'bargains_id_seq',
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
};
