<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();

            $table->string('work_order_number');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->date('launch_date');
            $table->integer('quantity');
            $table->string('status')->default('aktivan');

            $table->timestamps(); // << Dodato
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
