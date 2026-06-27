<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_stations', function (Blueprint $table) {
            $table->boolean('is_default')->default(false)->after('name');
        });

        \App\Models\PrintStation::orderBy('id')->first()?->update(['is_default' => true]);
    }

    public function down(): void
    {
        Schema::table('print_stations', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
};
