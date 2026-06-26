<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->index('status');
            $table->index('cut_at');
            $table->index('updated_at');
            $table->index('total_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['cut_at']);
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['total_amount']);
        });
    }
};
