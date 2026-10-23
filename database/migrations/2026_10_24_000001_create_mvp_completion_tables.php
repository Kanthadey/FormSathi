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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('premium_until')->nullable();
            // Prepaid form packs not yet used.
            $table->unsignedInteger('form_credits')->default(0);
            $table->boolean('is_admin')->default(false);
        });

        // free | credit | premium: what paid for this assist, for the monthly free count.
        Schema::table('applications', function (Blueprint $table) {
            $table->string('funded_by', 10)->default('free');
        });

        Schema::table('documents', function (Blueprint $table) {
            // OCR readings waiting for the user to confirm; encrypted JSON.
            $table->text('ocr_suggestions')->nullable();
        });

        // F11: bKash payments.
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('product', 30);
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('BDT');
            $table->string('gateway', 20);
            $table->string('status', 20)->default('initiated');
            $table->string('invoice', 40)->unique();
            $table->string('payment_id')->nullable()->unique();
            $table->string('trx_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        // F10: education, work history and skills the job kit may draw on.
        Schema::create('career_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 20);
            // Encrypted JSON; fields depend on kind.
            $table->text('data');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['user_id', 'kind']);
        });

        Schema::create('job_kits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('job_title')->nullable();
            $table->string('company')->nullable();
            $table->text('advert');
            // Encrypted JSON: CV parts, cover letter, answers, gaps.
            $table->text('result')->nullable();
            $table->string('status', 20)->default('ready');
            $table->string('funded_by', 10)->default('free');
            $table->timestamps();
        });

        // F12: support tickets, also used for "report a wrong field".
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 150);
            $table->string('status', 20)->default('open');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['status', 'updated_at']);
        });

        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
            // The ticket owner, whose key encrypts the body.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('from_staff')->default(false);
            $table->text('body');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
        Schema::dropIfExists('tickets');
        Schema::dropIfExists('job_kits');
        Schema::dropIfExists('career_items');
        Schema::dropIfExists('payments');

        Schema::table('documents', fn (Blueprint $table) => $table->dropColumn('ocr_suggestions'));
        Schema::table('applications', fn (Blueprint $table) => $table->dropColumn('funded_by'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['premium_until', 'form_credits', 'is_admin']));
    }
};
