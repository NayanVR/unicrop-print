<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('bottle_size_groups')) {
            Schema::create('bottle_size_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('bottle_sizes', 'group_id')) {
            Schema::table('bottle_sizes', function (Blueprint $table) {
                $table->foreignId('group_id')->nullable()->constrained('bottle_size_groups')->nullOnDelete()->after('id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('bottle_sizes', function (Blueprint $table) {
            $table->dropForeign(['group_id']);
            $table->dropColumn('group_id');
        });
        Schema::dropIfExists('bottle_size_groups');
    }
};
