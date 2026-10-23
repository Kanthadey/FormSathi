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
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('mobile', 11)->index();
            $table->string('code_hash');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('known_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // SHA-256 of the device cookie; the cookie itself is never stored.
            $table->string('token_hash', 64)->unique();
            $table->string('user_agent', 255)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 40);
            $table->string('category', 20);
            // Encrypted with the user's data key.
            $table->text('title')->nullable();
            $table->text('original_name');
            $table->string('file_path');
            $table->string('mime', 100);
            $table->unsignedInteger('size');
            $table->date('expires_at')->nullable();
            $table->timestamp('expiry_reminded_at')->nullable();
            $table->string('ocr_status', 20)->default('manual');
            $table->timestamps();

            $table->index(['user_id', 'category']);
            $table->index(['expires_at', 'expiry_reminded_at']);
        });

        // Values read from one paper. The mismatch check compares these.
        Schema::create('document_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_id')->constrained()->cascadeOnDelete();
            $table->string('key', 60);
            $table->text('value');
            $table->timestamps();

            $table->unique(['document_id', 'key']);
            $table->index(['user_id', 'key']);
        });

        // The master profile: one confirmed value per field.
        Schema::create('profile_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('key', 60);
            $table->text('value');
            $table->foreignId('source_document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->boolean('confirmed')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'key']);
        });

        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // Who acted: the user, or later a consented shop (B2B).
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 40);
            $table->string('subject_type', 40)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_logs');
        Schema::dropIfExists('profile_fields');
        Schema::dropIfExists('document_fields');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('known_devices');
        Schema::dropIfExists('otp_codes');
    }
};
