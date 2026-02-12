<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('purchase_status_library', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // e.g. draft, pending, approved
            $table->string('name')->unique();           // e.g. Draft, Pending Approval
            $table->string('description')->nullable();
            $table->string('color')->nullable(); // optional (for UI badge color)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_status_library');
    }
};
