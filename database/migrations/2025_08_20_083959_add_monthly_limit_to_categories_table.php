<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('categories', 'monthly_limit')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->decimal('monthly_limit', 12, 2)->nullable()->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('categories', 'monthly_limit')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropColumn('monthly_limit');
            });
        }
    }
};
