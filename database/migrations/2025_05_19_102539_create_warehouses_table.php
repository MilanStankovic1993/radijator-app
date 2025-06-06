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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('location', 100)->default('Glavno skladište');
            $table->unsignedInteger('quantity')->default(0);
            $table->decimal('reserved_quantity', 10, 2)->default(0)->comment('Rezervisana količina');
            $table->decimal('minimum_stock', 10, 2)->default(0)->comment('Minimalna zaliha');
            
            // Foreign key constraints
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes
            $table->index('product_id');
            $table->index('location');
            $table->unique(['product_id', 'location']); // Jedan proizvod po lokaciji
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};