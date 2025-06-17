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

            $table->string('full_name', 255);      // puno ime, može i nullable ako nije obavezno
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('cascade');

            // Novi dodati stupci:
            $table->string('work_order_number', 255)->nullable();
            $table->string('series', 255)->nullable();         // serija
            $table->unsignedInteger('quantity');


            $table->date('launch_date');
            $table->enum('type', ['standard', 'custom'])->default('standard');
            $table->enum('status', ['aktivan', 'u_toku', 'zavrsen', 'otkazan'])->default('aktivan');
            $table->enum('status_progresije', ['hitno', 'ceka se', 'aktivan'])->default('aktivan');

            // Foreign key constraints
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->index('work_order_number');
            $table->index(['status', 'launch_date']);
            $table->index('user_id');
            $table->index('product_id');
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
