<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbl_product_data', function (Blueprint $table) {
            $table->integer('intStock')->nullable(false)->after('strProductCode');
            $table->decimal('decCostInGBP', 10, 2)->nullable(false)->after('intStock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_product_data', function (Blueprint $table) {
            $table->dropColumn('intStock');
            $table->dropColumn('decCostInGBP');
        });
    }
};
