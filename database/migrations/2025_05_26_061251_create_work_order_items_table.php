<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            // $table->string('code')->nullable(); // šifra proizvoda, ako postoji
            $table->unsignedBigInteger('work_phase_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->boolean('is_confirmed')->default(false);
            $table->integer('required_to_complete')->nullable();// potrebno da odradi (npr. sati ili min)
            $table->integer('total_completed')->nullable();// ukupno odradjeno (npr. sati ili min)
            // $table->string('status')->default('pending');
            
            $table->unsignedBigInteger('created_by')->nullable(); // korisnik koji je kreirao
            $table->unsignedBigInteger('updated_by')->nullable(); // korisnik koji je izmenio
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
    }
};
