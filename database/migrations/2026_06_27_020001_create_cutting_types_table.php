<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cutting_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('print_station_cutting_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('print_station_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cutting_type_id')->constrained()->cascadeOnDelete();
            $table->decimal('rate', 8, 2);
            $table->timestamps();
            $table->unique(['print_station_id', 'cutting_type_id']);
        });

        Schema::table('print_stations', function (Blueprint $table) {
            $table->boolean('requires_cutting')->default(true)->after('is_default');
        });

        Schema::table('print_jobs', function (Blueprint $table) {
            $table->boolean('needs_cutting')->default(true)->after('status');
            $table->foreignId('cutting_type_id')->nullable()->after('needs_cutting')->constrained();
        });

        $fallbackRate = (float) (\App\Models\Setting::get('cutting_rate', 0));

        $types = [];
        foreach (['Full Cut', 'Half Cut', 'Die Cut'] as $name) {
            $types[] = \App\Models\CuttingType::create(['name' => $name]);
        }
        $types[0]->update(['is_default' => true]);

        foreach (\App\Models\PrintStation::all() as $station) {
            foreach ($types as $type) {
                \App\Models\PrintStationCuttingType::create([
                    'print_station_id' => $station->id,
                    'cutting_type_id' => $type->id,
                    'rate' => $fallbackRate,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cutting_type_id');
            $table->dropColumn('needs_cutting');
        });

        Schema::table('print_stations', function (Blueprint $table) {
            $table->dropColumn('requires_cutting');
        });

        Schema::dropIfExists('print_station_cutting_type');
        Schema::dropIfExists('cutting_types');
    }
};
