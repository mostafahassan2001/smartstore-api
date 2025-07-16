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
        Schema::create('coupons', function (Blueprint $table) {
              $table->id();

    $table->string('code')->unique();
    $table->text('description')->nullable();

    $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
    $table->decimal('discount_value', 8, 2);

    $table->integer('max_uses')->default(1);
    $table->integer('used_count')->default(0);

    $table->dateTime('start_date')->nullable();
    $table->dateTime('end_date')->nullable();

    $table->boolean('is_active')->default(true);

    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
