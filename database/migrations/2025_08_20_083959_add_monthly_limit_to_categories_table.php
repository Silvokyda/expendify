<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('categories', function (Blueprint $table) {
            // Nullable 0-by-default; lets you opt-in per category
            $table->decimal('monthly_limit', 12, 2)->nullable()->default(0)->after('type');
        });
    }

    public function down(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('monthly_limit');
        });
    }
};
