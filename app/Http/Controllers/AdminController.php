<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Report;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Minimal admin dashboard: read-only site overview.
     * The headline aggregates are computed with raw Oracle SQL (DB::scalar
     * returns the first column of the first row) to show hand-written queries
     * running against the tables; the detail lists below still use Eloquent
     * because the views rely on model relationships.
     */
    public function dashboard(): View
    {
        $stats = [
            'users'        => (int)   DB::scalar('SELECT COUNT(*) FROM users'),
            'admins'       => (int)   DB::scalar('SELECT COUNT(*) FROM users WHERE is_admin = 1'),
            'products'     => (int)   DB::scalar('SELECT COUNT(*) FROM products'),
            'available'    => (int)   DB::scalar("SELECT COUNT(*) FROM products WHERE status = 'AVAILABLE'"),
            'sold'         => (int)   DB::scalar("SELECT COUNT(*) FROM products WHERE status = 'SOLD'"),
            'transactions' => (int)   DB::scalar('SELECT COUNT(*) FROM transactions'),
            'revenue'      => (float) DB::scalar('SELECT NVL(SUM(final_price), 0) FROM transactions'),
            'reports'      => (int)   DB::scalar('SELECT COUNT(*) FROM reports'),
        ];

        $recentProducts = Product::with(['seller', 'category'])
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $reports = Report::with(['reporter', 'reported', 'product'])
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $recentUsers = User::orderByDesc('id')
            ->limit(8)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentProducts', 'reports', 'recentUsers'));
    }

    /**
     * All products across every seller, with photo counts. From here an admin
     * opens any product to add or remove its photos.
     */
    public function products(): View
    {
        $products = Product::with(['seller', 'category'])
            ->withCount('images')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.products', compact('products'));
    }

    /**
     * Raw-SQL showcase: runs a set of hand-written Oracle SELECT statements
     * (the same ones in SQL_Schema_&_query/query.sql) via DB::select and shows
     * each query's text next to its live result. Read-only, admin-only.
     */
    public function sqlDemo(): View
    {
        $queries = [
            [
                'title' => 'All tables in the schema (USER_TABLES)',
                'sql'   => 'SELECT table_name FROM user_tables ORDER BY table_name',
            ],
            [
                'title' => 'Product catalogue: product + seller + category',
                'sql'   => "SELECT p.id, p.title, u.name AS seller, c.category_name AS category,
                                   p.product_condition AS cond, p.min_proposed_price AS min_price, p.status
                            FROM products p
                            JOIN users u ON p.seller_id = u.id
                            JOIN categories c ON p.category_id = c.id
                            ORDER BY p.id",
            ],
            [
                'title' => 'View: AVAILABLE products with highest live bid (view_all_products)',
                'sql'   => 'SELECT * FROM view_all_products ORDER BY max_current_bid DESC',
            ],
            [
                'title' => 'Seller reputation (view_seller_ratings + users)',
                'sql'   => 'SELECT u.name AS seller, v.avg_seller_rating, v.total_reviews
                            FROM view_seller_ratings v
                            JOIN users u ON v.seller_id = u.id
                            ORDER BY v.avg_seller_rating DESC',
            ],
            [
                'title' => 'Completed transactions: product, seller, buyer, final price',
                'sql'   => "SELECT t.id, p.title AS product, s.name AS seller, b.name AS buyer,
                                   t.final_price, t.transaction_date
                            FROM transactions t
                            JOIN products p ON t.product_id = p.id
                            JOIN users s ON p.seller_id = s.id
                            JOIN users b ON t.buyer_id = b.id
                            ORDER BY t.id",
            ],
            [
                'title' => 'Number of products per category',
                'sql'   => 'SELECT c.category_name, COUNT(p.id) AS product_count
                            FROM categories c
                            LEFT JOIN products p ON p.category_id = c.id
                            GROUP BY c.category_name
                            ORDER BY product_count DESC, c.category_name',
            ],
            [
                'title' => 'PL/SQL FUNCTION: fn_seller_avg_rating(user id)',
                'sql'   => 'SELECT u.id, u.name AS seller, fn_seller_avg_rating(u.id) AS avg_rating
                            FROM users u
                            ORDER BY avg_rating DESC, u.id',
            ],
            [
                'title' => 'PL/SQL OBJECT type via fn_product_label(product id)',
                'sql'   => 'SELECT p.id, fn_product_label(p.id) AS summary_label FROM products p ORDER BY p.id',
            ],
            [
                'title' => 'PL/SQL VARRAY via TABLE(fn_product_images(product id))',
                'sql'   => 'SELECT p.id, p.title,
                                   (SELECT COUNT(*) FROM TABLE(fn_product_images(p.id))) AS image_count
                            FROM products p
                            ORDER BY p.id',
            ],
        ];

        $results = [];
        foreach ($queries as $q) {
            try {
                $rows = DB::select($q['sql']);
                $results[] = ['title' => $q['title'], 'sql' => $q['sql'], 'rows' => $rows, 'error' => null];
            } catch (\Throwable $e) {
                $results[] = ['title' => $q['title'], 'sql' => $q['sql'], 'rows' => [], 'error' => $e->getMessage()];
            }
        }

        return view('admin.sql-demo', compact('results'));
    }
}
