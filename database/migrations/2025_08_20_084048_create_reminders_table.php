<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['MONTHLY_SUMMARY','HIGH_EXPENSE_ALERT','UPCOMING_PAYMENT']);
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            // For UPCOMING_PAYMENT: when it’s due; for alerts: next check time
            $table->dateTime('scheduled_at')->nullable()->index();
            // For recurring summaries; RRULE-ish string or cron fragment if you prefer
            $table->string('schedule_rule')->nullable();
            // Toggle + last run bookkeeping
            $table->boolean('enabled')->default(true)->index();
            $table->dateTime('last_fired_at')->nullable();
            $table->json('meta')->nullable(); // e.g., paybill/till, threshold, etc.
            $table->timestamps();

            $table->index(['user_id','type']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('reminders');
    }
};
