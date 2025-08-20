<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('category_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            // Which kind of period this budget is for
            $table->enum('period', ['monthly','weekly','custom'])->index();

            // Limit for the period
            $table->decimal('amount', 12, 2);

            // Optional date constraints (used for custom; can also bound monthly/weekly if you want)
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();

            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            // Quick lookup
            $table->index(['user_id','category_id','period']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('category_budgets');
    }
};
