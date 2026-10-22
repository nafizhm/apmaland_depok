<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Services\KavlingPricing;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kavling_peta', function (Blueprint $table) {
            foreach (array_keys(KavlingPricing::FIELDS) as $field) $table->unsignedBigInteger($field)->nullable();
        });
        Schema::table('pengajuan_hold', fn (Blueprint $table) => $table->json('harga_snapshot')->nullable());
    }

    public function down(): void
    {
        Schema::table('pengajuan_hold', fn (Blueprint $table) => $table->dropColumn('harga_snapshot'));
        Schema::table('kavling_peta', fn (Blueprint $table) => $table->dropColumn(array_keys(KavlingPricing::FIELDS)));
    }
};
