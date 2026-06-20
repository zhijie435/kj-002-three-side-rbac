<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('guard', 50)->default('platform')->after('name')->comment('守卫端：platform平台, merchant商家, warehouse仓库');
            $table->dropUnique(['name']);
            $table->unique(['guard', 'name']);
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('guard', 50)->default('platform')->after('name')->comment('守卫端：platform平台, merchant商家, warehouse仓库');
            $table->dropUnique(['name']);
            $table->unique(['guard', 'name']);
        });
    }

    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['guard', 'name']);
            $table->unique('name');
            $table->dropColumn('guard');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropUnique(['guard', 'name']);
            $table->unique('name');
            $table->dropColumn('guard');
        });
    }
};
