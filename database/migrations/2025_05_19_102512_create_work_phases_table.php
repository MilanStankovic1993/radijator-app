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
        Schema::create('work_phases', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->enum('location', ['Grdica', 'Seovac']);
            $table->decimal('time_norm', 8, 2)->comment('Vremenska norma u minutima');
            $table->unsignedTinyInteger('number_of_workers')->default(1)->comment('Potreban broj radnika');
            $table->text('description')->nullable();
            
            // Foreign key constraints
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Index za bolje performanse
            $table->index('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_phases');
    }
};