<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sppr', fn (Blueprint $table) => $table->json('spr_data')->nullable());
        DB::table('menu')->where('route_name', 'sppr.index')->update(['title' => 'SPR']);
    }

    public function down(): void
    {
        Schema::table('sppr', fn (Blueprint $table) => $table->dropColumn('spr_data'));
        DB::table('menu')->where('route_name', 'sppr.index')->update(['title' => 'SPPR']);
    }
};
