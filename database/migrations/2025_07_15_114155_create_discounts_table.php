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
        Schema::create('discounts', function (Blueprint $table) {
               $table->id();

        $table->string('name');
        $table->string('name_ar');

        $table->text('description');
        $table->text('description_ar');

        $table->string('discount_code')->unique();
        $table->decimal('discount_percentage', 5, 2); // مثل 10.00 أو 15.50

        $table->dateTime('start_date');
        $table->dateTime('end_date');

        $table->boolean('is_active')->default(true);

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
