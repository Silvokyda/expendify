<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('savings_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('savings_goal_id')->constrained('savings_goals')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('contributed_at');
            $table->timestamps();
            $table->index(['user_id', 'savings_goal_id', 'contributed_at'], 'sc_user_goal_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_contributions');
    }
};
