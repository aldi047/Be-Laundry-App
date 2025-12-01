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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('name');
            $table->text('location');
            $table->string('city');
            $table->boolean('is_delivery')->default(false);
            $table->decimal('rating', 3, 2)->default(0.00);
            $table->string('whatsapp')->nullable();
            $table->text('description')->nullable();
            $table->decimal('price_per_kg', 10, 2)->default(5000);
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['city']);
            $table->index(['is_delivery']);
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
