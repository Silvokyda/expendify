<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['income','expense']);
            $table->decimal('amount', 12, 2);
            $table->date('occurred_at');
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['user_id','occurred_at','type','category_id'], 'tx_user_date_type_cat_idx');
        });        
    }

    public function down(): void {
        Schema::dropIfExists('transactions');
    }
};
