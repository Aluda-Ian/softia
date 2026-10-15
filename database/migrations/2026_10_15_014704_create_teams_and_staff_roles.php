<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Staff teams with section permissions, plus the super admin role (can
     * manage users, install updates and edit system settings). The seeded
     * super admin from .env is promoted automatically.
     */
    public function up(): void
    {
        // Guards make this safe on databases where an earlier draft already
        // added is_super_admin.
        if (! Schema::hasTable('teams')) {
            Schema::create('teams', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('description')->nullable();
                $table->json('permissions')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('users', 'is_super_admin')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('is_super_admin')->default(false)->after('is_admin');
            });
        }

        if (! Schema::hasColumn('users', 'team_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('team_id')->nullable()->after('is_super_admin')->constrained()->nullOnDelete();
            });
        }

        $email = config('deploy.seed_admin.email');

        if (filled($email)) {
            DB::table('users')->where('email', $email)->update(['is_admin' => true, 'is_super_admin' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('team_id');
            $table->dropColumn('is_super_admin');
        });

        Schema::dropIfExists('teams');
    }
};
