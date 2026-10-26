<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pengajuan_hold', 'dp_cash_b')) {
            Schema::table('pengajuan_hold', function (Blueprint $table) {
                $table->unsignedBigInteger('dp_cash_b')->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('pengajuan_hold', function (Blueprint $table) {
            $table->dropColumn('dp_cash_b');
        });
    }
};
