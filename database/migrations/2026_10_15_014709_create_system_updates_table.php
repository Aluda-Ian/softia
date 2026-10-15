<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Update packages uploaded through the admin, with the backup needed to roll back.
     */
    public function up(): void
    {
        Schema::create('system_updates', function (Blueprint $table) {
            $table->id();
            $table->string('version');
            $table->text('notes')->nullable();
            $table->string('package_path');
            $table->string('status', 20)->index();
            $table->json('files')->nullable();
            $table->json('added_files')->nullable();
            $table->string('backup_path')->nullable();
            $table->longText('log')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_updates');
    }
};
