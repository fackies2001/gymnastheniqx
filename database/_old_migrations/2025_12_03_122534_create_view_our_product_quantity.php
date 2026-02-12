<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('
                    CREATE OR REPLACE VIEW view_our_product_quantity AS
                    SELECT
                        sp.id AS supplier_product_id,
                        sp.name AS product_name,
                        sp.system_sku AS system_sku,
                        sp.images AS images,

                        c.id AS category_id,
                        c.name AS category_name,

                        s.id AS supplier_id,
                        s.name AS supplier_name,

                        sn.warehouse_id AS warehouse_id,

                        COUNT(sn.id) AS quantity

                    FROM serial_numbers AS sn
                    JOIN supplier_products AS sp ON sn.sku_id = sp.id
                    JOIN suppliers AS s ON sp.supplier_id = s.id
                    JOIN product_status AS ps ON sn.product_status_id = ps.id
                    JOIN purchase_orders AS po ON sn.purchase_order_id = po.id
                    JOIN employees AS e ON sn.scanned_by = e.id
                    JOIN categories AS c ON sp.category_id = c.id

                    GROUP BY
                        sp.id, sp.name, sp.system_sku, sp.images,
                        c.id, c.name,
                        s.id, s.name,
                        sn.warehouse_id
                ');

    }

    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS view_our_product_quantity');
    }
};
