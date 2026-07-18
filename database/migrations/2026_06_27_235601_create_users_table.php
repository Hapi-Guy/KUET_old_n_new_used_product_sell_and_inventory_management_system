<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Users (KUET students).
 *
 * Oracle 11g has no IDENTITY columns, so the primary key is fed by a sequence
 * through a BEFORE INSERT trigger. Raw DDL is sent via DB::unprepared() so the
 * Oracle-specific syntax (CHECK ... LIKE, PL/SQL trigger body, :NEW) is passed
 * to the server untouched.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(
            "CREATE TABLE users (
                id NUMBER(20) PRIMARY KEY,
                name VARCHAR2(100) NOT NULL,
                email VARCHAR2(150) UNIQUE NOT NULL,
                password_hash VARCHAR2(255) NOT NULL,
                mobile_no VARCHAR2(20),
                is_admin NUMBER(1) DEFAULT 0 NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                CONSTRAINT chk_kuet_email CHECK (email LIKE '%@stud.kuet.ac.bd'),
                CONSTRAINT chk_is_admin CHECK (is_admin IN (0, 1))
            )"
        );

        DB::unprepared('CREATE SEQUENCE users_id_seq START WITH 1 INCREMENT BY 1');

        DB::unprepared(
            "CREATE OR REPLACE TRIGGER users_bir
                BEFORE INSERT ON users
                FOR EACH ROW
                WHEN (NEW.id IS NULL)
                BEGIN
                    SELECT users_id_seq.NEXTVAL INTO :NEW.id FROM dual;
                END;"
        );
    }

    public function down(): void
    {
        $this->safeDrop('DROP TABLE users CASCADE CONSTRAINTS');
        $this->safeDrop('DROP SEQUENCE users_id_seq');
    }

    /** Execute a DROP, ignoring "object does not exist" errors so down() is idempotent. */
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
