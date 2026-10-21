<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kavling_peta', function (Blueprint $table) {
            if (!Schema::hasColumn('kavling_peta', 'nama_cluster')) $table->string('nama_cluster')->nullable();
            $table->unsignedBigInteger('diskon_dp')->nullable();
            $table->unsignedBigInteger('plafon_kpr')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('kavling_peta', fn (Blueprint $table) => $table->dropColumn(['diskon_dp', 'plafon_kpr']));
    }
};
