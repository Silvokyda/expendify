<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budgets')->cascadeOnDelete();

            // one of these will be set:
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('savings_goal_id')->nullable()->constrained('savings_goals')->nullOnDelete();

            $table->enum('type', ['income', 'expense', 'saving'])->index();
            $table->decimal('amount', 14, 2);
            $table->string('note')->nullable();

            $table->timestamps();

            $table->index(['budget_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
