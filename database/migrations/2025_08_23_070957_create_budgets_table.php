<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->enum('period', ['monthly', 'weekly', 'custom'])->default('monthly')->index();
            $table->decimal('total_amount', 14, 2);

            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();

            $table->boolean('is_active')->default(false)->index();
            $table->timestamp('activated_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
