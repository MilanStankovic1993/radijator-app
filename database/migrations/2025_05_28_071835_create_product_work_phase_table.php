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
        Schema::create('product_work_phase', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_phase_id')->constrained()->onDelete('cascade');
            $table->integer('pivot_order')->nullable(); // redosled faze
            $table->unsignedBigInteger('created_by')->nullable(); // korisnik koji je kreirao
            $table->unsignedBigInteger('updated_by')->nullable(); // korisnik koji je izmenio
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_work_phase');
    }
};
