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
        Schema::create('laundries', function (Blueprint $table) {
            $table->id();
            $table->string('claim_code', 8)->unique();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->decimal('weight', 8, 2);
            $table->decimal('total', 10, 2);
            $table->boolean('is_delivery')->default(false);
            $table->string('delivery_address')->nullable();
            $table->enum('service_type', ['wash', 'dry_clean', 'iron', 'wash_iron'])->default('wash');
            $table->enum('status', ['pending', 'queue', 'ready', 'completed', 'cancelled'])->default('pending');
            $table->date('delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('claimed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['shop_id']);
            $table->index(['status']);
            $table->index(['claim_code']);
            $table->index(['created_at']);
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laundries');
    }
};
