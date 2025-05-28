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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['individual', 'company']);

            // Fizicka lica
            $table->string('jmbg')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('id_card_number')->nullable();
            $table->text('note')->nullable();

            // Kompanije
            $table->string('company_name')->nullable();
            $table->string('pib')->nullable();
            $table->string('contact_person')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
