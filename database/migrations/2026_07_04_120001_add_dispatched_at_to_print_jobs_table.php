<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('print_jobs', 'dispatched_at')) {
            Schema::table('print_jobs', function (Blueprint $table) {
                $table->timestamp('dispatched_at')->nullable()->after('cut_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropColumn('dispatched_at');
        });
    }
};
