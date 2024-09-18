<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tbl_product_data', function (Blueprint $table) {
            $table->timestamp('dtmAdded')->default(\Illuminate\Support\Facades\DB::raw('CURRENT_TIMESTAMP'))->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tbl_product_data', function (Blueprint $table) {
            $table->dateTime('dtmAdded')->nullable()->change();
        });
    }
};
