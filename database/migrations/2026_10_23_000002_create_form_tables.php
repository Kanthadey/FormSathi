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
        // One row per template version; older versions stay for old applications.
        Schema::create('form_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60);
            $table->unsignedInteger('version');
            $table->string('name_bn');
            $table->string('name_en');
            $table->string('official_url')->nullable();
            // Sections and fields in the form's own order, each tied to a profile key.
            $table->json('sections');
            $table->boolean('is_active')->default(true);
            // Set once someone has checked the order against the live form.
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['slug', 'version']);
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_template_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('draft');
            // Template field keys the user has copied so far.
            $table->json('copied')->nullable();
            // Encrypted snapshot of the values, taken when the user marks it submitted.
            $table->text('summary')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('applications');
        Schema::dropIfExists('form_templates');
    }
};
