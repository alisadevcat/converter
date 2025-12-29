<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // src/app/Modules/Currency/Database/Migrations/2025_12_23_175310_create_exchange_rates_table.php

    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_code', 3);
            $table->string('target_code', 3);
            $table->decimal('rate', 15, 8);
            $table->date('date');
            $table->timestamps();

            // Unique constraint ensures no duplicate rates for same currency pair on same date
            $table->unique(['base_code', 'target_code', 'date']);

            $table->index(['base_code', 'date']);
            $table->index(['base_code', 'target_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};