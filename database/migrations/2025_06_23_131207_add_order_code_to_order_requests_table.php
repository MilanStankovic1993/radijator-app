<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_requests', function (Blueprint $table) {
            $table->string('order_code')->nullable()->after('id'); // ili ->unique() ako želiš jedinstvenost
        });
    }

    public function down(): void
    {
        Schema::table('order_requests', function (Blueprint $table) {
            $table->dropColumn('order_code');
        });
    }
};
