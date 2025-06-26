<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration 
{
    public function up(): void
    {
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_phase_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');

            $table->boolean('is_confirmed')->default(false);
            $table->integer('required_to_complete')->nullable(); // npr. broj sati
            $table->float('total_completed')->default(0);        // npr. ukupno odrađenih sati
            $table->integer('transferred_count')->default(0);    // broj komada prebačenih u magacin

            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->index('work_order_id');
            $table->index('work_phase_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
    }
};
