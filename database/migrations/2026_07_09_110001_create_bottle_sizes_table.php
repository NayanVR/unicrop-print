<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('bottle_sizes')) {
            return;
        }

        Schema::create('bottle_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('label_width_mm', 7, 2);
            $table->decimal('label_height_mm', 7, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bottle_sizes');
    }
};
