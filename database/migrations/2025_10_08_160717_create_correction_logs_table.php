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
        Schema::create('correction_logs', function (Blueprint $table) {
            $table->id(); // BIGINT, PK, AI
            $table->text('text'); // Kalimat yang dicek
            $table->string('rule_id', 100); // ID aturan yang dilanggar
            $table->text('message'); // Pesan koreksi
            $table->timestamp('created_at')->useCurrent(); // Waktu log dibuat
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('correction_logs');
    }
};
