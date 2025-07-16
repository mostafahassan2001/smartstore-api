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
        Schema::create('banners', function (Blueprint $table) {
           $table->id();

        $table->string('title');
        $table->string('title_ar');

        $table->text('description')->nullable();
        $table->text('description_ar')->nullable();

        $table->string('image'); // رابط أو اسم الصورة (يُرفع كـ string وليس binary مباشرة)
        $table->string('link_url')->nullable();

        $table->integer('order')->nullable(); // ترتيب العرض
        $table->boolean('is_active')->default(true);

        $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
