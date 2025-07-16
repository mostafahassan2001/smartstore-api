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
        Schema::create('products', function (Blueprint $table) {
           $table->id();
        
        $table->string('name_en');
        $table->string('name_ar');

        $table->text('description_en')->nullable();
        $table->text('description_ar')->nullable();

        $table->string('image')->nullable(); // مسار الصورة

        $table->json('colors')->nullable();   // ["red", "blue", "black"]
        $table->json('sizes')->nullable();    // ["S", "M", "L", "XL"]

        $table->decimal('price', 10, 2);
        $table->boolean('status')->default(true);
        $table->foreignId('category_id')->constrained()->onDelete('cascade');
        $table->foreignId('brand_id')->constrained()->onDelete('cascade');

        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
