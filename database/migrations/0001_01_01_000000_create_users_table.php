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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            // Normalised to 01XXXXXXXXX.
            $table->string('mobile', 11)->unique();
            $table->string('pin');
            // The user's own data key, wrapped with the app key.
            $table->text('data_key');
            $table->string('plan', 20)->default('free');
            $table->string('locale', 2)->default('bn');
            $table->unsignedTinyInteger('pin_failed_attempts')->default(0);
            $table->timestamp('pin_locked_until')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
