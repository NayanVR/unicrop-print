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
        if (Schema::hasTable('print_jobs')) {
            return;
        }

        Schema::create('print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('note')->default('-');
            $table->string('file_path');
            $table->string('file_name');
            $table->foreignId('size_id')->constrained('sizes');
            $table->decimal('rate', 10, 2);
            $table->unsignedInteger('sheets');
            $table->decimal('print_total', 10, 2)->default(0);
            $table->unsignedInteger('cutting_jobs')->default(0);
            $table->decimal('cutting_rate', 10, 2)->default(0);
            $table->decimal('cutting_total', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->default('pending'); // pending -> cutting -> completed
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('cut_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
