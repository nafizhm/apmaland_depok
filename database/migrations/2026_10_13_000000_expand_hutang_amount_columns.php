<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hutang', function (Blueprint $table) {
            $table->bigInteger('nominal')->change();
            $table->bigInteger('terbayar')->change();
            $table->bigInteger('sisa_bayar')->change();
        });

        // Creating a debt also creates income; repayments are recorded as expenses.
        foreach (['pemasukan', 'pengeluaran'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->bigInteger('nominal')->change();
            });
        }
    }

    public function down(): void
    {
        // Keep the wider columns so rollback cannot truncate existing large amounts.
    }
};
