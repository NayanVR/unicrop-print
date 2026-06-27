<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('print_station_size', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_station_id')->constrained()->cascadeOnDelete();
            $table->foreignId('size_id')->constrained()->cascadeOnDelete();
            $table->decimal('rate', 8, 2);
            $table->timestamps();
            $table->unique(['print_station_id', 'size_id']);
        });

        $stations = \App\Models\PrintStation::all();
        $sizes = \App\Models\Size::all();

        foreach ($stations as $station) {
            foreach ($sizes as $size) {
                \App\Models\PrintStationSize::create([
                    'print_station_id' => $station->id,
                    'size_id' => $size->id,
                    'rate' => $size->rate,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('print_station_size');
    }
};
