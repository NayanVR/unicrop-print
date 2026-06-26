<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_stations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('print_station_user', function (Blueprint $table) {
            $table->foreignId('print_station_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['print_station_id', 'user_id']);
        });

        foreach (['Pranjal', 'Avian', 'E-commerce', 'Factory', 'Chamunda'] as $name) {
            \App\Models\PrintStation::create(['name' => $name]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('print_station_user');
        Schema::dropIfExists('print_stations');
    }
};
