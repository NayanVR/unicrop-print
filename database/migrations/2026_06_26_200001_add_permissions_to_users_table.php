<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('role');
            $table->json('permissions')->nullable()->after('is_admin');
            $table->boolean('can_print')->default(true)->after('permissions');
        });

        foreach (DB::table('users')->select('id', 'role')->get() as $user) {
            $permissions = match ($user->role) {
                'admin' => [],
                'uploader' => ['upload_design'],
                'printer' => ['print_station', 'cutting_station'],
                default => [],
            };

            DB::table('users')->where('id', $user->id)->update([
                'is_admin' => $user->role === 'admin',
                'permissions' => json_encode($permissions),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'permissions', 'can_print']);
        });
    }
};
