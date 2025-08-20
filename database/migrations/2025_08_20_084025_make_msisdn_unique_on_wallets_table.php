<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('wallets', function (Blueprint $table) {
            // Drop plain index if it exists, then add unique
            try { $table->dropIndex(['msisdn']); } catch (\Throwable $e) {}
            $table->unique('msisdn', 'wallets_msisdn_unique');
        });
    }

    public function down(): void {
        Schema::table('wallets', function (Blueprint $table) {
            $table->dropUnique('wallets_msisdn_unique');
            $table->index('msisdn');
        });
    }
};
