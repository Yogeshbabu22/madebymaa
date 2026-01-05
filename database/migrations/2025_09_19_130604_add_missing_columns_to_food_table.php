<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('food', function (Blueprint $table) {
            if (!Schema::hasColumn('food', 'ingredients')) {
                $table->text('ingredients')->nullable()->after('description');
            }
            if (!Schema::hasColumn('food', 'veg')) {
                $table->boolean('veg')->default(0)->after('available_time_ends');
            }
            if (!Schema::hasColumn('food', 'new_available_times')) {
                $table->json('new_available_times')->nullable()->after('available_time_ends');
            }
            if (!Schema::hasColumn('food', 'super_category_ids')) {
                $table->string('super_category_ids')->nullable()->after('category_ids');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('food', function (Blueprint $table) {
            if (Schema::hasColumn('food', 'ingredients')) {
                $table->dropColumn('ingredients');
            }
            if (Schema::hasColumn('food', 'veg')) {
                $table->dropColumn('veg');
            }
            if (Schema::hasColumn('food', 'new_available_times')) {
                $table->dropColumn('new_available_times');
            }
            if (Schema::hasColumn('food', 'super_category_ids')) {
                $table->dropColumn('super_category_ids');
            }
        });
    }
};
