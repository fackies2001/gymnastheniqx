<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. GUMAWA NG PAYMENT_TERM TABLE
        // Ito ang missing piece kanina kaya nag-e-error ang seeder mo.
        if (!Schema::hasTable('payment_term')) {
            Schema::create('payment_term', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->integer('days')->nullable();
                $table->decimal('discount_rate', 5, 2)->nullable();
                $table->integer('discount_days')->nullable();
                $table->timestamps();
            });
        }

        // 2. SEEDING (Opsyonal kung r-run mo pa ang separate seeder, pero safe itong iwan dito)
        $this->seedPaymentTerm();

        // 3. FIX SUPPLIER TABLE
        if (Schema::hasTable('supplier')) {
            Schema::table('supplier', function (Blueprint $table) {
                if (!Schema::hasColumn('supplier', 'contact_person')) {
                    $table->string('contact_person')->nullable()->after('name');
                }
                if (!Schema::hasColumn('supplier', 'contact_number')) {
                    $table->string('contact_number')->nullable()->after('contact_person');
                }
                if (!Schema::hasColumn('supplier', 'email')) {
                    $table->string('email')->nullable()->after('contact_number');
                }
            });
        }

        // 4. FIX PURCHASE_REQUEST TABLE
        if (Schema::hasTable('purchase_request')) {
            Schema::table('purchase_request', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_request', 'remarks')) {
                    $table->text('remarks')->nullable();
                }
            });
        }

        // 5. FIX PURCHASE_ORDER TABLE
        if (Schema::hasTable('purchase_order')) {
            Schema::table('purchase_order', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_order', 'payment_term_id')) {
                    // Ginawa nating foreignId at tinuro sa singular na 'payment_term'
                    $table->foreignId('payment_term_id')->nullable()->constrained('payment_term')->after('delivery_date')->onDelete('set null');
                }

                if (!Schema::hasColumn('purchase_order', 'remarks')) {
                    $table->text('remarks')->nullable()->after('payment_term_id');
                }
            });
        }
    }

    public function down(): void
    {
        // Babalik tayo sa dati kapag nag-rollback
        if (Schema::hasTable('supplier')) {
            Schema::table('supplier', function (Blueprint $table) {
                $table->dropColumn(['contact_person', 'contact_number', 'email']);
            });
        }

        // Huwag i-drop ang payment_term dito kung ayaw mong mawala ang data tuwing rollback ng columns,
        // pero sa migrate:fresh, automatic naman itong lilinisin.
    }

    private function seedPaymentTerm(): void
    {
        $terms = [
            ['name' => 'Cash on Delivery (COD)', 'description' => 'Payment upon delivery', 'days' => 0],
            ['name' => 'Bank Transfer', 'description' => 'Payment via bank transfer', 'days' => null],
            ['name' => 'Credit Card', 'description' => 'Payment via credit card', 'days' => 0],
        ];

        foreach ($terms as $term) {
            DB::table('payment_term')->updateOrInsert(
                ['name' => $term['name']],
                array_merge($term, [
                    'created_at' => now(),
                    'updated_at' => now()
                ])
            );
        }
    }
};
