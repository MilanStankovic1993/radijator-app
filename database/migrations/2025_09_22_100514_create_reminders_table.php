<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();

            // vlasnik podsetnika (samo on ga vidi)
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // osnovno
            $table->string('title');
            $table->text('notes')->nullable();

            // termin (čuvaj u UTC)
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->boolean('all_day')->default(false);

            // glavno slanje mejla u tačno vreme (opciono)
            $table->string('email_to')->nullable();
            $table->dateTime('email_at')->nullable();
            $table->dateTime('emailed_at')->nullable();

            // PRE-REMINDER: automatski mejl X min ranije (default 15 min)
            $table->boolean('pre_email_enabled')->default(true);
            $table->unsignedSmallInteger('pre_email_offset_minutes')->default(15);
            $table->dateTime('pre_emailed_at')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'starts_at']);
            $table->index(['email_at', 'emailed_at']);
            $table->index(['pre_emailed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
