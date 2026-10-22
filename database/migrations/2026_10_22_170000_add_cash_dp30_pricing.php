<?php

use App\Services\KavlingPricing;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kavling_peta', function (Blueprint $table) {
            foreach (array_keys(KavlingPricing::DP30_FIELDS) as $field) {
                $table->unsignedBigInteger($field)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('kavling_peta', fn (Blueprint $table) => $table->dropColumn(array_keys(KavlingPricing::DP30_FIELDS)));
    }
};
