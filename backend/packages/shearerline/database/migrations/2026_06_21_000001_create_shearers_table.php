<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shearers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 100);
            $table->string('type', 50)->comment('剪切线类型');
            $table->string('location', 100)->nullable();
            $table->string('status', 20)->default('idle')->comment('状态: idle/running/maintenance/error/disabled');
            $table->unsignedInteger('max_capacity')->default(100)->comment('最大产能');
            $table->unsignedInteger('current_load')->default(0)->comment('当前负载');
            $table->unsignedBigInteger('operator_id')->nullable()->comment('操作员ID');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('shearerline_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('shearerline_id')->nullable();
            $table->string('order_no', 50)->comment('关联订单号');
            $table->string('product_name', 200)->comment('产品名称');
            $table->unsignedInteger('quantity')->default(0)->comment('数量');
            $table->string('priority', 20)->default('medium')->comment('优先级: low/medium/high/urgent');
            $table->string('status', 20)->default('pending')->comment('状态: pending/assigned/processing/completed/cancelled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('operator_id')->nullable()->comment('操作员ID');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('shearerline_id')->references('id')->on('shearers')->onDelete('set null');
            $table->index('shearerline_id');
            $table->index('status');
            $table->index('priority');
            $table->index('order_no');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shearerline_tasks');
        Schema::dropIfExists('shearers');
    }
};
