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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number')->nullable(); 
            $table->date('launch_date')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Ko je izdao
            $table->date('scheduled_at')->nullable(); // Datum lansiranja
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->enum('status', ['aktivan', 'zavrsen', 'neaktivan'])->default('aktivan'); // Status
            $table->timestamps(); // created_at i updated_at
        });
        }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
