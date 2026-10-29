<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lokasi_kavling', function (Blueprint $table) {
            $table->string('no_telp', 100)->nullable();
            $table->string('logo')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('lokasi_kavling', fn (Blueprint $table) => $table->dropColumn(['no_telp', 'logo']));
    }
};
