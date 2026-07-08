<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('lamination_types')) {
            Schema::create('lamination_types', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->boolean('is_default')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('print_station_lamination_type')) {
            Schema::create('print_station_lamination_type', function (Blueprint $table) {
                $table->id();
                $table->foreignId('print_station_id')->constrained()->cascadeOnDelete();
                $table->foreignId('lamination_type_id')->constrained()->cascadeOnDelete();
                $table->decimal('rate', 8, 2)->default(0);
                $table->timestamps();
                $table->unique(['print_station_id', 'lamination_type_id']);
            });
        }

        if (! Schema::hasColumn('print_jobs', 'needs_lamination')) {
            Schema::table('print_jobs', function (Blueprint $table) {
                $table->boolean('needs_lamination')->default(false)->after('cutting_type_id');
            });
        }

        if (! Schema::hasColumn('print_jobs', 'lamination_type_id')) {
            Schema::table('print_jobs', function (Blueprint $table) {
                $table->foreignId('lamination_type_id')->nullable()->after('needs_lamination')->constrained();
            });
        }

        if (! Schema::hasColumn('print_jobs', 'lamination_rate')) {
            Schema::table('print_jobs', function (Blueprint $table) {
                $table->decimal('lamination_rate', 8, 2)->default(0)->after('lamination_type_id');
            });
        }

        if (! Schema::hasColumn('print_jobs', 'lamination_total')) {
            Schema::table('print_jobs', function (Blueprint $table) {
                $table->decimal('lamination_total', 8, 2)->default(0)->after('lamination_rate');
            });
        }

        $types = [];
        foreach (['Glossy', 'Matte', 'Thermal'] as $name) {
            $types[] = \App\Models\LaminationType::firstOrCreate(['name' => $name]);
        }

        if (\App\Models\LaminationType::where('is_default', true)->doesntExist()) {
            $types[0]->update(['is_default' => true]);
        }

        foreach (\App\Models\PrintStation::all() as $station) {
            foreach ($types as $type) {
                \App\Models\PrintStationLaminationType::firstOrCreate(
                    ['print_station_id' => $station->id, 'lamination_type_id' => $type->id],
                    ['rate' => 0],
                );
            }
        }
    }

    public function down(): void
    {
        Schema::table('print_jobs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('lamination_type_id');
            $table->dropColumn(['needs_lamination', 'lamination_rate', 'lamination_total']);
        });
        Schema::dropIfExists('print_station_lamination_type');
        Schema::dropIfExists('lamination_types');
    }
};
