<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('channel', ['TILL','PAYBILL','P2P'])->index();
            $table->string('destination')->comment('Till/Paybill/Phone');
            $table->decimal('amount', 14, 2);
            $table->string('reference')->nullable();
            $table->enum('status', ['PENDING','SUCCESS','FAILED'])->default('PENDING')->index();
            $table->json('meta')->nullable(); // gateway responses, etc.
            $table->timestamps();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists('payouts');
    }
};
